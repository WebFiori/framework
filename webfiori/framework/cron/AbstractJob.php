<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
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

use Error;
use Exception;
use webfiori\collections\Queue;
use webfiori\framework\cli\CronCommand;
use webfiori\framework\exceptions\InvalidCRONExprException;
/**
 * An abstract class that contains basic functionality for implementing cron 
 * jobs.
 *
 * @author Ibrahim
 * 
 * @version 1.0.1
 */
abstract class AbstractJob {
    /**
     * A constant that indicates a sub cron expression is of type 'multi-value'.
     * 
     * @since 1.0
     */
    const ANY_VAL = '*';
    /**
     * A constant that indicates a sub cron expression is invalid.
     * 
     * @since 1.0
     */
    const INV_VAL = 'inv';
    /**
     * An associative array which holds the names and the numbers of year 
     * months.
     * 
     * @since 1.0
     */
    const MONTHS_NAMES = [
        'JAN' => 1,'FEB' => 2,'MAR' => 3,'APR' => 4,'MAY' => 5,'JUN' => 6,
        'JUL' => 7,'AUG' => 8,'SEP' => 9,'OCT' => 10,'NOV' => 11,'DEC' => 12
    ];
    /**
     * A constant that indicates a sub cron expression is of type 'range'.
     * 
     * @since 1.0
     */
    const RANGE_VAL = 'r';
    /**
     * A constant that indicates a sub cron expression is of type 'specific value'.
     * 
     * @since 1.0
     */
    const SPECIFIC_VAL = 'spe';
    /**
     * A constant that indicates a sub cron expression is of type 'step value'.
     * 
     * @since 1.0
     */
    const STEP_VAL = 's';
    /**
     * An associative array which holds the names and the numbers of week 
     * days.
     * 
     * @since 1.0
     */
    const WEEK_DAYS = [
        'SAT' => 6,'SUN' => 0,'MON' => 1,'TUE' => 2,'WED' => 3,'THU' => 4,'FRI' => 5
    ];
    /**
     * The command which is used to execute the job.
     * 
     * @var CronCommand
     * 
     * @since 1.0.1 
     */
    private $command;
    /**
     * The full cron expression.
     * 
     * @var type 
     * 
     * @since 1.0
     */
    private $cronExpr;
    /**
     * An array that contains custom attributes which can be provided on 
     * job execution.
     * 
     * @var array 
     * 
     * @since 1.0
     */
    private $customAttrs;
    /**
     * An array which contains the events that will be executed if it is the time 
     * to execute the job.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $events;
    /**
     * A boolean which is set to true if the job is forced to execute.
     * 
     * @var boolean 
     * 
     * @since 1.0
     */
    private $isForced;
    /**
     * A boolean which is set to true if the job was 
     * successfully executed.
     * 
     * @var boolean 
     * 
     * @since 1.0
     */
    private $isSuccess;
    /**
     * An array which contains all job details after parsing cron expression.
     * 
     * @var array 
     * 
     * @since 1.0
     */
    private $jobDetails;
    /**
     * A name for the cron job.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $jobName;
    /**
     * Creates new instance of the class.
     * 
     * @param string $jobName The name of the job.
     * 
     * @param string $when A cron expression. An exception will be thrown if 
     * the given expression is invalid. Default is '* * * * *' which means run 
     * the job every minute.
     * 
     * @throws Exception
     * 
     * @since 1.0
     */
    public function __construct($jobName = '', $when = '* * * * *') {
        $this->setJobName($jobName);
        $this->customAttrs = [];
        $this->isSuccess = false;
        $this->jobDetails = [
            'minutes' => [],
            'hours' => [],
            'days-of-month' => [],
            'months' => [],
            'days-of-week' => []
        ];
        $this->events = [
            'on' => [
                'func' => null,
                'params' => []
            ],
            'on-failure' => [
                'func' => null,
                'params' => []
            ]
        ];

        if ($when !== null) {
            if ($this->cron($when) === false) {
                throw new InvalidCRONExprException('Invalid cron expression: \''.$when.'\'.');
            }
        } else {
            $this->cron();
        }
        $this->setIsForced(false);
    }
    /**
     * Adds new execution argument.
     * 
     * An execution argument is an argument that can be supplied to the 
     * job in case of force execute. They will appear in cron control panel 
     * as a table. They also can be provided to the job when executing it 
     * throw CLI as 'arg-name="argVal".
     * The argument name must follow the following rules:
     * <ul>
     * <li>Must be non-empty string.</li>
     * <li>Must not contain '#', '?', '&', '=' or space.</li>
     * </ul>
     * 
     * @param string $name The name of the attribute.
     * 
     * @since 1.0
     */
    public function addExecutionArg($name) {
        $trimmed = trim($name);
        $isValid = $this->_validateAttrName($trimmed);

        if ($isValid && !in_array($trimmed, $this->customAttrs)) {
            $this->customAttrs[] = $trimmed;
        }
    }
    /**
     * Adds multiple execution arguments at one shot.
     * 
     * @param array $argsArr An array that contains the names of the 
     * arguments.
     * 
     * @since 1.0
     */
    public function addExecutionArgs(array $argsArr) {
        foreach ($argsArr as $argName) {
            $this->addExecutionArg($argName);
        }
    }
    /**
     * Run some routines after the job is executed.
     * 
     * The developer can implement this method to perform some actions after the 
     * job is executed. Note that the method will get executed if the job is failed 
     * or successfully completed. It is optional to implement that method. The developer can 
     * leave the body of the method empty.
     * 
     * @since 1.0 
     */
    public abstract function afterExec();
    /**
     * Schedules a job using specific cron expression.
     * 
     * For more information on cron expressions, go to 
     * https://en.wikipedia.org/wiki/Cron#CRON_expression. Note that 
     * the method does not support year field. This means 
     * the expression will have only 5 fields. Notes about the expression: 
     * <ul>
     * <li>Step values are not supported for months.</li>
     * <li>Step values are not supported for day of week.</li>
     * <li>Step values are not supported for day of month.</li>
     * </ul>
     * 
     * @param string $when A cron expression (such as '8 15 * * 1'). Default 
     * is '* * * * *' which means run the job every minute.
     * 
     * @return boolean If the given cron expression is valid, the method will 
     * set the time of cron job as specified by the expression and return 
     * true. If the expression is invalid, the method will return false.
     * 
     * @since 1.0
     */
    public function cron($when = '* * * * *') {
        $retVal = false;
        $trimmed = trim($when);
        $split = explode(' ', $trimmed);
        $count = count($split);

        if ($count == 5) {
            $minutesValidity = $this->_checkMinutes($split[0]);
            $hoursValidity = $this->_checkHours($split[1]);
            $daysOfMonthValidity = $this->_dayOfMonth($split[2]);
            $monthValidity = $this->_checkMonth($split[3]);
            $daysOfWeekValidity = $this->_checkDayOfWeek($split[4]);

            if (!($minutesValidity === false || 
               $hoursValidity === false || 
               $daysOfMonthValidity === false ||
               $monthValidity === false || 
               $daysOfWeekValidity === false)) {
                $this->jobDetails = [
                    'minutes' => $minutesValidity,
                    'hours' => $hoursValidity,
                    'days-of-month' => $daysOfMonthValidity,
                    'months' => $monthValidity,
                    'days-of-week' => $daysOfWeekValidity
                ];
                $retVal = true;
                $this->cronExpr = $when;
            }
        }

        return $retVal;
    }
    /**
     * Schedules a cron job to run daily at specific hour and minute.
     * 
     * The job will be executed every day at the given hour and minute. The 
     * function uses 24 hours mode. If no parameters are given, 
     * The default time is 00:00 which means that the job will be executed 
     * daily at midnight.
     * 
     * @param int $hour A number between 0 and 23 inclusive. 0 Means daily at 
     * 12:00 AM and 23 means at 11:00 PM. Default is 0.
     * @param int $minute A number between 0 and 59 inclusive. Represents the 
     * minute part of an hour. Default is 0.
     * 
     * @return boolean If job time is set, the method will return true. If 
     * not set, the method will return false. It will not set only if the 
     * given time is not correct.
     * 
     * @since 1.0
     */
    public function dailyAt($hour = 0,$minute = 0) {
        if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
            return $this->cron($minute.' '.$hour.' * * *');
        }

