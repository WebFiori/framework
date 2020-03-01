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
use webfiori\WebFiori;
use webfiori\entity\router\Router;
use webfiori\entity\Util;
/**
 * A class that is used to manage cron jobs.
 * It is used to create jobs, schedule them and execute them. In order to run 
 * the jobs automatically, the developer must add an entry in the following 
 * formate in crontab:
 * <p><code>* * * * *  /usr/bin/php path/to/WebFiori.php --check-cron &lt;cron-pass&gt;<code></p>
 * Where &lt;cron-pass&gt; is the password 
 * that was set by the developer to protect the jobs from unauthorized access.
 * Note that the path to PHP executable might differ from "/usr/bin/php". 
 * It depends on where the executable has been installed.
 * @author Ibrahim
 * @version 1.0.8
 */
class Cron {
    /**
     * An array that contains strings which acts as log messages.
     * @var array
     * @since 1.0.8 
     */
    private $logsArray;
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
    private static function _get(){
        if(self::$executer === null){
            self::$executer = new Cron();
        }
        return self::$executer;
    }
    /**
     * Returns the time at which jobs check was initialized.
     * @return string The method will return a time string in the format 
     * 'YY-DD HH:MM' where: 
     * <ul>
     * <li>'YY' is month number.</li>
     * <li>'MM' is day number in the current month.</li>
     * <li>'HH' is the hour.</li>
     * <li>'MM' is the minute.</li>
     * </ul> 
     * @since 1.0.7
     */
    public static function timestamp() {
        $month = self::month();
        if($month < 10){
            $month = '0'.$month;
        }
        $day = self::dayOfMonth();
        if($day < 10){
            $day = '0'.$day;
        }
        $hour = self::hour();
        if($hour < 10){
            $hour = '0'.$hour;
        }
        $minute = self::minute();
        if($minute < 10){
            $minute = '0'.$minute;
        }
        return $month.'-'.$day.' '.$hour.':'.$minute;
    }
    /**
     * 
     * @param CronJob|null $job
     * @since 1.0.4
     */
    private function _setActiveJob($job) {
        $this->activeJob = $job;
        if($job !== null){
            self::log('Active job: "'.$job->getJobName().'" ...');
        }
    }
    /**
     * Returns an object that represents the job which is currently being executed.
     * @return CronJob|null If there is a job which is being executed, the 
     * method will return an object of type 'CronJob' that represent it. If 
     * no job is being executed, the method will return null.
     * @since 1.0.4
     */
    public static function activeJob() {
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
    public static function getJob($jobName) {
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
        $this->timestamp = [
            'month'=>intval(date('m')),
            'month-day'=>intval(date('d')),
            'week-day'=>intval(date('w')),
            'hour'=>intval(date('H')),
            'minute'=>intval(date('i'))
        ];
        $this->logsArray = [];
        $this->isLogEnabled = false;
        $this->cronJobsQueue = new Queue();
        $this->_setPassword('');
        Router::other([
            'path'=>'/cron/login',
            'route-to'=>'/entity/cron/CronLoginView.php'
        ]);
        Router::other([
            'path'=>'/cron/apis/{action}',
            'route-to'=>'/entity/cron/CronAPIs.php',
            'as-api'=>true
        ]);
        Router::other([
            'path'=>'/cron',
            'route-to'=>'/entity/cron/CronLoginView.php'
        ]);
        Router::closure([
            'path'=>'/cron/jobs',
            'route-to'=>function(){
                new CronTasksView();
            }
        ]);
        Router::closure([
            'path'=>'/cron/jobs/{job-name}',
            'route-to'=>function(){
                new CronTaskView();
            }
        ]);
    }
    /**
     * Appends a message to the array that contains logged messages.
     * The main aim of the log is to help developers identify the issues which 
     * might cause a job to fail.
     * @param string $message A string that act as a log message. It will be 
     * appended as passed without any changes.
     * @since 1.0.8
     */
    public static function log($message) {
        self::_get()->logsArray[] = $message;
    }
    /**
     * Returns the array that contains logged messages.
     * The array will contain the messages which where logged using the method 
     * <code>Cron::log()</code>
     * @return array An array of strings.
     * @since 1.0.8
     */
    public static function getLogArray() {
        return self::_get()->logsArray;
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
     * The provided password must be 'sha256' hashed string. It is recommended 
     * to hash the password externally then use the hash inside your code.
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
    /**
     * Check each scheduled job and run it if its time to run it.
     * @param string $pass If cron password is set, this value must be 
     * provided. The given value will be hashed inside the body of the 
     * method and then compared with the password which was set. Default 
     * is empty string
     * @param string|null $jobName An optional job name. If specified, only 
     * the given job will be checked. Default is null.
     * @param boolean $force If this attribute is set to true and a job name 
     * was provided, the job will be forced to execute. Default is false.
     * @return string|array If cron password is set and the given one is 
     * invalid, the method will return the string 'INV_PASS'. If 
     * a job name is specified and no job was found which has the given 
     * name, the method will return the string 'JOB_NOT_FOUND'. Other than that, 
     * the method will return an associative array which has the 
     * following indices:
     * <ul>
     * <li><b>total-jobs</b>: Total number of scheduled jobs.</li>
     * <li><b>executed-count</b>: Number of executed jobs.</li>
     * <li><b>successfully-completed</b>: Number of successfully 
     * completed jobs.</li>
     * <li><b>failed</b>: Number of jobs which did not 
     * finish successfully.</li>
     * </ul>
     * @since 1.0.6
     */
    public static function run($pass='',$jobName=null,$force=false) {
        self::log('Running job(s) check...');
        if(Cron::password() != 'NO_PASSWORD' && WebFiori::getWebsiteController()->getSessionVar('cron-login-status') !== true){
            if(hash('sha256',$pass) != Cron::password()){
                return 'INV_PASS';
            }
        }
        $xForce = $force === true;
        $retVal = [
            'total-jobs'=>Cron::jobsQueue()->size(),
            'executed-count'=>0,
            'successfully-completed'=>[],
            'failed'=>[]
        ];
        if($jobName !== null){
            $job = self::getJob(trim($jobName));
            if($job instanceof CronJob){
                if($job->isTime() || $xForce){
                    $job->setIsForced($xForce);
                    self::_get()->_setActiveJob($job);
                }
                if($job->execute($xForce)){
                    self::_get()->_logJobExecution($job,$xForce);
                    $retVal['executed-count']++;
                    if($job->isSuccess() === true){
                        $retVal['successfully-completed'][] = $job->getJobName();
                    }
                    else if($job->isSuccess() === false){
                        $retVal['failed'][] = $job->getJobName();
                    }
                }
                self::_get()->_setActiveJob(null);
            }
            else{
                return 'JOB_NOT_FOUND';
            }
        }
        else{
            while ($job = Cron::jobsQueue()->dequeue()){
                if($job->isTime()){
                    self::_get()->_setActiveJob($job);
                }
                if($job->execute()){
                    self::_get()->_logJobExecution($job,$xForce);
                    $retVal['executed-count']++;
                    if($job->isSuccess() === true){
                        $retVal['successfully-completed'][] = $job->getJobName();
                    }
                    else if($job->isSuccess() === false){
                        $retVal['failed'][] = $job->getJobName();
                    }
                }
                self::_get()->_setActiveJob(null);
            }
        }
        self::log('Check finished.');
        return $retVal;
    }
    
    private function _logJobExecution($job,$forced=false){
        if($this->isLogEnabled){
            $ds = DIRECTORY_SEPARATOR;
            $logFile = ROOT_DIR.$ds.'logs'.$ds.'cron.txt';
            if(Util::isDirectory(ROOT_DIR.$ds.'logs', true)){
                if(!file_exists($logFile)){
                    $file = fopen($logFile, 'w');
                }
                else{
                    $file = fopen($logFile, 'a+');
                }
                if(is_resource($file)){
                    if($forced){
                        fwrite($file, 'Job \''.$job->getJobName().'\' was forced to executed at '.date(DATE_RFC1123).". Request source IP: ".Util::getClientIP()."\n");
                        if($job->isSuccess()){
                            fwrite($file, 'Execution status: Successfully completed.'."\n");
                        }
                        else{
                            fwrite($file, 'Execution status: Failed to completed.'."\n");
                        }
                    }
                    else{
                        fwrite($file, 'Job \''.$job->getJobName().'\' automatically executed at '.date(DATE_RFC1123)."\n");
                        if($job->isSuccess()){
                            fwrite($file, 'Execution status: Successfully completed.'."\n");
                        }
                        else{
                            fwrite($file, 'Execution status: Failed to completed.'."\n");
                        }
                    }
                    fclose($file);
                }
            }
        }
    }
}
