<?php
/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\cron;
use phpStructs\Queue;
use webfiori\entity\router\Router;
use webfiori\entity\Logger;
use webfiori\entity\Util;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
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
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * A class that is used to manage cron jobs.
 * It is used to create jobs, schedule them and execute them. In order to run 
 * the jobs automatically, the developer must add an entry in the following 
 * formate in crontab:
 * <p>* * * * *  /usr/bin/curl {BASE_URL}/cron-jobs/execute/{password}</p>
 * Where {BASE_URL} is the web site's base URL and {password} is the password 
 * that was set by the developer to protect the jobs from unauthorized access.
 * @author Ibrahim
 * @version 1.0.1
 */
class Cron {
    /**
     * The password that is used to access and execute jobs.
     * @var string
     * @since 1.0 
     */
    private $accessPass;
    /**
     * A variable that is set to TRUE if job execution log 
     * is enabled.
     * @var boolean
     * @since 1.0.1 
     */
    private $isLogEnabled;
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
    private function __construct() {
        $this->isLogEnabled = FALSE;
        $this->cronJobsQueue = new Queue();
        $this->_setPassword('');
        $func = function(){
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
                                    $this->_logJobExecution($job);
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
                            . 'Invalid password.'
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
                            $this->_logJobExecution($job);
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
        };
        Router::closure('/cron-jobs/execute/{password}',$func);
        Router::closure('/cron-jobs/execute',$func);
        
        $forceFunc = function(){
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
                            Logger::log('Checking if job name is given...');
                            $jobName = isset($_GET['job-name']) ? filter_var($_GET['job-name']) : NULL;
                            Logger::log('Job name = \''.$jobName.'\'.', 'debug');
                            if($jobName != NULL){
                                while ($job = Cron::jobsQueue()->dequeue()){
                                    if($job->getJobName() == $jobName){
                                        $job->execute(TRUE);
                                        Logger::log('Job executed.');
                                        $this->_logJobExecution($job,TRUE);
                                        Logger::requestCompleted();
                                        http_response_code(200);
                                        die(''
                                        . '<!DOCTYPE html>'
                                        . '<html>'
                                        . '<head>'
                                        . '<title>Job Executed</title>'
                                        . '</head>'
                                        . '<body>'
                                        . '<h1>200 - Ok</h1>'
                                        . '<hr>'
                                        . '<p>'
                                        . 'The given job was forced to execute.'
                                        . '</p>'
                                        . '</body>'
                                        . '</html>');
                                    }
                                }
                                Logger::log('No job was found which has the given name.','warning');
                                Logger::requestCompleted();
                                http_response_code(404);
                                die(''
                                . '<!DOCTYPE html>'
                                . '<html>'
                                . '<head>'
                                . '<title>Job Not Found</title>'
                                . '</head>'
                                . '<body>'
                                . '<h1>404 - Not Found</h1>'
                                . '<hr>'
                                . '<p>'
                                . 'No job was found which has the given name.'
                                . '</p>'
                                . '</body>'
                                . '</html>');
                            }
                            else{
                                Logger::log('No job name was given.','warning');
                                Logger::requestCompleted();
                                http_response_code(404);
                                die(''
                                . '<!DOCTYPE html>'
                                . '<html>'
                                . '<head>'
                                . '<title>Job Not Found</title>'
                                . '</head>'
                                . '<body>'
                                . '<h1>404 - Not Found</h1>'
                                . '<hr>'
                                . '<p>'
                                . 'No job was found which has the given name.'
                                . '</p>'
                                . '</body>'
                                . '</html>');
                            }
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
                            . 'Invalid password.'
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
                    Logger::log('Checking if job name is given...');
                    $jobName = isset($_GET['job-name']) ? filter_var($_GET['job-name']) : NULL;
                    Logger::log('Job name = \''.$jobName.'\'.', 'debug');
                    if($jobName != NULL){
                        while ($job = Cron::jobsQueue()->dequeue()){
                            if($job->getJobName() == $jobName){
                                $job->execute(TRUE);
                                Logger::log('Job executed.');
                                $this->_logJobExecution($job,TRUE);
                                Logger::requestCompleted();
                                http_response_code(200);
                                die(''
                                . '<!DOCTYPE html>'
                                . '<html>'
                                . '<head>'
                                . '<title>Job Executed</title>'
                                . '</head>'
                                . '<body>'
                                . '<h1>200 - Ok</h1>'
                                . '<hr>'
                                . '<p>'
                                . 'The given job was forced to execute.'
                                . '</p>'
                                . '</body>'
                                . '</html>');
                            }
                        }
                        Logger::log('No job was found which has the given name.','warning');
                        Logger::requestCompleted();
                        http_response_code(404);
                        die(''
                        . '<!DOCTYPE html>'
                        . '<html>'
                        . '<head>'
                        . '<title>Job Not Found</title>'
                        . '</head>'
                        . '<body>'
                        . '<h1>404 - Not Found</h1>'
                        . '<hr>'
                        . '<p>'
                        . 'No job was found which has the given name.'
                        . '</p>'
                        . '</body>'
                        . '</html>');
                    }
                    else{
                        Logger::log('No job name was given.','warning');
                        Logger::requestCompleted();
                        http_response_code(404);
                        die(''
                        . '<!DOCTYPE html>'
                        . '<html>'
                        . '<head>'
                        . '<title>Job Not Found</title>'
                        . '</head>'
                        . '<body>'
                        . '<h1>404 - Not Found</h1>'
                        . '<hr>'
                        . '<p>'
                        . 'No job was found which has the given name.'
                        . '</p>'
                        . '</body>'
                        . '</html>');
                    }
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
        };
        Router::closure('/cron-jobs/execute/force/{job-name}',$forceFunc);
        Router::closure('/cron-jobs/execute/{password}/force/{job-name}',$forceFunc);
    }
    private function _setLogEnabled($bool){
        $this->isLogEnabled = $bool === TRUE ? TRUE : FALSE;
    }
    private function _isLogEnabled() {
        return $this->isLogEnabled;
    }
    /**
     * Enable or disable logging for jobs execution. 
     * This method is also used to check if logging is enabled or not.
     * @param boolean $bool If set to TRUE, a log file that contains the details 
     * of the executed jobs will be created in 'logs' folder. Default value 
     * is NULL.
     * @return boolean If logging is enabled, the method will return TRUE.
     * @since 1.0.1
     */
    public static function execLog($bool=null) {
        if($bool !== NULL){
            self::_get()->_setLogEnabled($bool);
        }
        return self::_get()->_isLogEnabled();
    }
    /**
     * Creates new job using cron expression.
     * The job will be created and scheduled only if the given cron expression 
     * is valid. For more information on cron expressions, go to 
     * https://en.wikipedia.org/wiki/Cron#CRON_expression. Note that 
     * the method does not support year field. This means 
     * the expression will have only 5 fields.
     * @param string $when A cron expression.
     * @param string $jobName An optional job name. 
     * @param callable $function A function to run when it is the time to execute 
     * the job.
     * @param array $funcParams An array of parameters that can be passed to the 
     * function. 
     * @return boolean If the job was created and scheduled, the method will 
     * return TRUE. Other than that, the method will return FALSE.
     * @since 1.0
     */
    public static function createJob($when='*/5 * * * *',$jobName='',$function='',$funcParams=array()){
        try{
            $job = new CronJob($when);
            $job->setOnExecution($function, $funcParams);
            if(strlen($jobName) > 0){
                $job->setJobName($jobName);
            }
            return self::scheduleJob($job);
        } 
        catch (Exception $ex) {
            return FALSE;
        }
    }
    /**
     * Creates a daily job to execute every day at specific hour and minute.
     * @param string $time A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive.
     * @param string $name An optional name for the job.
     * @param callable $func A function that will be executed once it is the 
     * time to run the job. Default is NULL.
     * @param array $funcParams An array of parameters which will be passed to 
     * the function.
     * @return boolean If the job was created and scheduled, the method will 
     * return TRUE. Other than that, the method will return FALSE.
     * @since 1.0
     */
    public static function dailyJob($time,$name='',$func=null,$funcParams=array()){
        $split = explode(':', $time);
        if(count($split) == 2){
            $job = new CronJob();
            $job->setJobName($name);
            $job->dailyAt($split[0], $split[1]);
            $job->setOnExecution($func, $funcParams);
            return self::scheduleJob($job);
        }
        return FALSE;
    }
    /**
     * Creates a job that will be executed on specific time weekly.
     * @param string $time A string in the format 'd-hh:mm'. 'd' can be a number 
     * between 0 and 6 inclusive or a 3 characters day name such as 'sun'. 0 is 
     * for Sunday and 6 is for Saturday.
     * 'hh' can have any value between 0 and 23 inclusive. mm can have any value 
     * between 0 and 59 inclusive.
     * @param string $name An optional name for the job.
     * @param callable|NULL $func A function that will be executed once it is the 
     * time to run the job. Default is NULL.
     * @param array $funcParams An array of parameters which will be passed to 
     * the function.
     * @return boolean If the job was created and scheduled, the method will 
     * return TRUE. Other than that, the method will return FALSE.
     * @since 1.0
     */
    public static function weeklyJob($time,$name='',$func=null,$funcParams=array()){
        $split1 = explode('-', $time);
        if(count($split1) == 2){
            $job = new CronJob();
            $job->setJobName($name);
            $job->weeklyOn($split1[0], $split1[1]);
            $job->setOnExecution($func, $funcParams);
            return self::scheduleJob($job);
        }
        return FALSE;
    }
    /**
     * Sets or gets the password that is used to protect the cron instance.
     * The password is used to prevent unauthorized access to execute jobs.
     * @param string $pass If not NULL, the password will be updated to the 
     * given one.
     * @return string If the password is set, the method will return it. 
     * If not set, the method will return the string 'NO_PASSWORD'.
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
     * @return Queue An object of type 'Queue' which contains all queued jobs.
     * @since 1.0
     */
    public static function jobsQueue(){
        return self::_get()->_getQueue();
    }

    /**
     * Adds new job to jobs queue.
     * @param CronJob $job An instance of the class 'CronJob'.
     * @return boolean If the job is added, the method will return TRUE.
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
    private function _getQueue() {
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
    
    private function _logJobExecution($job,$forced=false){
        if($this->isLogEnabled){
            $logFile = ROOT_DIR.'/logs/cron.txt';
            $file = fopen($logFile, 'a+');
            if(is_resource($file)){
                if($forced){
                    fwrite($file, 'Job \''.$job->getJobName().'\' was forced to executed at '.date(DATE_RFC1123)."\n");
                }
                else{
                    fwrite($file, 'Job \''.$job->getJobName().'\' automatically executed at '.date(DATE_RFC1123)."\n");
                }
                fclose($file);
            }
        }
    }
}
