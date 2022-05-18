<?php
/*
 * The MIT License
 *
 * Copyright 2018, WebFiori Framework.
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
namespace webfiori\framework\cron;

use Exception;
use webfiori\collections\Queue;
use webfiori\framework\cli\CLI;
use webfiori\framework\cli\CLICommand;
use webfiori\framework\cron\webServices\CronServicesManager;
use webfiori\framework\router\Router;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\Util;
use webfiori\framework\WebFioriApp;
/**
 * A class that is used to manage scheduled background jobs.
 * 
 * It is used to create jobs, schedule them and execute them. In order to run 
 * the jobs automatically, the developer must add an entry in the following 
 * formate in crontab:
 * <p><code>* * * * *  /usr/bin/php path/to/webfiori --cron check p=&lt;password&gt;</code></p>
 * Where &lt;password&gt; is the password 
 * that was set by the developer to protect the jobs from unauthorized access. 
 * If no password is set, then it can be removed from the command.
 * Note that the path to PHP executable might differ from "/usr/bin/php". 
 * It depends on where the executable has been installed.
 * 
 * @author Ibrahim
 * 
 * @version 1.1.0
 */
class Cron {
    /**
     * The password that is used to access and execute jobs.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $accessPass;
    /**
     * The job which is currently executing.
     * 
     * @var CronJob|null
     * 
     * @since 1.0.4 
     */
    private $activeJob;
    /**
     *
     * @var CLICommand 
     */
    private $command;
    /**
     * A queue which contains all cron jobs.
     * 
     * @var Queue 
     * 
     * @since 1.0
     */
    private $cronJobsQueue;
    /**
     * An instance of 'CronExecuter'
     * 
     * @var Cron 
     * 
     * @since 1.0
     */
    private static $executer;
    /**
     * A variable that is set to true if job execution log is enabled.
     * 
     * @var boolean
     * 
     * @since 1.0.1 
     */
    private $isLogEnabled;
    /**
     *
     * @var type 
     * 
     * @since 1.0.9
     */
    private $jobsNamesArr;
    /**
     * An array that contains strings which acts as log messages.
     * 
     * @var array
     * 
     * @since 1.0.8 
     */
    private $logsArray;
    /**
     * An array that contains current timestamp. 
     * 
     * @var array 
     */
    private $timestamp;
    /**
     * Creates new instance of the class.
     * 
     * @since 1.0
     */
    private function __construct() {
        $this->timestamp = [
            'month' => intval(date('m')),
            'month-day' => intval(date('d')),
            'week-day' => intval(date('w')),
            'hour' => intval(date('H')),
            'minute' => intval(date('i'))
        ];
        $this->jobsNamesArr = [];
        $this->logsArray = [];
        $this->isLogEnabled = false;
        $this->cronJobsQueue = new Queue();
        $this->_setPassword('');
    }
    /**
     * Returns an object that represents the job which is currently being executed.
     * 
     * @return CronJob|null If there is a job which is being executed, the 
     * method will return an object of type 'CronJob' that represent it. 
     * If no job is being executed, the method will return null.
     * 
     * @since 1.0.4
     */
    public static function activeJob() {
        return self::_get()->activeJob;
    }
    /**
     * Creates new job using cron expression.
     * 
     * The job will be created and scheduled only if the given cron expression 
     * is valid. For more information on cron expressions, go to 
     * https://en.wikipedia.org/wiki/Cron#CRON_expression. Note that 
     * the method does not support year field. This means 
     * the expression will have only 5 fields.
     * 
     * @param string $when A cron expression.
     * 
     * @param string $jobName An optional job name.
     *  
     * @param callable $function A function to run when it is the time to execute 
     * the job.
     * 
     * @param array $funcParams An array of parameters that can be passed to the 
     * function. 
     * 
     * @return boolean If the job was created and scheduled, the method will 
     * return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public static function createJob(string $when = '*/5 * * * *', string $jobName = '', $function = '', array $funcParams = []) : bool {
        try {
            $job = new CronJob($when);
            $job->setOnExecution($function, $funcParams);

            if (strlen($jobName) > 0) {
                $job->setJobName($jobName);
            }

            return self::scheduleJob($job);
        } catch (Exception $ex) {
            return false;
        }
    }
    /**
     * Creates a daily job to execute every day at specific hour and minute.
     * 
     * @param string $time A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive.
     * 
     * @param string $name An optional name for the job. Can be null.
     * 
     * @param callable $func A function that will be executed once it is the 
     * time to run the job.
     * 
     * @param array $funcParams An optional array of parameters which will be passed to 
     * the callback that will be executed when its time to execute the job.
     * 
     * @return boolean If the job was created and scheduled, the method will 
     * return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public static function dailyJob(string $time, string $name, $func, array $funcParams = []) {
        $split = explode(':', $time);

        if (count($split) == 2 && is_callable($func)) {
            $job = new CronJob();
            $job->setJobName($name);

            if ($job->dailyAt($split[0], $split[1])) {
                $job->setOnExecution($func, $funcParams);

                return self::scheduleJob($job);
            }
        }

        return false;
    }
    /**
     * Returns the number of current day in the current  month as integer.
     * 
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time.
     * 
     * @return int An integer that represents current day number in 
     * the current month.
     * 
     * @since 1.0.2
     */
    public static function dayOfMonth() : int {
        return self::_get()->timestamp['month-day'];
    }
    /**
     * Returns the number of current day in the current  week as integer.
     * 
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 0 and 6 
     * inclusive. 0 Means Sunday and 6 is for Saturday.
     * 
     * @return int An integer that represents current day number in 
     * the week.
     * 
     * @since 1.0.2
     */
    public static function dayOfWeek() : int {
        return self::_get()->timestamp['week-day'];
    }
    /**
     * Enable or disable logging for jobs execution. 
     * 
     * This method is also used to check if logging is enabled or not. If 
     * execution log is enabled, a log file with the name 'cron.log' will be 
     * created in the folder '/logs'.
     * 
     * @param boolean $bool If set to true, a log file that contains the details 
     * of the executed jobs will be created in 'logs' folder. Default value 
     * is null.
     * 
     * @return boolean If logging is enabled, the method will return true.
     * 
     * @since 1.0.1
     */
    public static function execLog($bool = null) : bool {
        if ($bool !== null) {
            self::_get()->_setLogEnabled($bool);
        }

        return self::_get()->_isLogEnabled();
    }
    /**
     * Returns a job given its name.
     * 
     * @param string $jobName The name of the job.
     * 
     * @return CronJob|null If a job which has the given name was found, 
     * the method will return an object of type 'CronJob' that represents 
     * the job. Other than that, the method will return null.
     * 
     * @since 1.0.5
     */
    public static function getJob(string $jobName) {
        $trimmed = trim($jobName);
        $retVal = null;

        if (strlen($trimmed) != 0) {
            $tempQ = new Queue();

            while ($job = &self::jobsQueue()->dequeue()) {
                $tempQ->enqueue($job);

                if ($job->getJobName() == $trimmed) {
                    $retVal = $job;
                }
            }

            while ($job = &$tempQ->dequeue()) {
                self::scheduleJob($job);
            }
        }

        return $retVal;
    }
    /**
     * Returns an array that contains the names of scheduled jobs.
     * 
     * @return array An array that contains the names of scheduled jobs.
     * 
     * @since 1.0.9
     */
    public static function getJobsNames() : array {
        return self::_get()->jobsNamesArr;
    }
    /**
     * Returns the array that contains logged messages.
     * 
     * The array will contain the messages which where logged using the method 
     * <code>Cron::log()</code>
     * 
     * @return array An array of strings.
     * 
     * @since 1.0.8
     */
    public static function getLogArray() : array {
        return self::_get()->logsArray;
    }
    /**
     * Returns the number of current hour in the day as integer.
     * 
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 0 and 23 
     * inclusive.
     * 
     * @return int An integer that represents current hour number in 
     * the day.
     * @since 1.0.2
     */
    public static function hour() : int {
        return self::_get()->timestamp['hour'];
    }
    /**
     * Creates routes to cron web interface pages.
     * 
     * This method is used to initialize the following routes:
     * <ul>
     * <li>/cron</li>
     * <li>/cron/login</li>
     * <li>/cron/apis/{action}</li>
     * <li>/cron/jobs</li>
     * <li>/cron/jobs/{job-name}</li>
     * </ul>
     * 
     * @since 1.1.0
     */
    public static function initRoutes() {
        Router::addRoute([
            'path' => '/cron/login',
            'route-to' => webUI\CronLoginView::class
        ]);
        Router::addRoute([
            'path' => '/cron/apis/{action}',
            'route-to' => CronServicesManager::class,
            'as-api' => true
        ]);
        Router::addRoute([
            'path' => '/cron',
            'route-to' => webUI\CronLoginView::class
        ]);
        Router::addRoute([
            'path' => '/cron/jobs',
            'route-to' => webUI\CronTasksView::class
        ]);
    }
    /**
     * Returns a queue of all queued jobs.
     * 
     * @return Queue An object of type 'Queue' which contains all queued jobs.
     * 
     * @since 1.0
     */
    public static function jobsQueue() : Queue {
        return self::_get()->_getQueue();
    }
    /**
     * Appends a message to the array that contains logged messages.
     * 
     * The main aim of the log is to help developers identify the issues which 
     * might cause a job to fail. This method can be called in any place to 
     * log a message while the code is executing.
     * 
     * @param string $message A string that act as a log message. It will be 
     * appended as passed without any changes.
     * 
     * @since 1.0.8
     */
    public static function log(string $message) {
        self::_get()->logsArray[] = $message;

        if (self::_get()->command !== null && self::_get()->command->isArgProvided('--show-log')) {
            self::_get()->command->println("%s", $message);
        }
    }
    /**
     * Returns the number of current minute in the current hour as integer.
     * 
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 0 and 59 
     * inclusive.
     * 
     * @return int An integer that represents current minute number in 
     * the current hour.
     * 
     * @since 1.0.2
     */
    public static function minute() : int {
        return self::_get()->timestamp['minute'];
    }
    /**
     * Returns the number of current month as integer.
     * 
     * This method is used by the class 'CronJob' to validate cron job 
     * execution time. The method will always return a value between 1 and 12 
     * inclusive.
     * 
     * @return int An integer that represents current month's number.
     * @since 1.0.2
     */
    public static function month() : int {
        return self::_get()->timestamp['month'];
    }
    /**
     * Create a job that will be executed once every month.
     * 
     * @param int $dayNumber The day of the month at which the job will be 
     * executed on. It can have any value between 1 and 31 inclusive.
     * 
     * @param string $time A string that represents the time of the day that 
     * the job will execute on. The format of the time must be 'HH:MM'. where 
     * HH can have any value from '00' up to '23' and 'MM' can have any value 
     * from '00' up to '59'.
     * 
     * @param string $name The name of cron job.
     * 
     * @param callable $func A function that will be executed when its time to 
     * run the job.
     * 
     * @param array $funcParams An optional array of parameters which will be 
     * passed to job function.
     * 
     * @return boolean If the job was scheduled, the method will return true. 
     * If not, the method will return false.
     * 
     * @since 1.0.3
     */
    public static function monthlyJob(int $dayNumber, string $time, string $name, $func, array $funcParams = []) {
        if ($dayNumber > 0 && $dayNumber < 32) {
            $split = explode(':', $time);

            if (count($split) == 2 && is_callable($func)) {
                $job = new CronJob();
                $job->setJobName($name);

                if ($job->everyMonthOn($dayNumber, $time)) {
                    $job->setOnExecution($func, $funcParams);

                    return self::scheduleJob($job);
                }
            }
        }

        return false;
    }
    /**
     * Sets or gets the password that is used to protect the cron instance.
     * 
     * The password is used to prevent unauthorized access to execute jobs. 
     * The provided password must be 'sha256' hashed string. It is recommended 
     * to hash the password externally then use the hash inside your code.
     * 
     * @param string $pass If not null, the password will be updated to the 
     * given one.
     * 
     * @return string If the password is set, the method will return it. 
     * If not set, the method will return the string 'NO_PASSWORD'.
     * 
     * @since 1.0
     */
    public static function password($pass = null) : string {
        if ($pass !== null) {
            self::_get()->_setPassword($pass);
        }

        return self::_get()->_getPassword();
    }
    /**
     * Register any CRON job which exist in the folder 'jobs' of the application.
     * 
     * Note that this method will register jobs only if the framework is running
     * using CLI or the constant 'CRON_THROUGH_HTTP' is set to true.
     */
    public static function registerJobs() {
        if (CLI::isCLI() || (defined('CRON_THROUGH_HTTP') && CRON_THROUGH_HTTP === true)) {
            WebFioriApp::autoRegister('jobs', function ($job)
            {
                Cron::scheduleJob($job);
            });
        }
    }
    /**
     * Check each scheduled job and run it if its time to run it.
     * 
     * @param string $pass If cron password is set, this value must be 
     * provided. The given value will be hashed inside the body of the 
     * method and then compared with the password which was set. Default 
     * is empty string.
     * 
     * @param string|null $jobName An optional job name. If specified, only 
     * the given job will be checked. Default is null.
     * 
     * @param boolean $force If this attribute is set to true and a job name 
     * was provided, the job will be forced to execute. Default is false.
     * 
     * @param CronCommand $command If cron is run from CLI, this parameter is 
     * provided to set custom execution attributes of a job.
     * 
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
     * 
     * @since 1.0.6
     */
    public static function run(string $pass = '', string $jobName = null, bool $force = false, $command = null) {
        self::_get()->command = $command;
        self::log('Running job(s) check...');
        $activeSession = SessionsManager::getActiveSession();
        $isSessionLogged = $activeSession !== null ? $activeSession->get('cron-login-status') : false;

        if (Cron::password() != 'NO_PASSWORD' && $isSessionLogged !== true && hash('sha256',$pass) != Cron::password()) {
            self::log('Error: Given password is incorrect.');
            self::log('Check finished.');

            return 'INV_PASS';
        }
        $xForce = $force === true;
        $retVal = [
            'total-jobs' => Cron::jobsQueue()->size(),
            'executed-count' => 0,
            'successfully-completed' => [],
            'failed' => []
        ];

        if ($jobName !== null) {
            self::log("Forceing job '$jobName' to execute...");
            $job = self::getJob(trim($jobName));

            if ($job instanceof AbstractJob) {
                self::_runJob($retVal, $job, $xForce, $command);
            } else {
                self::log("Error: No job which has the name '$jobName' is found.");
                self::log('Check finished.');

                return 'JOB_NOT_FOUND';
            }
        } else {
            while ($job = Cron::jobsQueue()->dequeue()) {
                self::_runJob($retVal, $job, $xForce, $command);
            }
        }
        self::log('Check finished.');

        return $retVal;
    }

    /**
     * Adds new job to jobs queue.
     * 
     * @param AbstractJob $job An instance of the class 'AbstractJob'.
     * 
     * @return boolean If the job is added, the method will return true.
     * 
     * @since 1.0
     */
    public static function scheduleJob(AbstractJob $job) : bool {
        return self::_get()->_addJob($job);
    }
    /**
     * Sets the number of day in the month at which the scheduler started to 
     * execute jobs.
     * 
     * This method is helpful for the developer to test if jobs will run on 
     * the specified time or not.
     * 
     * @param int $dayOfMonth The number of day. 1 for the first day of month and 
     * 31 for the last day of the month.
     * 
     * @since 1.1.1
     */
    public function setDayOfMonth(int $dayOfMonth) {
        $asInt = intval($dayOfMonth);

        if ($asInt >= 1 && $asInt <= 31) {
            self::_get()->timestamp['month-day'] = $asInt;
        }
    }
    /**
     * Sets the value of the week at which the scheduler started to 
     * run.
     * 
     * This method is helpful for the developer to test if jobs will run on 
     * the specified time or not.
     * 
     * @param int $val Numeric representation of the day of the week. 
     * 0 for Sunday through 6 for Saturday.
     * 
     * @since 1.1.1
     */
    public static function setDayOfWeek(int $val) {
        $asInt = intval($val);

        if ($asInt >= 0 && $asInt <= 6) {
            self::_get()->timestamp['week-day'] = $asInt;
        }
    }
    /**
     * Sets the hour at which the scheduler started to 
     * execute jobs.
     * 
     * This method is helpful for the developer to test if jobs will run on 
     * the specified time or not.
     * 
     * @param int $hour The number of hour. Can be any value between 1 and 
     * 23 inclusive.
     * 
     * @since 1.1.1
     */
    public function setHour(int $hour) {
        $asInt = intval($hour);

        if ($asInt >= 1 && $asInt <= 23) {
            self::_get()->timestamp['hour'] = $asInt;
        }
    }
    /**
     * Sets the minute at which the scheduler started to 
     * execute jobs.
     * 
     * This method is helpful for the developer to test if jobs will run on 
     * the specified time or not.
     * 
     * @param int $minute The number of the minute. Can be any value from 
     * 1 to 59.
     * 
     * @since 1.1.1
     */
    public function setMinute(int $minute) {
        $asInt = intval($minute);

        if ($asInt >= 1 && $asInt <= 59) {
            self::_get()->timestamp['minute'] = $asInt;
        }
    }
    /**
     * Sets the month at which the scheduler started to 
     * execute jobs.
     * 
     * This method is helpful for the developer to test if jobs will run on 
     * the specified time or not.
     * 
     * @param int $month The number of the month. Can be any value 
     * between 1 and 12 inclusive.
     * 
     * @since 1.1.1
     */
    public function setMonth(int $month) {
        $asInt = intval($month);

        if ($asInt >= 1 && $asInt <= 31) {
            self::_get()->timestamp['month'] = $asInt;
        }
    }
    /**
     * Returns the time at which jobs check was initialized.
     * 
     * @return string The method will return a time string in the format 
     * 'YY-DD HH:MM' where: 
     * <ul>
     * <li>'YY' is month number.</li>
     * <li>'MM' is day number in the current month.</li>
     * <li>'HH' is the hour.</li>
     * <li>'MM' is the minute.</li>
     * </ul> 
     * 
     * @since 1.0.7
     */
    public static function timestamp() : string {
        $month = self::month();

        if ($month < 10) {
            $month = '0'.$month;
        }
        $day = self::dayOfMonth();

        if ($day < 10) {
            $day = '0'.$day;
        }
        $hour = self::hour();

        if ($hour < 10) {
            $hour = '0'.$hour;
        }
        $minute = self::minute();

        if ($minute < 10) {
            $minute = '0'.$minute;
        }

        return $month.'-'.$day.' '.$hour.':'.$minute;
    }
    /**
     * Creates a job that will be executed on specific time weekly.
     * 
     * @param string $time A string in the format 'd-hh:mm'. 'd' can be a number 
     * between 0 and 6 inclusive or a 3 characters day name such as 'sun'. 0 is 
     * for Sunday and 6 is for Saturday.
     * 'hh' can have any value between 0 and 23 inclusive. mm can have any value 
     * between 0 and 59 inclusive.
     * 
     * @param string $name An optional name for the job. Can be null.
     * 
     * @param callable|null $func A function that will be executed once it is the 
     * time to run the job.
     * 
     * @param array $funcParams An optional array of parameters which will be passed to 
     * the function.
     * 
     * @return boolean If the job was created and scheduled, the method will 
     * return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public static function weeklyJob(string $time, string $name, $func, array $funcParams = []) {
        $split1 = explode('-', $time);

        if (count($split1) == 2) {
            $job = new CronJob();
            $job->setJobName($name);

            if ($job->weeklyOn($split1[0], $split1[1])) {
                $job->setOnExecution($func, $funcParams);

                return self::scheduleJob($job);
            }
        }

        return false;
    }
    /**
     * 
     * @param AbstractJob $job
     * @return type
     * @since 1.0
     */
    private function _addJob($job) {
        $retVal = false;

        if ($job instanceof AbstractJob) {
            if ($job->getJobName() == 'CRON-JOB') {
                $job->setJobName('job-'.$this->jobsQueue()->size());
            }
            $retVal = $this->cronJobsQueue->enqueue($job);

            if ($retVal === true && !in_array($job->getJobName(), $this->jobsNamesArr)) {
                $this->jobsNamesArr[] = $job->getJobName();
            }
        }

        return $retVal;
    }
    /**
     * Returns a singleton of the class CronExecuter.
     * @return Cron
     * @since 1.0
     */
    private static function _get() {
        if (self::$executer === null) {
            self::$executer = new Cron();
        }

        return self::$executer;
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function _getPassword() {
        if ($this->accessPass == '') {
            return 'NO_PASSWORD';
        }

        return $this->accessPass;
    }
    /**
     * 
     * @return Queue
     * @since 1.0
     */
    private function _getQueue() {
        return $this->cronJobsQueue;
    }
    private function _isLogEnabled() {
        return $this->isLogEnabled;
    }
    private function _logExecHelper($forced, $job, $file) {
        if ($forced) {
            fwrite($file, 'Job \''.$job->getJobName().'\' was forced to executed at '.date(DATE_RFC1123).". Request source IP: ".Util::getClientIP()."\n");

            if ($job->isSuccess()) {
                fwrite($file, 'Execution status: Successfully completed.'."\n");
            } else {
                fwrite($file, 'Execution status: Failed to completed.'."\n");
            }
        } else {
            fwrite($file, 'Job \''.$job->getJobName().'\' automatically executed at '.date(DATE_RFC1123)."\n");

            if ($job->isSuccess()) {
                fwrite($file, 'Execution status: Successfully completed.'."\n");
            } else {
                fwrite($file, 'Execution status: Failed to completed.'."\n");
            }
        }
    }

    private function _logJobExecution($job,$forced = false) {
        if ($this->isLogEnabled) {
            $logsPath = ROOT_DIR.DS.'app'.DS.'sto'.DS.'logs';
            $logFile = $logsPath.DS.'cron.log';

            if (Util::isDirectory($logsPath, true)) {
                if (!file_exists($logFile)) {
                    $file = fopen($logFile, 'w');
                } else {
                    $file = fopen($logFile, 'a+');
                }

                if (is_resource($file)) {
                    $this->_logExecHelper($forced, $job, $file);
                    fclose($file);
                }
            }
        }
    }
    /**
     * 
     * @param type $retVal
     * @param AbstractJob $job
     * @param type $xForce
     */
    private static function _runJob(&$retVal, $job, $xForce, $command = null) {
        if ($job->isTime() || $xForce) {
            if ($command !== null) {
                $job->setCommand($command);

                foreach ($job->getExecArgsNames() as $attr) {
                    $command->addArg($attr);
                    $val = $command->getArgValue($attr);

                    if ($val !== null) {
                        $_POST[$attr] = $val;
                    }
                }
            }
            self::_get()->_setActiveJob($job);
        }

        if ($job->exec($xForce)) {
            self::_get()->_logJobExecution($job,$xForce);
            $retVal['executed-count']++;

            if ($job->isSuccess() === true) {
                $retVal['successfully-completed'][] = $job->getJobName();
            } else if ($job->isSuccess() === false) {
                $retVal['failed'][] = $job->getJobName();
            }
        }
        self::_get()->_setActiveJob(null);
    }
    /**
     * 
     * @param AbstractJob|null $job
     * @since 1.0.4
     */
    private function _setActiveJob($job) {
        $this->activeJob = $job;

        if ($job !== null) {
            self::log('Active job: "'.$job->getJobName().'" ...');
        }
    }
    private function _setLogEnabled($bool) {
        $this->isLogEnabled = $bool === true;
    }
    /**
     * 
     * @param type $pass
     * @since 1.0
     */
    private function _setPassword($pass) {
        if (gettype($pass) == 'string') {
            $this->accessPass = $pass;
        }
    }
}
