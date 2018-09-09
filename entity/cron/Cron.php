<?php
/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * A class that is used to manage cron jobs.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class Cron {
    /**
     * The password that is used to access and execute jobs.
     * @var string
     * @since 1.0 
     */
    private $accessPass;
    /**
     * A queue which contains all cron jobs.
     * @var Queue 
     * @since 1.0
     */
    private $cronJobsQueue;
    /**
     * An instance of 'CronExecuter'
     * @var Cron 
     * @since 1.0
     */
    private static $executer;
    /**
     * Returns a singleton of the class CronExecuter.
     * @return Cron
     * @since 1.0
     */
    private static function &_get(){
        if(self::$executer === NULL){
            self::$executer = new Cron();
        }
        return self::$executer;
    }
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    public function __construct() {
        $this->cronJobsQueue = new Queue();
        $this->_setPassword('');
        Router::closure('/cron-jobs/execute', function(){
            Logger::logFuncCall('CLOSURE_ROUTE');
            Logger::log('Validating source IP address...');
            $clientIp = Util::getClientIP();
            $serverIp = Util::getClientIP();
            Logger::log('Client IP = \''.$clientIp.'\'.', 'debug');
            Logger::log('Server IP = \''.$serverIp.'\'.', 'debug');
            if($clientIp == $serverIp){
                Logger::log('Checking if password is required to execute cron jobs...');
                if(Cron::password() != 'NO_PASSWORD'){
                    Logger::log('Password required. Checking if password is provided...');
                    $password = isset($_GET['password']) ? filter_var($_GET['password']) : '';
                    Logger::log('Password = \''.$password.'\'.', 'debug');
                    if($password != ''){
                        Logger::log('Checking if password is valid...');
                        if($password == Cron::password()){
                            Logger::log('Valid password.');
                            Logger::log('Starting the execution of tasks.');
                            $totalJobs = Cron::jobsQueue()->size();
                            $executedJobsCount = 0;
                            while ($job = Cron::jobsQueue()->dequeue()){
                                if($job->execute()){
                                    $executedJobsCount++;
                                }
                            }
                            Logger::log('Jobs execution finished.');
                            Logger::requestCompleted();
                            http_response_code(200);
                            die(''
                            . '<!DOCTYPE html>'
                            . '<html>'
                            . '<head>'
                            . '<title>OK</title>'
                            . '</head>'
                            . '<body>'
                            . '<h1>200 - OK</h1>'
                            . '<hr>'
                            . '<p>'
                            . 'Total number of jobs: '.$totalJobs
                            . '</p>'
                            . '<p>'
                            . 'Number of jobs executed: '.$executedJobsCount
                            . '</p>'
                            . '</body>'
                            . '</html>');
                        }
                        else{
                            Logger::log('Invalid password.', 'error');
                            Logger::requestCompleted();
                            die(''
                            . '<!DOCTYPE html>'
                            . '<html>'
                            . '<head>'
                            . '<title>Not Authorized</title>'
                            . '</head>'
                            . '<body>'
                            . '<h1>401 - Not Authorized</h1>'
                            . '<hr>'
                            . '<p>'
                            . 'Invalid cron password.'
                            . '</p>'
                            . '</body>'
                            . '</html>');
                        }
                    }
                    else{
                        Logger::log('No password is provided.', 'error');
                        Logger::requestCompleted();
                        die(''
                        . '<!DOCTYPE html>'
                        . '<html>'
                        . '<head>'
                        . '<title>Not Authorized</title>'
                        . '</head>'
                        . '<body>'
                        . '<h1>401 - Not Authorized</h1>'
                        . '<hr>'
                        . '<p>'
                        . 'Password is missing.'
                        . '</p>'
                        . '</body>'
                        . '</html>');
                    }
                }
                else{
                    Logger::log('No password required. Executing jobs...');
                    $totalJobs = Cron::jobsQueue()->size();
                    $executedJobsCount = 0;
                    while ($job = Cron::jobsQueue()->dequeue()){
                        if($job->execute()){
                            $executedJobsCount++;
                        }
                    }
                    Logger::log('Jobs execution finished.');
                    Logger::requestCompleted();
                    http_response_code(200);
                    die(''
                    . '<!DOCTYPE html>'
                    . '<html>'
                    . '<head>'
                    . '<title>OK</title>'
                    . '</head>'
                    . '<body>'
                    . '<h1>200 - OK</h1>'
                    . '<hr>'
                    . '<p>'
                    . 'Total number of jobs: '.$totalJobs
                    . '</p>'
                    . '<p>'
                    . 'Number of jobs executed: '.$executedJobsCount
                    . '</p>'
                    . '</body>'
                    . '</html>');
                }
            }
            else{
                Logger::log('Client IP address is not the same as server IP. No jobs executed.', 'error');
                Logger::requestCompleted();
                http_response_code(403);
                die(''
                . '<!DOCTYPE html>'
                . '<html>'
                . '<head>'
                . '<title>Forbidden</title>'
                . '</head>'
                . '<body>'
                . '<h1>403 - Forbidden</h1>'
                . '<hr>'
                . '<p>'
                . 'Cron jobs can be executed only withen the server environment.'
                . '</p>'
                . '</body>'
                . '</html>');
            }
        });
    }
    /**
     * Creates new cron job.
     * @param string $when A cron expression.
     * @param function $function A function to run when it is the time to execute 
     * the job.
     * @param array $funcParams An array of parameters that can be passed to the 
     * function. 
     * @since 1.0
     */
    public static function createJob($when='*/5 * * * *',$function='',$funcParams=array()){
        $job = new CronJob($when);
        $job->setOnExecution($function, $funcParams);
        self::scheduleJob($job);
    }
    /**
     * Adds a daily job to execute every day at specific hour and minute.
     * @param string $time [Optional] A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value btween 0 and 59 inclusive. 
     * default is '00:00'.
     * @param function $func A function that will be executed once it is the 
     * time to run the job.
     * @param array $funcParams An array of parameters which will be passed to 
     * the function.
     * @since 1.0
     */
    public static function dailyJob($time,$func,$funcParams=array()){
        $split = explode(':', $time);
        if(count($split) == 2){
            $job = new CronJob();
            $job->dailyAt($split[0], $split[1]);
            $job->setOnExecution($func, $funcParams);
            self::scheduleJob($job);
        }
    }
    /**
     * Adds a job that will be executed on specific time weekly.
     * @param string $time A string in the format 'd-hh:mm'. 'd' can be a number 
     * between 0 and 7 inclusive or a 3 characters day name. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive.
     * @param function $func A function that will be executed once it is the 
     * time to run the job.
     * @param array $funcParams An array of parameters which will be passed to 
     * the function.
     * @since 1.0
     */
    public static function weeklyJob($time,$func,$funcParams=array()){
        $split1 = explode('-', $time);
        if(count($split1) == 2){
            $job = new CronJob();
            $job->weeklyOn($split1[0], $split1[1]);
            $job->setOnExecution($func, $funcParams);
            self::scheduleJob($job);
        }
    }
    /**
     * Sets or gets the password that is used to protect the cron instance.
     * @param string $pass If not NULL, the password will be updated to the 
     * given one.
     * @return string|NULL If the password is set, the function will return it. 
     * If not set, the function will return NULL.
     * @since 1.0
     */
    public static function password($pass=null) {
        if($pass !== NULL){
            self::_get()->_setPassword($pass);
        }
        return self::_get()->_getPassword();
    }
    /**
     * Returns a queue of all queued jobs.
     * @return Queue An instance of the class 'Queue'.
     * @since 1.0
     */
    public static function jobsQueue(){
        return self::_get()->_getQueue();
    }

    /**
     * Adds new cron job.
     * @param CronJob $job An instance of the class 'CronJob'.
     * @return boolean If the job is added, the function will return TRUE.
     * @since 1.0
     */
    public static function scheduleJob($job){
        return self::_get()->_addJob($job);
    }
    /**
     * 
     * @param CronJob $job
     * @return type
     * @since 1.0
     */
    private function _addJob($job){
        $retVal = FALSE;
        if($job instanceof CronJob){
            if($job->getJobName() == 'CRON-JOB'){
                $job->setJobName('job-'.$this->jobsQueue()->size());
            }
            $retVal = $this->cronJobsQueue->enqueue($job);
        }
        return $retVal;
    }
    /**
     * 
     * @return Queue
     * @since 1.0
     */
    public function _getQueue() {
        return $this->cronJobsQueue;
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function _getPassword(){
        if($this->accessPass == ''){
            return 'NO_PASSWORD';
        }
        return $this->accessPass;
    }
    /**
     * 
     * @param type $pass
     * @since 1.0
     */
    private function _setPassword($pass){
        if(gettype($pass) == 'string'){
            $this->accessPass = $pass;
        }
    }
}
