<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\scheduler;

use Exception;
use InvalidArgumentException;
use Throwable;
use WebFiori\Collections\Queue;
use webfiori\framework\cli\commands\SchedulerCommand;
use webfiori\framework\exceptions\InvalidCRONExprException;
use webfiori\framework\Util;
use WebFiori\Http\Request;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * An abstract class that contains basic functionality for implementing background
 * tasks.
 *
 * This class uses an implementation which is similar to cron for scheduling tasks.
 *
 * @author Ibrahim
 *
 * @version 1.0.3
 */
abstract class AbstractTask implements JsonI {
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
     * The command which is used to execute the task.
     *
     * @var SchedulerCommand
     *
     * @since 1.0.1
     */
    private $command;
    /**
     * The full cron expression.
     *
     * @var string
     *
     * @since 1.0
     */
    private $cronExpr;
    /**
     * An array that contains custom attributes which can be provided on
     * task execution.
     *
     * @var array
     *
     * @since 1.0
     */
    private $customAttrs;
    /**
     * A boolean which is set to true if the task is forced to execute.
     *
     * @var bool
     *
     * @since 1.0
     */
    private $isForced;
    /**
     * A boolean which is set to true if the task was
     * successfully executed.
     *
     * @var bool
     *
     * @since 1.0
     */
    private $isSuccess;
    /**
     * A string that describes what does the task do.
     *
     * @var string
     */
    private $taskDesc;
    /**
     * An array which contains all task details after parsing cron expression.
     *
     * @var array
     *
     * @since 1.0
     */
    private $taskDetails;
    /**
     * A name for the task.
     *
     * @var string
     *
     * @since 1.0
     */
    private $taskName;
    /**
     * Creates new instance of the class.
     *
     * @param string $taskName The name of the task.
     *
     * @param string $when A cron expression. An exception will be thrown if
     * the given expression is invalid.
     * The parts of the expression are as follows:
     * <ul>
     * <li>First part is minutes (0-59)</li>
     * <li>Second part is hours (0-23)</li>
     * <li>Third part is day of the month (1-31)</li>
     * <li>Fourth part is month (1-12)</li>
     * <li>Last part is day of the week (0-6)</li>
     * </ul>
     * Default is '* * * * *' which means run the task every minute.
     *
     * @param string $description A description for the task. Shown in scheduler
     * web interface or CLI.
     *
     * @throws Exception
     *
     * @since 1.0
     */
    public function __construct(string $taskName = '', string $when = '* * * * *', string $description = 'NO DESCRIPTION') {
        $this->taskDesc = '';
        $this->taskName = '';
        $this->setTaskName($taskName);
        $this->setDescription($description);
        $this->customAttrs = [];
        $this->isSuccess = false;

        $this->taskDetails = [];
        $this->taskDetails['minutes'] = [];
        $this->taskDetails['hours'] = [];
        $this->taskDetails['days-of-month'] = [];
        $this->taskDetails['months'] = [];
        $this->taskDetails['days-of-week'] = [];



        if (!$this->cron($when)) {
            throw new InvalidCRONExprException('Invalid cron expression: \''.$when.'\'.');
        }
        $this->setIsForced(false);
    }
    /**
     * Adds new execution argument.
     *
     * An execution argument is an argument that can be supplied to the
     * task in case of force execute. They will appear in tasks management control panel.
     * They also can be provided to the task when executing it
     * throw CLI as 'arg-name="argVal".
     * The argument name must follow the following rules:
     * <ul>
     * <li>Must be non-empty string.</li>
     * <li>Must not contain '#', '?', '&', '=' or space.</li>
     * </ul>
     *
     * @param string|TaskArgument $nameOrObj The name of the argument. This also can be an
     * object of type TaskArgument.
     *
     * @throws InvalidArgumentException If provided argument is not a string or an object of type
     * 'TaskArgument'.
     *
     * @since 1.0
     */
    public function addExecutionArg($nameOrObj) {
        if (gettype($nameOrObj) == 'string') {
            $arg = new TaskArgument($nameOrObj);
        } else if ($nameOrObj instanceof TaskArgument) {
            $arg = $nameOrObj;
        } else {
            throw new InvalidArgumentException('Invalid argument type. Expected \'string\' or \''.TaskArgument::class.'\'');;
        }

        if (!$this->hasArg($arg->getName())) {
            $this->customAttrs[] = $arg;
        }
    }
    /**
     * Adds multiple execution arguments at one shot.
     *
     * @param array $argsArr An array that contains the names of the
     * arguments. This also can be an associative array. The indices
     * are arguments names and the values are argument options.
     *
     * @since 1.0
     */
    public function addExecutionArgs(array $argsArr) {
        foreach ($argsArr as $argName => $argParamsOrName) {
            if (gettype($argName) != 'integer') {
                $argObj = new TaskArgument($argName);

                if (isset($argParamsOrName['description'])) {
                    $argObj->setDescription($argParamsOrName['description']);
                }

                if (isset($argParamsOrName['default'])) {
                    $argObj->setDefault($argParamsOrName['default']);
                }
                $this->addExecutionArg($argObj);
                continue;
            }
            $this->addExecutionArg($argParamsOrName);
        }
    }
    /**
     * Run some routines after the task is executed.
     *
     * The developer can implement this method to perform some actions after the
     * task is executed. Note that the method will get executed if the task is failed
     * or successfully completed. It is optional to implement that method. The developer can
     * leave the body of the method empty.
     *
     * @since 1.0
     */
    public abstract function afterExec();
    /**
     * Schedules a task using specific cron expression.
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
     * is '* * * * *' which means run the task every minute.
     *
     * @return bool If the given cron expression is valid, the method will
     * set the time of task as specified by the expression and return
     * true. If the expression is invalid, the method will return false.
     *
     * @since 1.0
     */
    public function cron(string $when = '* * * * *') : bool {
        $retVal = false;
        $trimmed = trim($when);
        $split = explode(' ', $trimmed);
        $count = count($split);

        if ($count == 5) {
            $minutesValidity = $this->checkMinutesHelper($split[0]);
            $hoursValidity = $this->checkHoursHelper($split[1]);
            $daysOfMonthValidity = $this->dayOfMonthHelper($split[2]);
            $monthValidity = $this->checkMonthHelper($split[3]);
            $daysOfWeekValidity = $this->checkDayOfWeekHelper($split[4]);

            if (!($minutesValidity === false ||
               $hoursValidity === false ||
               $daysOfMonthValidity === false ||
               $monthValidity === false ||
               $daysOfWeekValidity === false)) {
                $this->taskDetails = [
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
     * Schedules a task to run daily at specific hour and minute.
     *
     * The task will be executed every day at the given hour and minute. The
     * function uses 24 hours mode. If no parameters are given,
     * The default time is 00:00 which means that the task will be executed
     * daily at midnight.
     *
     * @param int $hour A number between 0 and 23 inclusive. 0 Means daily at
     * 12:00 AM and 23 means at 11:00 PM. Default is 0.
     * @param int $minute A number between 0 and 59 inclusive. Represents the
     * minute part of an hour. Default is 0.
     *
     * @return bool If task time is set, the method will return true. If
     * not set, the method will return false. It will not set only if the
     * given time is not correct.
     *
     * @since 1.0
     */
    public function dailyAt(int $hour = 0, int $minute = 0) : bool {
        if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
            return $this->cron($minute.' '.$hour.' * * *');
        }

        return false;
    }
    /**
     * Schedules a task to run every hour.
     *
     * The task will run at the start of the hour.
     *
     * @since 1.0.2
     */
    public function everyHour() {
        $this->cron('0 * * * *');
    }
    /**
     * Schedules a task to run every month on specific day and time.
     *
     * @param int $dayNum The number of the day. It can be any value between
     * 1 and 31 inclusive.
     *
     * @param string $time A day time string in the form 'hh:mm' in 24 hours mode.
     *
     * @return bool If the time for the task is set, the method will
     * return true. If not, it will return false.
     *
     * @since 1.0.1
     */
    public function everyMonthOn(int $dayNum = 1, string $time = '00:00') : bool {
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
     * Schedules a task to run weekly at specific week day and time.
     *
     * @param int $dayNameOrNum A 3 letter day name (such as 'sun' or 'tue') or a day number from 0 to 6.
     * 0 for sunday. Default is 0.
     *
     * @param string $time A time in the form 'HH:MM'. HH can have any value
     * between 0 and 23 inclusive. MM can have any value between 0 and 59 inclusive.
     * default is '00:00'.
     *
     * @return bool If the time for the task is set, the method will
     * return true. If not, it will return false.
     */
    public function everyWeek($dayNameOrNum = 0, string $time = '00:00') : bool {
        return $this->weeklyOn($dayNameOrNum, $time);
    }
    /**
     * Schedule a task to run every specific number of hours.
     *
     * The expression that will be used is "At minute 0 past every X hour" where
     * x is the number of hours.
     *
     * @param int $xHour The number of hours at which the job will be executed.
     */
    public function everyXHour(int $xHour) {
        $this->cron('0 */'.$xHour.' * * *');
    }
    /**
     * Schedule a task to run every specific number of minutes.
     *
     * Assuming that 5 is supplied as a value, this means that the job will be
     * executed every 5 minutes within an hour.
     *
     * @param int $step The number of minutes that a job will be executed after.
     */
    public function everyXMinuts(int $step) {
        $this->cron('*/'.$step.' * * * *');
    }
    /**
     * Execute the event which should run when it is time to execute the task.
     *
     * This method will be called automatically when task URL is accessed. The
     * method will check if it is time to execute the associated event or
     * not. If it is the time, The event will be executed. If
     * the task is forced to execute, the event that is associated with the
     * task will be executed even if it is not the time to execute the task.
     *
     * @param bool $force If set to true, the task will be forced to execute
     * even if it is not task time. Default is false.
     *
     * @return bool If the event that is associated with the task is executed,
     * the method will return true (Even if the task did not finish successfully).
     * If it is not executed, the method will return false.
     *
     * @since 1.0
     */
    public function exec(bool $force = false): bool {
        $retVal = false;
        $this->setIsForced($force);

        if ($force || $this->isTime()) {
            //Called to set the values of task args
            $this->getArgsValues();
            $isSuccessRun = $this->callMethod('execute');
            $retVal = true;
            $this->isSuccess = $isSuccessRun === true || $isSuccessRun === null;

            if ($this->isSuccess()) {
                $this->callMethod('onSuccess');
                $this->callMethod('afterExec');

                return true;
            }
            $this->callMethod('onFail');
            $this->callMethod('afterExec');
        }

        return $retVal;
    }
    /**
     * Execute the task.
     *
     * The code that will be in the body of that method is the code that will be
     * executed if it is time to run the task or the task is forced to
     * execute. The developer must implement this method in a way it returns null or true
     * if the task is executed successfully. If the implementation of the method
     * throws an exception, the task will be considered as failed.
     *
     * @return bool|null If the task successfully completed, the method should
     * return null or true. If the task failed, the method should return false.
     *
     * @since 1.0
     */
    public abstract function execute();
    /**
     * Returns an associative array that contains the values of
     * custom execution parameters.
     *
     * @return array An associative array. The keys are parameters names and
     * the values are the values which are given as input. If a value
     * is not provided, it will be set to null.
     *
     * @since 1.0
     */
    public function getArgsValues() : array {
        $retVal = [];

        foreach ($this->customAttrs as $attrObj) {
            $retVal[$attrObj->getName()] = $this->getArgValue($attrObj->getName());
        }

        return $retVal;
    }
    /**
     * Returns task argument as an object given its name.
     *
     * @param string $argName The name of the argument.
     *
     * @return TaskArgument|null If an argument which has the given name was added
     * to the task, the method will return it as an object. Other than that, the
     * method will return null.
     *
     * @since 1.0.3
     */
    public function getArgument(string $argName) {
        foreach ($this->getArguments() as $taskArgObj) {
            if ($taskArgObj->getName() == $argName) {
                return $taskArgObj;
            }
        }
    }
    /**
     * Returns an array that holds execution arguments of the task.
     *
     * @return array An array that holds objects of type 'TaskArgument'.
     *
     * @since 1.0.2
     */
    public function getArguments() : array {
        return $this->customAttrs;
    }
    /**
     * Returns the value of a custom execution argument.
     *
     * The value of the argument can be supplied through the table that will
     * appear in tasks management control panel. If the execution is performed through
     * CLI, the value of the argument can be supplied to the task as arg-name="Arg Val".
     *
     * @param string $name the name of execution argument.
     *
     * @return string|null If the argument does exist on the task and its value
     * is provided, the method will return its value. If it is not provided, or
     * it does not exist on the task, the method will return null.
     *
     * @since 1.0
     */
    public function getArgValue(string $name) {
        $argObj = $this->getArgument($name);

        if ($argObj === null) {
            return null;
        }

        $val = $this->getArgValFromRequest($name);

        if ($val === null) {
            $val = $this->getArgValFromTerminal($name);
        }



        if ($val === null) {
            $val = $argObj->getValue();
        }

        if ($val === null) {
            $val = $argObj->getDefault();
        }

        if ($val !== null) {
            $argObj->setValue($val);
        }

        return $val;
    }

    /**
     * Returns the command that was used to execute the task.
     *
     * Note that the command will be null if not executed from CLI environment.
     *
     * @return SchedulerCommand|null
     *
     * @since 1.0.1
     */
    public function getCommand() {
        return $this->command;
    }
    /**
     * Returns task description.
     *
     * Task description is a string which is used to describe what does the task
     * do.
     *
     * @return string Task description. Default return value is 'NO DESCRIPTION'.
     *
     * @since 1.0.2
     */
    public function getDescription() : string {
        return $this->taskDesc;
    }
    /**
     * Returns an array that contains the names of added custom
     * execution attributes.
     *
     * @return array An indexed array that contains all added
     * custom execution attributes names.
     *
     * @since 1.0
     */
    public function getExecArgsNames() : array {
        return array_map(function(TaskArgument $obj)
        {
            return $obj->getName();
        }, $this->getArguments());
    }
    /**
     * Returns the cron expression which is associated with the task.
     *
     * @return string The cron expression which is associated with the task.
     *
     * @since 1.0
     */
    public function getExpression() : string {
        return $this->cronExpr;
    }
    /**
     * Returns an associative array which contains details about the timings
     * at which the task will be executed.
     *
     * @return array The array will have the following indices:
     * <ul>
     * <li><b>minutes</b>: Contains sub arrays which has info about the minutes
     * at which the task will be executed.</li>
     * <li><b>hours</b>: Contains sub arrays which has info about the hours
     * at which the task will be executed.</li>
     * <li><b>days-of-month</b>: Contains sub arrays which has info about the days of month
     * at which the task will be executed.</li>
     * <li><b>months</b>: Contains sub arrays which has info about the months
     * at which the task will be executed.</li>
     * <li><b>days-of-week</b>: Contains sub arrays which has info about the days of week
     * at which the task will be executed.</li>
     * </ul>
     *
     * @since 1.0
     */
    public function getTaskDetails() : array {
        return $this->taskDetails;
    }
    /**
     * Returns the name of the task.
     *
     * The name is used to make different tasks unique. Each task must
     * have its own name. Also, the name of the task is used to force task
     * execution. It can be supplied as a part of task URL.
     *
     * @return string The name of the task. If no name is set, the function will return
     * 'SCHEDULER-TASK'.
     *
     * @since 1.0
     */
    public function getTaskName() : string {
        return $this->taskName;
    }
    /**
     * Checks if an argument with specific name belongs to the task or not.
     *
     * @param string $name The name of the argument that will be checked.
     *
     * @return bool If an argument with the given name already exist, the
     * method will return true. False if not.
     *
     * @since 1.0.2
     */
    public function hasArg(string $name) : bool {
        $added = false;

        foreach ($this->getArguments() as $argObj) {
            $added = $added || $argObj->getName() == $name;
        }

        return $added;
    }
    /**
     * Checks if current day of month in time is a day at which the task must be
     * executed.
     *
     * @return bool The method will return true if the current day of month in
     * time is a day at which the task must be executed.
     *
     * @since 1.0
     */
    public function isDayOfMonth() : bool {
        $monthDaysArr = $this->taskDetails['days-of-month'];
        $retVal = true;

        if ($monthDaysArr['every-day'] !== true) {
            $retVal = false;
            $current = TasksManager::dayOfMonth();
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
     * Checks if current day of week in time is a day at which the task must be
     * executed.
     *
     * @return bool The method will return true if the current day of week in
     * time is a day at which the task must be executed.
     *
     * @since 1.0
     */
    public function isDayOfWeek() : bool {
        $daysArr = $this->taskDetails['days-of-week'];
        $retVal = true;

        if ($daysArr['every-day'] !== true) {
            $retVal = false;
            $current = TasksManager::dayOfWeek();
            $ranges = $daysArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $days = $daysArr['at-every-x-day'];
                $retVal = in_array($current, $days);
            }
        }

        return $retVal;
    }
    /**
     * Checks if the task is forced to execute or not.
     *
     * @return bool If the task was forced to execute, the method will return
     * true. Other than that, it will return false.
     *
     * @since 1.0
     */
    public function isForced() : bool {
        return $this->isForced;
    }
    /**
     * Checks if current hour in time is an hour at which the task must be
     * executed.
     *
     * @return bool The method will return true if the current hour in
     * time is an hour at which the task must be executed.
     *
     * @since 1.0
     */
    public function isHour() : bool {
        $hoursArr = $this->taskDetails['hours'];
        $retVal = true;

        if ($hoursArr['every-hour'] !== true) {
            $retVal = false;
            $current = TasksManager::getHour();
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
     * Checks if current minute in time is a minute at which the task must be
     * executed.
     *
     * @return bool The method will return true if the current minute in
     * time is a minute at which the task must be executed.
     *
     * @since 1.0
     */
    public function isMinute() : bool {
        $minuteArr = $this->taskDetails['minutes'];
        $retVal = true;

        if ($minuteArr['every-minute'] !== true) {
            $retVal = false;
            $current = TasksManager::getMinute();
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
     * Checks if current month in time is a month at which the task must be
     * executed.
     *
     * @return bool The method will return true if the current month in
     * time is a month at which the task must be executed.
     *
     * @since 1.0
     */
    public function isMonth() : bool {
        $monthsArr = $this->taskDetails['months'];
        $retVal = true;

        if ($monthsArr['every-month'] !== true) {
            $retVal = false;
            $current = TasksManager::getMonth();
            $ranges = $monthsArr['at-range'];

            foreach ($ranges as $range) {
                if ($current >= $range[0] && $current <= $range[1]) {
                    $retVal = true;
                    break;
                }
            }

            if ($retVal === false) {
                $months = $monthsArr['every-x-month'];
                $retVal = in_array($current, $months);
            }
        }

        return $retVal;
    }
    /**
     * Checks if task name is valid or not.
     *
     * This method is also used to validate names of task arguments.
     *
     * @param string $val The name of the task.
     *
     * @return bool If valid, the method will return true. False otherwise.
     */
    public static function isNameValid(string $val) : bool {
        $len = strlen($val);

        if ($len > 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $val[$x];

                if ($char == '=' || $char == '&' || $char == '#' || $char == '?') {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Returns true if the task was executed successfully.
     *
     * The value returned by this method will depend on the return value
     * of the value which is returned by the method AbstractTask::execute().
     * If the method returned null or true, then it means the task
     * was successfully executed. If it returns false, this means the task did
     * not execute successfully. If it throws an exception, then the task is
     * not successfully completed.
     *
     * @return bool True if the task was executed successfully. False
     * if not.
     *
     * @since 1.0
     */
    public function isSuccess() : bool {
        return $this->isSuccess;
    }
    /**
     * Checks if it's time to execute the task or not.
     *
     * @return bool If it's time to execute the task, the method will return true.
     * If not, it will return false.
     *
     * @since 1.0
     */
    public function isTime() : bool {
        return $this->isMinute() && $this->isHour() && $this->isDayOfMonth() && $this->isMonth() && $this->isDayOfWeek();
    }
    /**
     * Run some routines if the task is executed and failed to completed successfully.
     *
     * The status of failure or success depends on the implementation of the method
     * AbstractTask::execute().
     * The developer can implement this method to take actions after the
     * task is executed and failed to complete.
     * It is optional to implement that method. The developer can
     * leave the body of the method empty.
     *
     * @since 1.0
     */
    public abstract function onFail();
    /**
     * Schedules a task to run at specific day and time in a specific month.
     *
     * @param int|string $monthNameOrNum Month number from 1 to 12 inclusive
     * or 3 letters month name. Default is 'jan'.
     *
     * @param int $dayNum The number of day in the month starting from 1 up to
     * 31 inclusive. Default is 1.
     *
     * @param string $time A time in the form 'HH:MM'. hh can have any value
     * between 0 and 23 inclusive. MM can have any value between 0 and 59 inclusive.
     * default is '00:00'.
     *
     * @return bool If the time for the task is set, the method will
     * return true. If not, it will return false.
     *
     * @since 1.0
     */
    public function onMonth($monthNameOrNum = 'jan', int $dayNum = 1, string $time = '00:00') : bool {
        if ($dayNum >= 1 && $dayNum <= 31) {
            $timeSplit = explode(':', $time);

            if (count($timeSplit) == 2) {
                $hour = intval($timeSplit[0]);
                $minute = intval($timeSplit[1]);

                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                    $uMonth = strtoupper($monthNameOrNum);

                    if (!in_array($uMonth, array_keys(self::MONTHS_NAMES))) {
                        return $this->onMonthHelper($monthNameOrNum, $minute, $hour, $dayNum);
                    }
                    $monthNum = self::MONTHS_NAMES[$uMonth];

                    return $this->cron($minute.' '.$hour.' '.$dayNum.' '.$monthNum.' *');
                }
            }
        }

        return false;
    }
    /**
     * Run some routines if the task is executed and completed successfully.
     *
     * The status of failure or success depends on the implementation of the method
     * AbstractTask::execute().
     * The developer can implement this method to perform actions after the
     * task is executed and failed to complete.
     * It is optional to implement that method. The developer can
     * leave the body of the method empty.
     *
     * @since 1.0
     */
    public abstract function onSuccess();
    /**
     * Associate the task with the command that was used to execute the task.
     *
     * @param SchedulerCommand $command
     *
     * @since 1.0.1
     */
    public function setCommand(SchedulerCommand $command) {
        $this->command = $command;
    }
    /**
     * Sets task description.
     *
     * Task description is a string which is used to describe what does the task do.
     *
     * @param string $desc Task description.
     *
     * @return bool If the description is set, the method will return true. Other than
     * that, the method will return false.
     *
     * @since 1.0.2
     */
    public function setDescription(string $desc) : bool {
        $trimmed = trim($desc);

        if (strlen($trimmed) > 0) {
            $this->taskDesc = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Sets an optional name for the task.
     *
     * The name is used to make different tasks unique. Each task must
     * have its own name. Also, the name of the task is used to force task
     * execution. It can be supplied as a part of task URL.
     *
     * Note that task name will be considered invalid
     * if it contains one of the following characters: '=', '&', '#' and '?'.
     *
     * @param string $name The name of the task.
     *
     * @return bool If task name is set, the method will return true. If not,
     * the method will return false. The method will not set the name only if
     * given value is empty string or the given name was used by a task which
     * was already scheduled.
     *
     * @since 1.0
     */
    public function setTaskName(string $name) : bool {
        if (!self::isNameValid($name)) {
            return false;
        }

        $trimmed = trim($name);

        if (strlen($trimmed) > 0) {
            $this->taskName = $trimmed;
            return true;
        }

        return false;
    }
    public function toJSON() : Json {
        $json = new Json([
            'name' => $this->getTaskName(),
            'expression' => $this->getExpression(),
            'args' => $this->getArguments(),
            'description' => $this->getDescription(),
            'is-time' => $this->isTime(),
            'time' => new Json([
                'is-minute' => $this->isMinute(),
                'is-day-of-week' => $this->isDayOfWeek(),
                'is-month' => $this->isMonth(),
                'is-hour' => $this->isHour(),
                'is-day-of-month' => $this->isDayOfMonth()
            ])
        ]);
        $json->setPropsStyle('snake');

        return $json;
    }
    /**
     * Schedules a task to run weekly at specific week day and time.
     *
     * @param int $dayNameOrNum A 3 letter day name (such as 'sun' or 'tue') or a day number from 0 to 6.
     * 0 for sunday. Default is 0.
     *
     * @param string $time A time in the form 'HH:MM'. HH can have any value
     * between 0 and 23 inclusive. MM can have any value between 0 and 59 inclusive.
     * default is '00:00'.
     *
     * @return bool If the time for the task is set, the method will
     * return true. If not, it will return false.
     *
     * @since 1.0
     */
    public function weeklyOn($dayNameOrNum = 0, string $time = '00:00'): bool {
        $uDayName = strtoupper($dayNameOrNum);

        if (!in_array($uDayName, array_keys(self::WEEK_DAYS))) {
            if (gettype($dayNameOrNum) == 'string') {
                $trimmed = trim($dayNameOrNum);

                if (!in_array($trimmed, ['0','1','2','3','4','5','6'])) {
                    return false;
                }
                $dayNameOrNum = intval($trimmed);
            }

            if ($dayNameOrNum >= 0 && $dayNameOrNum <= 6) {
                return $this->weeklyOnHelper($dayNameOrNum, $time);
            }

            return false;
        }

        return $this->weeklyOnHelper(self::WEEK_DAYS[$uDayName], $time);
    }
    /**
     * Calls one of the abstract methods of the class.
     *
     * This method is only used by the method AbstractTask::exec().
     *
     * @param string $fName The name of the method.
     *
     * @return null|boolean
     */
    private function callMethod(string $fName) {
        TasksManager::log('Calling the method '.get_class($this)."::$fName()");
        try {
            return $this->$fName();
        } catch (Throwable $ex) {
            $this->logExeException($ex, $fName);

            return false;
        }
    }
    /**
     *
     * @param string $dayOfWeekField
     * @return bool
     * @since 1.0
     */
    private function checkDayOfWeekHelper($dayOfWeekField) {
        $isValidExpr = true;
        $split = explode(',', $dayOfWeekField);
        $dayAttrs = $this->createAttrs('day');

        foreach ($split as $subExpr) {
            $exprType = $this->getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $dayAttrs['every-day'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = in_array(strtoupper($range[0]), array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[strtoupper($range[0])] : intval($range[0]);
                $end = in_array(strtoupper($range[1]), array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[strtoupper($range[1])] : intval($range[1]);

                if (!$this->isValidRange($start, $end, 0, 6)) {
                    $isValidExpr = false;
                    break;
                }
                $dayAttrs['at-range'][] = [$start,$end];
            } else if ($exprType == self::STEP_VAL) {
                $isValidExpr = false;
            } else if ($exprType == self::SPECIFIC_VAL) {
                $subExpr = strtoupper($subExpr);
                $value = in_array($subExpr, array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[$subExpr] : intval($subExpr);

                if ($value < 0 || $value > 6) {
                    $isValidExpr = false;
                    break;
                }
                $dayAttrs['at-every-x-day'][] = $value;
            }
        }

        if ($isValidExpr !== true) {
            $dayAttrs = false;
        }

        return $dayAttrs;
    }
    /**
     *
     * @param string $hoursField
     * @return bool
     * @since 1.0
     */
    private function checkHoursHelper(string $hoursField) {
        $isValidExpr = true;
        $split = explode(',', $hoursField);
        $hoursAttrs = $this->createAttrs('hour');

        foreach ($split as $subExpr) {
            $exprType = $this->getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $hoursAttrs['every-hour'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);

                if (!$this->isValidRange($start, $end, 0, 23)) {
                    $isValidExpr = false;
                    break;
                }
                $hoursAttrs['at-range'][] = [$start,$end];
            } else if ($exprType == self::STEP_VAL) {
                $stepVal = intval(explode('/', $subExpr)[1]);

                if (!($stepVal >= 0 && $stepVal < 24)) {
                    $isValidExpr = false;
                    break;
                }
                $hoursAttrs['every-x-hour'][] = $stepVal;
            } else if ($exprType == self::SPECIFIC_VAL) {
                if (!is_numeric($subExpr)) {
                    $isValidExpr = false;
                    break;
                }
                $value = intval($subExpr);

                if (!($value >= 0 && $value <= 23)) {
                    $isValidExpr = false;
                    break;
                }
                $hoursAttrs['at-every-x-hour'][] = $value;
            }
        }

        if ($isValidExpr !== true) {
            $hoursAttrs = false;
        }

        return $hoursAttrs;
    }
    /**
     *
     * @param string $minutesField
     * @return bool|array
     * @since 1.0
     */
    private function checkMinutesHelper(string $minutesField) {
        $isValidExpr = true;
        $split = explode(',', $minutesField);
        $minuteAttrs = $this->createAttrs('minute');

        foreach ($split as $subExpr) {
            $exprType = $this->getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $minuteAttrs['every-minute'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                if (!(is_numeric($range[0]) && is_numeric($range[1]))) {
                    $isValidExpr = false;
                    break;
                }
                $start = intval($range[0]);
                $end = intval($range[1]);

                if (!$this->isValidRange($start, $end, 0, 59)) {
                    $isValidExpr = false;
                    break;
                }
                $minuteAttrs['at-range'][] = [$start,$end];
            } else if ($exprType == self::STEP_VAL) {
                $stepVal = intval(explode('/', $subExpr)[1]);

                if (!($stepVal >= 0 && $stepVal <= 59)) {
                    $isValidExpr = false;
                    break;
                }
                $minuteAttrs['every-x-minute'][] = $stepVal;
            } else if ($exprType == self::SPECIFIC_VAL) {
                if (!is_numeric($subExpr)) {
                    $isValidExpr = false;
                    break;
                }
                $value = intval($subExpr);

                if (!($value >= 0 && $value <= 59)) {
                    $isValidExpr = false;
                    break;
                }
                $minuteAttrs['at-every-x-minute'][] = $value;
            }
        }

        if ($isValidExpr !== true) {
            $minuteAttrs = false;
        }

        return $minuteAttrs;
    }
    /**
     *
     * @param string $monthField
     * @return bool
     * @since 1.0
     */
    private function checkMonthHelper(string $monthField) {
        $isValidExpr = true;
        $split = explode(',', $monthField);
        $monthAttrs = $this->createAttrs('month');

        foreach ($split as $subExpr) {
            $exprType = $this->getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $monthAttrs['every-month'] = true;
            } else if ($exprType == self::INV_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = in_array(strtoupper($range[0]), array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[strtoupper($range[0])] : intval($range[0]);
                $end = in_array(strtoupper($range[1]), array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[strtoupper($range[1])] : intval($range[1]);

                if (!$this->isValidRange($start, $end, 1, 12)) {
                    $isValidExpr = false;
                    break;
                }
                $monthAttrs['at-range'][] = [$start,$end];
            } else if ($exprType == self::STEP_VAL) {
                $isValidExpr = false;
            } else if ($exprType == self::SPECIFIC_VAL) {
                $subExpr = strtoupper($subExpr);
                $value = in_array($subExpr, array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[$subExpr] : intval($subExpr);

                if (!($value >= 1 && $value <= 12)) {
                    $isValidExpr = false;
                    break;
                }
                $monthAttrs['every-x-month'][] = $value;
            }
        }

        if ($isValidExpr !== true) {
            $monthAttrs = false;
        }

        return $monthAttrs;
    }
    private function createAttrs($suffix): array {
        return [
            // *
            'every-'.$suffix => false,
            // Steps
            'every-x-'.$suffix => [],
            // Exact
            'at-every-x-'.$suffix => [],
            'at-range' => []
        ];
    }
    /**
     *
     * @param string $dayOfMonthField
     * @return bool
     * @since 1.0
     */
    private function dayOfMonthHelper(string $dayOfMonthField) {
        $isValidExpr = true;
        $split = explode(',', $dayOfMonthField);
        $monthDaysAttrs = $this->createAttrs('day');

        foreach ($split as $subExpr) {
            $exprType = $this->getSubExprType($subExpr);

            if ($exprType == self::ANY_VAL) {
                $monthDaysAttrs['every-day'] = true;
            } else if ($exprType == self::INV_VAL || $exprType == self::STEP_VAL) {
                $isValidExpr = false;
                break;
            } else if ($exprType == self::RANGE_VAL) {
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);

                if (!$this->isValidRange($start, $end, 1, 31)) {
                    $isValidExpr = false;
                    break;
                }
                $monthDaysAttrs['at-range'][] = [$start,$end];
            } else if ($exprType == self::SPECIFIC_VAL) {
                $value = intval($subExpr);

                if (!($value >= 1 && $value <= 31)) {
                    $isValidExpr = false;
                    break;
                }
                $monthDaysAttrs['at-every-x-day'][] = $value;
            }
        }

        if ($isValidExpr !== true) {
            $monthDaysAttrs = false;
        }

        return $monthDaysAttrs;
    }

    private function getArgValFromRequest($name) {
        $uName = str_replace(' ', '_', $name);
        $retVal = Request::getParam($name);

        if ($retVal === null) {
            $retVal = Request::getParam($uName);
        }

        return $retVal;
    }
    private function getArgValFromTerminal($name) {
        $c = $this->getCommand();

        if ($c === null) {
            return null;
        }

        return $c->getArgValue($name);
    }
    /**
     *
     * @param string $expr
     * @return string
     * @since 1.0
     */
    private function getSubExprType(string $expr): string {
        $retVal = self::ANY_VAL;

        if ($expr != '*') {
            $split0 = explode('/', $expr);
            $count = count($split0);

            if (!($count == 2)) {
                $split0 = explode('-', $expr);
                $count = count($split0);

                if (!($count == 2)) {
                    $retVal = self::SPECIFIC_VAL;
                    //it can be invalid value
                    if (!(strlen($expr) != 0)) {
                        $retVal = self::INV_VAL;
                    }

                    return $retVal;
                }
                $retVal = self::RANGE_VAL;

                if (!(strlen($split0[0]) != 0 && strlen($split0[1]) != 0)) {
                    $retVal = self::INV_VAL;
                }

                return $retVal;
            }
            //Step val
            if (!(strlen($split0[0]) != 0 && strlen($split0[1]) != 0)) {
                return self::INV_VAL;
            }

            return self::STEP_VAL;
        }

        return $retVal;
    }
    private function isHourHelper($hoursArr, $current) {
        $hours = $hoursArr['at-every-x-hour'];
        $retVal = in_array($current, $hours);

        if ($retVal === false) {
            $hours = $hoursArr['every-x-hour'];

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
            $minutes = $minuteArr['every-x-minute'];

            foreach ($minutes as $min) {
                if ($current % $min == 0) {
                    $retVal = true;
                    break;
                }
            }
        }

        return $retVal;
    }
    private function isValidRange(int $start, int $end, int $min, int $max): bool {
        $isValidExpr = true;

        if (!($start < $end)) {
            $isValidExpr = false;
        }

        if (!($start >= $min && $start <= $max)) {
            $isValidExpr = false;
        }

        if (!($end >= $min && $end <= $max)) {
            $isValidExpr = false;
        }

        return $isValidExpr;
    }

    /**
     *
     * @param Throwable $ex
     * @param string $meth
     */
    private function logExeException(Throwable $ex, string $meth = '') {
        TasksManager::log('WARNING: An exception was thrown while performing the operation '.get_class($this).'::'.$meth.'. '
                .'The output of the task might be not as expected.');
        TasksManager::log('Exception class: '.get_class($ex));
        TasksManager::log('Exception message: '.$ex->getMessage());
        TasksManager::log('Thrown in: '.Util::extractClassName($ex->getFile()));
        TasksManager::log('Line: '.$ex->getLine());
        TasksManager::log('Stack Trace:');
        $index = 0;
        $trace = debug_backtrace();
        

        foreach ($trace as $traceEntry) {
            $e =  ($traceEntry["class"] ?? "unknown") . " Line: " . ($traceEntry["line"] ?? "unknown");
            TasksManager::log('#'.$index.' At class '.$e);
            $index++;
        }

        if ($meth == 'execute') {
            $this->isSuccess = false;
        }
    }
    private function onMonthHelper($monthNameOrNum, $minute, $hour, $dayNum): bool {
        $trimmed = trim($monthNameOrNum);

        if (!in_array($trimmed, ['12','1','2','3','4','5','6','7','8','9','10','11'])) {
            return false;
        }
        $monthNameOrNum = intval($trimmed);

        if ($monthNameOrNum >= 1 && $monthNameOrNum <= 12) {
            return $this->cron($minute.' '.$hour.' '.$dayNum.' '.$monthNameOrNum.' *');
        }

        return false;
    }
    /**
     * Sets the value of the property which is used to check if the task is
     * forced to execute or not.
     *
     * @param bool $bool True or false.
     *
     * @since 1.0
     */
    private function setIsForced(bool $bool) {
        $this->isForced = $bool;
    }


    private function weeklyOnHelper($day,$time): bool {
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
}