        return false;
    }
    /**
     * Schedules a cron job to run every hour.
     * 
     * The job will run at the start of the hour.
     * 
     * @since 1.0.2
     */
    public function everyHour() {
        $this->cron('0 * * * *');
    }
    /**
     * Schedules a cron job to run every month on specific day and time.
     * 
     * @param int $dayNum The number of the day. It can be any value between 
     * 1 and 31 inclusive.
     * 
     * @param string $time A day time string in the form 'hh:mm' in 24 hours mode.
     * 
     * @return boolean If the time for the cron job is set, the method will 
     * return true. If not, it will return false.
     * 
     * @since 1.0.1
     */
    public function everyMonthOn($dayNum = 1,$time = '00:00') {
        if ($dayNum >= 1 && $dayNum <= 31) {
            $timeSplit = explode(':', $time);

            if (count($timeSplit) == 2) {
                $hour = intval($timeSplit[0]);
                $minute = intval($timeSplit[1]);

                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                    return $this->cron($minute.' '.$hour.' '.$dayNum.' * *');
                }
            }
        }

        return false;
    }
    /**
     * Execute the event which should run when it is time to execute the job. 
     * 
     * This method will be called automatically when cron URL is accessed. The 
     * method will check if it is time to execute the associated event or 
     * not. If it is the time, The event will be executed. If 
     * the job is forced to execute, the event that is associated with the 
     * job will be executed even if it is not the time to execute the job.
     * 
     * @param boolean $force If set to true, the job will be forced to execute 
     * even if it is not job time. Default is false.
     * 
     * @return boolean If the event that is associated with the job is executed, 
     * the method will return true (Even if the job did not finish successfully).
     * If it is not executed, the method will return false. 
     * 
     * @since 1.0
     */
    public function exec($force = false) {
        $xForce = $force === true;
        $retVal = false;
        $this->setIsForced($xForce);

        if ($xForce || $this->isTime()) {
            $isSuccessRun = $this->_callMethod('execute');
            $retVal = true;
            $this->isSuccess = $isSuccessRun === true || $isSuccessRun === null;

            if (!$this->isSuccess()) {
                $this->_callMethod('onFail');
            } else {
                $this->_callMethod('onSuccess');
            }
            $this->_callMethod('afterExec');
        }

        return $retVal;
    }
    /**
     * Execute the job.
     * 
     * The code that will be in the body of that method is the code that will be 
     * get executed if it is time to run the job or the job is forced to 
     * executed. The developer must implement this method in a way it returns null or true 
     * if the job is executed successfully. If the implementation of the method 
     * throws an exception, the job will be considered as failed.
     * 
     * @return boolean|null If the job successfully completed, the method should 
     * return null or true. If the job failed, the method should return false.
     * 
     * @since 1.0
     */
    public abstract function execute();
    /**
     * Returns the value of a custom execution argument.
     * 
     * The value of the argument can be supplied through the table that will 
     * appear in cron control panel. If the execution is performed through 
     * CLI, the value of the argument can be supplied to the job as arg-name="Arg Val".
     * 
     * @param string $name the name of execution argument.
     * 
     * @return string|null If the argument does exist on the job and its value 
     * is provided, the method will return its value. If it is not provided or 
     * it does not exist on the job, the method will return null.
     * 
     * @since 1.0
     */
    public function getArgValue($name) {
        $trimmed = trim($name);
        $args = $this->getExecArgs();

        if (isset($args[$trimmed])) {
            return $args[$trimmed];
        }

        return null;
    }
    /**
     * Returns the command that was used to execute the job.
     * 
     * Note that the command will be null if not executed from CLI environment.
     * 
     * @return CronCommand|null 
     * 
     * @since 1.0.1
     */
    public function getCommand() {
        return $this->command;
    }
    /**
     * Returns an associative array that contains the values of 
     * custom execution parameters.
     * 
     * Note that the method will filter the values using the filter FILTER_SANITIZE_STRING.
     * 
     * @return array An associative array. The keys are attributes values and 
     * the values are the values which are given as input. If a value 
     * is not provided, it will be set to null.
     * 
     * @since 1.0
     */
    public function getExecArgs() {
        $retVal = [];

        foreach ($this->customAttrs as $attrName) {
            if (isset($_POST[$attrName])) {
                $filtered = filter_var(urldecode($_POST[$attrName]), FILTER_SANITIZE_STRING);

                if ($filtered !== false) {
                    $retVal[$attrName] = $filtered;
                } else {
                    $retVal[$attrName] = null;
                }
            } else {
                $retVal[$attrName] = null;
            }
        }

        return $retVal;
    }
    /**
     * Returns an array that contains the names of added custom 
     * execution attributes.
     * 
     * @return array An indexed array that contains all added 
     * custom execution attributes values.
     * 
     * @since 1.0
     */
    public function getExecArgsNames() {
        return $this->customAttrs;
    }
    /**
     * Returns the cron expression which is associated with the job.
     * 
     * @return string The cron expression which is associated with the job.
     * 
     * @since 1.0
     */
    public function getExpression() {
        return $this->cronExpr;
    }
    /**
     * Returns an associative array which contains details about the timings 
     * at which the job will be executed.
     * 
     * @return array The array will have the following indices: 
     * <ul>
     * <li><b>minutes</b>: Contains sub arrays which has info about the minutes 
     * at which the job will be executed.</li>
     * <li><b>hours</b>: Contains sub arrays which has info about the hours 
     * at which the job will be executed.</li>
     * <li><b>days-of-month</b>: Contains sub arrays which has info about the days of month 
     * at which the job will be executed.</li>
     * <li><b>months</b>: Contains sub arrays which has info about the months 
     * at which the job will be executed.</li>
     * <li><b>days-of-week</b>: Contains sub arrays which has info about the days of week 
     * at which the job will be executed.</li>
     * </ul>
     * 
     * @since 1.0
     */
    public function getJobDetails() {
        return $this->jobDetails;
    }
    /**
     * Returns the name of the job.
     * 
     * The name is used to make different jobs unique. Each job must 
     * have its own name. Also, the name of the job is used to force job 
     * execution. It can be supplied as a part of cron URL. 
     * 
     * @return string The name of the job. If no name is set, the function will return 
     * 'CRON-JOB'.
     * 
     * @since 1.0
     */
    public function getJobName() {
        return $this->jobName;
    }
    /**
     * Checks if current day of month in time is a day at which the job must be 
     * executed.
     * 
     * @return boolean The method will return true if the current day of month in 
     * time is a day at which the job must be executed.
     * 
     * @since 1.0
     */
    public function isDayOfMonth() {
        $monthDaysArr = $this->jobDetails['days-of-month'];

        if ($monthDaysArr['every-day'] === true) {
            $retVal = true;
        } else {
            $retVal = false;
            $current = Cron::dayOfMonth();
            $ranges = $monthDaysArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $days = $monthDaysArr['at-every-x-day'];
                $retVal = in_array($current, $days);
            }
        }

        return $retVal;
    }
    /**
     * Checks if current day of week in time is a day at which the job must be 
     * executed.
     * 
     * @return boolean The method will return true if the current day of week in 
     * time is a day at which the job must be executed.
     * 
     * @since 1.0
     */
    public function isDayOfWeek() {
        $daysArr = $this->jobDetails['days-of-week'];

        if ($daysArr['every-day'] === true) {
            $retVal = true;
        } else {
            $retVal = false;
            $current = Cron::dayOfWeek();
            $ranges = $daysArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $days = $daysArr['at-x-day'];
                $retVal = in_array($current, $days);
            }
        }

        return $retVal;
    }
    /**
     * Checks if the job is forced to execute or not.
     * 
     * @return boolean If the job was forced to execute, the method will return 
     * true. Other than that, it will return false.
     * 
     * @since 1.0
     */
    public function isForced() {
        return $this->isForced;
    }
    /**
     * Checks if current hour in time is an hour at which the job must be 
     * executed.
     * 
     * @return boolean The method will return true if the current hour in 
     * time is an hour at which the job must be executed.
     * 
     * @since 1.0
     */
    public function isHour() {
        $hoursArr = $this->jobDetails['hours'];

        if ($hoursArr['every-hour'] === true) {
            $retVal = true;
        } else {
            $retVal = false;
            $current = Cron::hour();
            $ranges = $hoursArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $retVal = $this->isHourHelper($hoursArr, $current);
            }
        }

        return $retVal;
    }
    /**
     * Checks if current minute in time is a minute at which the job must be 
     * executed.
     * 
     * @return boolean The method will return true if the current minute in 
     * time is a minute at which the job must be executed.
     * 
     * @since 1.0
     */
    public function isMinute() {
        $minuteArr = $this->jobDetails['minutes'];

        if ($minuteArr['every-minute'] === true) {
            $retVal = true;
        } else {
            $retVal = false;
            $current = Cron::minute();
            $ranges = $minuteArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $retVal = $this->isMinuteHelper($minuteArr, $current);
            }
        }

        return $retVal;
    }
    /**
     * Checks if current month in time is a month at which the job must be 
     * executed.
     * 
     * @return boolean The method will return true if the current month in 
     * time is a month at which the job must be executed.
     * 
     * @since 1.0
     */
    public function isMonth() {
        $monthsArr = $this->jobDetails['months'];

        if ($monthsArr['every-month'] === true) {
            $retVal = true;
        } else {
            $retVal = false;
            $current = intval(date('m'));
            $ranges = $monthsArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $months = $monthsArr['at-x-month'];
                $retVal = in_array($current, $months);
            }
        }

        return $retVal;
    }
    /**
     * Returns true if the job was executed successfully.
     * 
     * The value returned by this method will depends on the return value 
     * of the value which is returned by the method AbstractJob::execute(). 
     * If the method returned null or true, then it means the job 
     * was successfully executed. If it returns false, this means the job did 
     * not execute successfully. If it throws an exception, then the job is 
     * not successfully completed.
     * 
     * @return boolean True if the job was executed successfully. False 
     * if not.
     * 
     * @since 1.0
     */
    public function isSuccess() {
        return $this->isSuccess;
    }
    /**
     * Checks if its time to execute the job or not.
     * 
     * @return boolean If its time to execute the job, the method will return true. 
     * If not, it will return false.
     * 
     * @since 1.0
     */
    public function isTime() {
        return $this->isMinute() && $this->isHour() && $this->isDayOfMonth() && $this->isMonth() && $this->isDayOfWeek();
    }
    /**
     * Run some routines if the job is executed and failed to completed successfully.
     * 
     * The status of failure or success depends on the implementation of the method 
     * AbstractJob::execute().
     * The developer can implement this method to take actions after the 
     * job is executed and failed to completed. 
     * It is optional to implement that method. The developer can 
     * leave the body of the method empty.
     * 
     * @since 1.0 
     */
    public abstract function onFail();
    /**
     * Schedules a job to run at specific day and time in a specific month.
     * 
     * @param int|string $monthNameOrNum Month number from 1 to 12 inclusive 
     * or 3 letters month name. Default is 'jan'.
     * 
     * @param int $dayNum The number of day in the month starting from 1 up to 
     * 31 inclusive. Default is 1.
     * 
     * @param string $time A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive. 
     * default is '00:00'.
     * 
     * @return boolean If the time for the cron job is set, the method will 
     * return true. If not, it will return false.
     * 
     * @since 1.0
     */
    public function onMonth($monthNameOrNum = 'jan',$dayNum = 1,$time = '00:00') {
        if (gettype($dayNum) == 'string') {
            $trimmed = trim($dayNum);

            if (in_array($trimmed, ['0','1','2','3','4','5','6','7','8','9',
                '10','11','12','13','14','15','16','17','18','19',
                '20','21','22','23','24','25','26','27','28','29','30','31'])) {
                $dayNum = intval($trimmed);
            } else {
                return false;
            }
        }

        if ($dayNum >= 1 && $dayNum <= 31) {
            $timeSplit = explode(':', $time);

            if (count($timeSplit) == 2) {
                $hour = intval($timeSplit[0]);
                $minute = intval($timeSplit[1]);

                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                    $uMonth = strtoupper($monthNameOrNum);

                    if (in_array($uMonth, array_keys(self::MONTHS_NAMES))) {
                        $monthNum = self::MONTHS_NAMES[$uMonth];

                        return $this->cron($minute.' '.$hour.' '.$dayNum.' '.$monthNum.' *');
                    } else {
                        return $this->onMonthHelper($monthNameOrNum, $minute, $hour, $dayNum);
                    }
                }
            }
        }

        return false;
    }
    /**
     * Run some routines if the job is executed and completed successfully.
     * 
     * The status of failure or success depends on the implementation of the method 
     * AbstractJob::execute().
     * The developer can implement this method to perform actions after the 
     * job is executed and failed to completed. 
     * It is optional to implement that method. The developer can 
     * leave the body of the method empty.
     * 
     * @since 1.0 
     */
    public abstract function onSuccess();
    /**
     * Associate the job with the command that was used to execute the job.
     * 
     * @param CronCommand $command
     * 
     * @since 1.0.1
     */
    public function setCommand(CronCommand $command) {
        $this->command = $command;
    }
    /**
     * Sets an optional name for the job.
     * 
     * The name is used to make different jobs unique. Each job must 
     * have its own name. Also, the name of the job is used to force job 
     * execution. It can be supplied as a part of cron URL. 
     * 
     * @param string $name The name of the job.
     * 
     * @since 1.0
     */
    public function setJobName($name) {
        $trimmed = trim($name);
        $this->getJobName();

        if (strlen($trimmed) > 0) {
            $tempJobsQueue = new Queue();
            $nameTaken = false;

            while ($job = Cron::jobsQueue()->dequeue()) {
                if ($job->getJobName() == $trimmed) {
                    $nameTaken = true;
                }
                $tempJobsQueue->enqueue($job);
            }

            while ($job = $tempJobsQueue->dequeue()) {
                Cron::scheduleJob($job);
            }

            if (!$nameTaken) {
                $this->jobName = $trimmed;
            } else {
                $randF = is_callable('') ? 'random_int' : 'rand';
                $this->setJobName($trimmed.'-'.call_user_func($randF, 0, 1000));
            }
        }
    }
    /**
     * Schedules a job to run weekly at specific week day and time.
     * 
     * @param int $dayNameOrNum A 3 letter day name (such as 'sun' 
     * or 'tue') or a day number from 0 to 6. 0 for sunday. Default is 0.
     * 
     * @param string $time A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive. 
     * default is '00:00'.
     * 
     * @return boolean If the time for the cron job is set, the method will 
     * return true. If not, it will return false.
     * 
     * @since 1.0
     */
    public function weeklyOn($dayNameOrNum = 0,$time = '00:00') {
        $uDayName = strtoupper($dayNameOrNum);

        if (in_array($uDayName, array_keys(self::WEEK_DAYS))) {
            return $this->_weeklyOn(self::WEEK_DAYS[$uDayName], $time);
        } else {
            if (gettype($dayNameOrNum) == 'string') {
                $trimmed = trim($dayNameOrNum);

                if (in_array($trimmed, ['0','1','2','3','4','5','6'])) {
                    $dayNameOrNum = intval($trimmed);
                } else {
                    return false;
                }
            }

            if ($dayNameOrNum >= 0 && $dayNameOrNum <= 6) {
                return $this->_weeklyOn($dayNameOrNum, $time);
            }
        }

        return false;
    }
    /**
     * Calls one of the abstract methods of the class.
     * 
     * This method is only used by the method AbstractJob::exec().
     * 
     * @param string $fName The name of the method.
     * 
     * @return null|boolean
     */
    private function _callMethod($fName) {
        Cron::log('Calling the method '.get_class($this)."::$fName()");
        try {
            return $this->$fName();
        } catch (Exception $ex) {
            $this->_logExeException($ex, $fName);

            return false;
        } catch (Error $ex) {
            $this->_logExeException($ex, $fName);

            return false;
        }

        return null;
    }
    /**
     * 
     * @param type $dayOfWeekField
     * @return boolean
     * @since 1.0
     */
    private function _checkDayOfWeek($dayOfWeekField) {
        $isValidExpr = true;
        $split = explode(',', $dayOfWeekField);
        $dayAttrs = [
            'every-day' => false,
            'at-x-day' => [],
            'at-range' => []
        ];

        foreach ($split as $subExpr) {
            $exprType = $this->_getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $dayAttrs['every-day'] = true;
            } else {
                if ($exprType == self::INV_VAL) {
                    $isValidExpr = false;
                    break;
                } else if ($exprType == self::RANGE_VAL) {
                    $range = explode('-', $subExpr);
                    $start = in_array(strtoupper($range[0]), array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[strtoupper($range[0])] : intval($range[0]);
                    $end = in_array(strtoupper($range[1]), array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[strtoupper($range[1])] : intval($range[1]);

                    if ($start < $end) {
                        if ($start >= 0 && $start < 6) {
                            if ($end >= 0 && $end <= 6) {
                                $dayAttrs['at-range'][] = [$start,$end];
                            } else {
                                $isValidExpr = false;
                                break;
                            }
                        } else {
                            $isValidExpr = false;
                            break;
                        }
                    } else {
                        $isValidExpr = false;
                        break;
                    }
                } else if ($exprType == self::STEP_VAL) {
                    $isValidExpr = false;
                } else if ($exprType == self::SPECIFIC_VAL) {
                    $subExpr = strtoupper($subExpr);
                    $value = in_array($subExpr, array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[$subExpr] : intval($subExpr);

                    if ($value >= 0 && $value <= 6) {
                        $dayAttrs['at-x-day'][] = $value;
                    } else {
                        $isValidExpr = false;
                        break;
                    }
                }
            }
        }

        if ($isValidExpr !== true) {
            $dayAttrs = false;
        }

        return $dayAttrs;
    }
    /**
     * 
     * @param type $hoursField
     * @return boolean
     * @since 1.0
     */
    private function _checkHours($hoursField) {
        $isValidExpr = true;
        $split = explode(',', $hoursField);
        $hoursAttrs = [
            'every-hour' => false,
            'every-x-hours' => [],
            'at-every-x-hour' => [],
            'at-range' => []
        ];

        foreach ($split as $subExpr) {
            $exprType = $this->_getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $hoursAttrs['every-hour'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);

                if ($start < $end) {
                    if ($start >= 0 && $start < 24) {
                        if ($end >= 0 && $end < 24) {
                            $hoursAttrs['at-range'][] = [$start,$end];
                        } else {
                            $isValidExpr = false;
                            break;
                        }
                    } else {
                        $isValidExpr = false;
                        break;
                    }
                } else {
                    $isValidExpr = false;
                    break;
                }
            } else if ($exprType == self::STEP_VAL) {
                $stepVal = intval(explode('/', $subExpr)[1]);

                if ($stepVal >= 0 && $stepVal < 24) {
                    $hoursAttrs['every-x-hours'][] = $stepVal;
                } else {
                    $isValidExpr = false;
                    break;
                }
            } else if ($exprType == self::SPECIFIC_VAL) {
                if ($this->_isNumber($subExpr)) {
                    $value = intval($subExpr);
                } else {
                    $isValidExpr = false;
                    break;
                }

                if ($value >= 0 && $value <= 23) {
                    $hoursAttrs['at-every-x-hour'][] = $value;
                } else {
                    $isValidExpr = false;
                    break;
                }
            }
        }

        if ($isValidExpr !== true) {
            $hoursAttrs = false;
        }

        return $hoursAttrs;
    }

    /**
     * 
     * @param type $minutesField
     * @return boolean|array
     * @since 1.0
     */
    private function _checkMinutes($minutesField) {
        $isValidExpr = true;
        $split = explode(',', $minutesField);
        $minuteAttrs = [
            'every-minute' => false,
            'every-x-minutes' => [],
            'at-every-x-minute' => [],
            'at-range' => []
        ];

        foreach ($split as $subExpr) {
            $exprType = $this->_getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $minuteAttrs['every-minute'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);

                if ($start < $end) {
                    if ($start >= 0 && $start <= 59) {
                        if ($end >= 0 && $end <= 59) {
                            $minuteAttrs['at-range'][] = [$start,$end];
                        } else {
                            $isValidExpr = false;
                            break;
                        }
                    } else {
                        $isValidExpr = false;
                        break;
                    }
                } else {
                    $isValidExpr = false;
                    break;
                }
            } else if ($exprType == self::STEP_VAL) {
                $stepVal = intval(explode('/', $subExpr)[1]);

                if ($stepVal >= 0 && $stepVal <= 59) {
                    $minuteAttrs['every-x-minutes'][] = $stepVal;
                } else {
                    $isValidExpr = false;
                    break;
                }
            } else if ($exprType == self::SPECIFIC_VAL) {
                if ($this->_isNumber($subExpr)) {
                    $value = intval($subExpr);
                } else {
                    $isValidExpr = false;
                    break;
                }

                if ($value >= 0 && $value <= 59) {
                    $minuteAttrs['at-every-x-minute'][] = $value;
                } else {
                    $isValidExpr = false;
                    break;
                }
            }
        }

        if ($isValidExpr !== true) {
            $minuteAttrs = false;
        }

        return $minuteAttrs;
    }
    /**
     * 
     * @param type $monthField
     * @return boolean
     * @since 1.0
     */
    private function _checkMonth($monthField) {
        $isValidExpr = true;
        $split = explode(',', $monthField);
        $monthAttrs = [
            'every-month' => false,
            'at-x-month' => [],
            'at-range' => []
        ];

        foreach ($split as $subExpr) {
            $exprType = $this->_getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $monthAttrs['every-month'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = in_array(strtoupper($range[0]), array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[strtoupper($range[0])] : intval($range[0]);
                $end = in_array(strtoupper($range[1]), array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[strtoupper($range[1])] : intval($range[1]);

                if ($start < $end) {
                    if ($start >= 1 && $start < 13) {
                        if ($end >= 1 && $end < 13) {
                            $monthAttrs['at-range'][] = [$start,$end];
                        } else {
                            $isValidExpr = false;
                            break;
                        }
                    } else {
                        $isValidExpr = false;
                        break;
                    }
                } else {
                    $isValidExpr = false;
                    break;
                }
            } else if ($exprType == self::STEP_VAL) {
                $isValidExpr = false;
            } else if ($exprType == self::SPECIFIC_VAL) {
                $subExpr = strtoupper($subExpr);
                $value = in_array($subExpr, array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[$subExpr] : intval($subExpr);

                if ($value >= 1 && $value <= 12) {
                    $monthAttrs['at-x-month'][] = $value;
                } else {
                    $isValidExpr = false;
                    break;
                }
            }
        }

        if ($isValidExpr !== true) {
            $monthAttrs = false;
        }

        return $monthAttrs;
    }
    /**
     * 
     * @param type $dayOfMonthField
     * @return boolean
     * @since 1.0
     */
    private function _dayOfMonth($dayOfMonthField) {
        $isValidExpr = true;
        $split = explode(',', $dayOfMonthField);
        $monthDaysAttrs = [
            'every-day' => false,
            'at-every-x-day' => [],
            'at-range' => []
        ];

        foreach ($split as $subExpr) {
            $exprType = $this->_getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $monthDaysAttrs['every-day'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);

                if ($start < $end) {
                    if ($start >= 1 && $start < 32) {
                        if ($end >= 1 && $end < 32) {
                            $monthDaysAttrs['at-range'][] = [$start,$end];
                        } else {
                            $isValidExpr = false;
                            break;
                        }
                    } else {
                        $isValidExpr = false;
                        break;
                    }
                } else {
                    $isValidExpr = false;
                    break;
                }
            } else if ($exprType == self::STEP_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::SPECIFIC_VAL) {
                $value = intval($subExpr);

                if ($value >= 1 && $value <= 31) {
                    $monthDaysAttrs['at-every-x-day'][] = $value;
                } else {
                    $isValidExpr = false;
                    break;
                }
            }
        }

        if ($isValidExpr !== true) {
            $monthDaysAttrs = false;
        }

        return $monthDaysAttrs;
    }
    /**
     * 
     * @param type $expr
     * @return string
     * @since 1.0
     */
    private function _getSubExprType($expr) {
        $retVal = self::ANY_VAL;

        if ($expr != '*') {
            $split = explode('/', $expr);
            $count = count($split);

            if ($count == 2) {
                if (strlen($split[0]) != 0 && strlen($split[1]) != 0) {
                    $retVal = self::STEP_VAL;
                } else {
                    $retVal = self::INV_VAL;
                }
            } else {
                $split = explode('-', $expr);
                $count = count($split);

                if ($count == 2) {
                    if (strlen($split[0]) != 0 && strlen($split[1]) != 0) {
                        $retVal = self::RANGE_VAL;
                    } else {
                        $retVal = self::INV_VAL;
                    }
                } else {
                    //it can be invalid value
                    if (strlen($expr) != 0) {
                        $retVal = self::SPECIFIC_VAL;
                    } else {
                        $retVal = self::INV_VAL;
                    }
                }
            }
        }

        return $retVal;
    }
    /**
     * Checks if a given string represents a number or not.
     * @param string $str
     * @return boolean
     */
    private function _isNumber($str) {
        $len = strlen($str);

        if ($len != 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $ch = $str[$x];

                if (!($ch >= '0' && $ch <= '9')) {
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * 
     * @param \Exception $ex
     */
    private function _logExeException($ex, $meth = '') {
        Cron::log('WARNING: An exception was thrown while performing the operation '.get_class($this).'::'.$meth.'. '
                .'The output of the job might be not as expected.');
        Cron::log('Exception class: "'.get_class($ex).'"');
        Cron::log('Exception message: "'.$ex->getMessage().'"');
        Cron::log('Thrown in file: "'.$ex->getFile().'"');
        Cron::log('Line: "'.$ex->getLine().'"');

        if ($meth == 'execute') {
            $this->isSuccess = false;
        }
    }
    /**
     * 
     * @param type $val
     * @return boolean
     */
    private function _validateAttrName($val) {
        $len = strlen($val);

        if ($len > 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $val[$x];

                if ($char == ' ' || $char == '=' || $char == '&' || $char == '#' || $char == '?') {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
    private function _weeklyOn($day,$time) {
        $timeSplit = explode(':', $time);

        if (count($timeSplit) == 2) {
            $hour = intval($timeSplit[0]);
            $minute = intval($timeSplit[1]);

            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return $this->cron($minute.' '.$hour.' * * '.$day);
            }
        }

        return false;
    }
    private function isHourHelper($hoursArr, $current) {
        $hours = $hoursArr['at-every-x-hour'];
        $retVal = in_array($current, $hours);

        if ($retVal === false) {
            $hours = $hoursArr['every-x-hours'];

            foreach ($hours as $hour) {
                if ($current % $hour == 0) {
                    $retVal = true;
                    break;
                }
            }
        }

        return $retVal;
    }
    private function isMinuteHelper($minuteArr, $current) {
        $minutes = $minuteArr['at-every-x-minute'];
        $retVal = in_array($current, $minutes);

        if ($retVal === false) {
            $minutes = $minuteArr['every-x-minutes'];

            foreach ($minutes as $min) {
                if ($current % $min == 0) {
                    $retVal = true;
                    break;
                }
            }
        }

        return $retVal;
    }
    private function onMonthHelper($monthNameOrNum, $minute, $hour, $dayNum) {
        $trimmed = trim($monthNameOrNum);

        if (in_array($trimmed, ['12','1','2','3','4','5','6','7','8','9','10','11'])) {
            $monthNameOrNum = intval($trimmed);
        } else {
            return false;
        }

        if ($monthNameOrNum >= 1 && $monthNameOrNum <= 12) {
            return $this->cron($minute.' '.$hour.' '.$dayNum.' '.$monthNameOrNum.' *');
        }

        return false;
    }
    /**
     * Sets the value of the property which is used to check if the job is 
     * forced to execute or not.
     * 
     * @param boolean $bool True or false.
     * 
     * @since 1.0
     */
    private function setIsForced($bool) {
        $this->isForced = $bool === true;
    }
}
