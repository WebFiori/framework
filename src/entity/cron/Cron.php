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
use webfiori\entity\Util;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 404 Not Found");
    die('<!DOCTYPE html><html><head><title>Not Found</title></head><body>'
    . '<h1>404 - Not Found</h1><hr><p>The requested resource was not found on the server.</p></body></html>');
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
 * @version 1.0.5
 */
class Cron {
    /**
     * The job which is currently executing.
     * @var CronJob|null
     * @since 1.0.4 
     */
    private $activeJob;
    /**
     * An array that contains current timestamp. 
     * @var array 
     */
    private $timestamp;
    /**
     * The password that is used to access and execute jobs.
     * @var string
     * @since 1.0 
     */
    private $accessPass;
    /**
     * A variable that is set to true if job execution log 
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
        if(self::$executer === null){
            self::$executer = new Cron();
        }
        return self::$executer;
    }
    /**
     * 
     * @param CronJob|null $job
     * @since 1.0.4
     */
    private function _setActiveJob($job) {
        $this->activeJob = $job;
    }
    /**
     * Returns an object that represents the job which is currently being executed.
     * @return CronJob|null If there is a job which is being executed, the 
     * method will return an object of type 'CronJob' that represent it. If 
     * no job is being executed, the method will return null.
     * @since 1.0.4
     */
    public static function &activeJob() {
        return self::_get()->activeJob;
    }
    /**
     * Returns a job given its name.
     * @param string $jobName The name of the job.
     * @return CronJob|null If a job which has the given name was found, 
     * the method will return an object of type 'CronJob' that represents 
     * the job. Other than that, the method will return null.
     * @since 1.0.5
     */
    public static function &getJob($jobName) {
        $trimmed = trim($jobName);
        $retVal = null;
        if(strlen($trimmed) != 0){
            $tempQ = new Queue();
            while ($job = &self::jobsQueue()->dequeue()){
                $tempQ->enqueue($job);
                if($job->getJobName() == $trimmed){
                    $retVal = $job;
                }
            }
            while ($job = &$tempQ->dequeue()){
                self::scheduleJob($job);
            }
        }
        return $retVal;
    }
    /**
     * Returns the number of current month as integer.
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 1 and 12 
     * inclusive.
     * @return int An integer that represents current month's number.
     * @since 1.0.2
     */
    public static function month(){
        return self::_get()->timestamp['month'];
    }
    /**
     * Returns the number of current day in the current  month as integer.
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time.
     * @return int An integer that represents current day number in 
     * the current month.
     * @since 1.0.2
     */
    public static function dayOfMonth(){
        return self::_get()->timestamp['month-day'];
    }
    /**
     * Returns the number of current day in the current  week as integer.
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 0 and 6 
     * inclusive. 0 Means Sunday and 6 is for Saturday.
     * @return int An integer that represents current day number in 
     * the week.
     * @since 1.0.2
     */
    public static function dayOfWeek(){
        return self::_get()->timestamp['week-day'];
    }
    /**
     * Returns the number of current hour in the day as integer.
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 0 and 23 
     * inclusive.
     * @return int An integer that represents current hour number in 
     * the day.
     * @since 1.0.2
     */
    public static function hour(){
        return self::_get()->timestamp['hour'];
    }
    /**
     * Returns the number of current minute in the current hour as integer.
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 0 and 59 
     * inclusive.
     * @return int An integer that represents current minute number in 
     * the current hour.
     * @since 1.0.2
     */
    public static function minute(){
        return self::_get()->timestamp['minute'];
    }
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    private function __construct() {
        $this->timestamp = array(
            'month'=>intval(date('m')),
            'month-day'=>intval(date('d')),
            'week-day'=>intval(date('w')),
            'hour'=>intval(date('H')),
            'minute'=>intval(date('i'))
        );
        $this->isLogEnabled = false;
        $this->cronJobsQueue = new Queue();
        $this->_setPassword('');
        $func = function(){
            $clientIp = Util::getClientIP();
            $serverIp = Util::getClientIP();
            if($clientIp == $serverIp){
                if(Cron::password() != 'NO_PASSWORD'){
                    $password = isset($_GET['password']) ? filter_var($_GET['password']) : '';
                    if($password != ''){
                        if($password == Cron::password()){
                            $totalJobs = Cron::jobsQueue()->size();
                            $executedJobsCount = 0;
                            while ($job = Cron::jobsQueue()->dequeue()){
                                if($job->isTime()){
                                    $this->_setActiveJob($job);
                                }
                                if($job->execute()){
                                    $this->_logJobExecution($job);
                                    $executedJobsCount++;
                                }
                                $this->_setActiveJob(null);
                            }
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
                    $totalJobs = Cron::jobsQueue()->size();
                    $executedJobsCount = 0;
                    while ($job = Cron::jobsQueue()->dequeue()){
                        if($job->isTime()){
                            $this->_setActiveJob($job);
                        }
                        if($job->execute()){
                            $this->_logJobExecution($job);
                            $executedJobsCount++;
                        }
                        $this->_setActiveJob(null);
                    }
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
        Router::closure([
            'path'=>'/cron-jobs/execute/{password}',
            'route-to'=>$func
        ]);
        Router::closure([
            'path'=>'/cron-jobs/execute',
            'route-to'=>$func
        ]);
        
        $forceFunc = function(){
            $clientIp = Util::getClientIP();
            $serverIp = Util::getClientIP();
            if($clientIp == $serverIp){
                if(Cron::password() != 'NO_PASSWORD'){
                    $password = isset($_GET['password']) ? filter_var($_GET['password']) : '';
                    if($password != ''){
                        if($password == Cron::password()){
                            $jobName = isset($_GET['job-name']) ? filter_var($_GET['job-name']) : null;
                            if($jobName != null){
                                while ($job = Cron::jobsQueue()->dequeue()){
                                    if($job->getJobName() == $jobName){
                                        if($job->isTime()){
                                            $this->_setActiveJob($job);
                                        }
                                        $job->execute(true);
                                        $this->_logJobExecution($job,true);
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
                    $jobName = isset($_GET['job-name']) ? filter_var($_GET['job-name']) : null;
                    if($jobName != null){
                        while ($job = Cron::jobsQueue()->dequeue()){
                            if($job->getJobName() == $jobName){
                                if($job->isTime()){
                                    $this->_setActiveJob($job);
                                }
                                $job->execute(true);
                                $this->_logJobExecution($job,true);
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
        Router::closure([
            'path'=>'/cron-jobs/execute/force/{job-name}',
            'route-to'=>$forceFunc
        ]);
        Router::closure([
            'path'=>'/cron-jobs/execute/{password}/force/{job-name}',
            'route-to'=>$forceFunc
        ]);
        
        $viewJobsFunc = function(){
            if(Cron::password() != 'NO_PASSWORD'){
                $password = isset($_GET['password']) ? filter_var($_GET['password']) : '';
                if($password != ''){
                    if($password == Cron::password()){
                        new CronTasksView();
                        die();
                    }
                    else{
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
                new CronTasksView();
                die('');
            }
        };
        Router::closure([
            'path'=>'/cron-jobs/list',
            'route-to'=>$viewJobsFunc
        ]);
        Router::closure([
            'path'=>'/cron-jobs/list/{password}',
            'route-to'=>$viewJobsFunc
        ]);
    }
    private function _setLogEnabled($bool){
        $this->isLogEnabled = $bool === true ? true : false;
    }
    private function _isLogEnabled() {
        return $this->isLogEnabled;
    }
    /**
     * Enable or disable logging for jobs execution. 
     * This method is also used to check if logging is enabled or not.
     * @param boolean $bool If set to true, a log file that contains the details 
     * of the executed jobs will be created in 'logs' folder. Default value 
     * is null.
     * @return boolean If logging is enabled, the method will return true.
     * @since 1.0.1
     */
    public static function execLog($bool=null) {
        if($bool !== null){
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
     * return true. Other than that, the method will return false.
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
            return false;
        }
    }
    /**
     * Creates a daily job to execute every day at specific hour and minute.
     * @param string $time A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive.
     * @param string $name An optional name for the job. Can be null.
     * @param callable $func A function that will be executed once it is the 
     * time to run the job.
     * @param array $funcParams An optional array of parameters which will be passed to 
     * the callback that will be executed when its time to execute the job.
     * @return boolean If the job was created and scheduled, the method will 
     * return true. Other than that, the method will return false.
     * @since 1.0
     */
    public static function dailyJob($time,$name,$func,$funcParams=array()){
        $split = explode(':', $time);
        if(count($split) == 2){
            if(is_callable($func)){
                $job = new CronJob();
                $job->setJobName($name);
                if($job->dailyAt($split[0], $split[1])){
                    $job->setOnExecution($func, $funcParams);
                    return self::scheduleJob($job);
                }
            }
        }
        return false;
    }
    /**
     * Create a job that will be executed once every month.
     * @param int $dayNumber The day of the month at which the job will be 
     * executed on. It can have any value between 1 and 31 inclusive.
     * @param string $time A string that represents the time of the day that 
     * the job will execute on. The format of the time must be 'HH:MM'. where 
     * HH can have any value from '00' up to '23' and 'MM' can have any value 
     * from '00' up to '59'.
     * @param string $name The name of cron job.
     * @param callable $func A function that will be executed when its time to 
     * run the job.
     * @param array $funcParams An optional array of parameters which will be 
     * passed to job function.
     * @return boolean If the job was scheduled, the method will return true. 
     * If not, the method will return false.
     * @since 1.0.3
     */
    public static function monthlyJob($dayNumber,$time,$name,$func,$funcParams=[]) {
        if($dayNumber > 0 && $dayNumber < 32){
            $split = explode(':', $time);
            if(count($split) == 2){
                if(is_callable($func)){
                    $job = new CronJob();
                    $job->setJobName($name);
                    if($job->everyMonthOn($dayNumber, $time)){
                        $job->setOnExecution($func, $funcParams);
                        return self::scheduleJob($job);
                    }
                }
            }
        }
        return false;
    }
    /**
     * Creates a job that will be executed on specific time weekly.
     * @param string $time A string in the format 'd-hh:mm'. 'd' can be a number 
     * between 0 and 6 inclusive or a 3 characters day name such as 'sun'. 0 is 
     * for Sunday and 6 is for Saturday.
     * 'hh' can have any value between 0 and 23 inclusive. mm can have any value 
     * between 0 and 59 inclusive.
     * @param string $name An optional name for the job. Can be null
     * @param callable|null $func A function that will be executed once it is the 
     * time to run the job.
     * @param array $funcParams An optional array of parameters which will be passed to 
     * the function.
     * @return boolean If the job was created and scheduled, the method will 
     * return true. Other than that, the method will return false.
     * @since 1.0
     */
    public static function weeklyJob($time,$name,$func,$funcParams=array()){
        $split1 = explode('-', $time);
        if(count($split1) == 2){
            $job = new CronJob();
            $job->setJobName($name);
            if($job->weeklyOn($split1[0], $split1[1])){
                $job->setOnExecution($func, $funcParams);
                return self::scheduleJob($job);
            }
        }
        return false;
    }
    /**
     * Sets or gets the password that is used to protect the cron instance.
     * The password is used to prevent unauthorized access to execute jobs.
     * @param string $pass If not null, the password will be updated to the 
     * given one.
     * @return string If the password is set, the method will return it. 
     * If not set, the method will return the string 'NO_PASSWORD'.
     * @since 1.0
     */
    public static function password($pass=null) {
        if($pass !== null){
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
     * @return boolean If the job is added, the method will return true.
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
        $retVal = false;
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
                    fwrite($file, 'Job \''.$job->getJobName().'\' was forced to executed at '.date(DATE_RFC1123).". Request source IP: ".Util::getClientIP()."\n");
                }
                else{
                    fwrite($file, 'Job \''.$job->getJobName().'\' automatically executed at '.date(DATE_RFC1123)."\n");
                }
                fclose($file);
            }
        }
    }
}
