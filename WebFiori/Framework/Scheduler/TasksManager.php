<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2018 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Scheduler;

use const DS;
use Exception;
use WebFiori\Cli\Command;
use WebFiori\Cli\Runner;
use WebFiori\Collections\Queue;
use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\Commands\SchedulerCommand;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Scheduler\WebServices\TasksServicesManager;
use WebFiori\Framework\Scheduler\WebUI\ListTasksPage;
use WebFiori\Framework\Scheduler\WebUI\SetPasswordPage;
use WebFiori\Framework\Scheduler\WebUI\TasksLoginPage;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Util;
/**
 * A class that is used to manage scheduled background tasks.
 *
 * It is used to create tasks, schedule them and execute them. In order to run
 * the tasks automatically, the developer must add an entry in the following
 * format in crontab:
 * <p><code>* * * * *  /usr/bin/php path/to/WebFiori --scheduler check p=&lt;password&gt;</code></p>
 * Where &lt;password&gt; is the password
 * that was set by the developer to protect the tasks from unauthorized access.
 * If no password is set, then it can be removed from the command.
 * Note that the path to PHP executable might differ from "/usr/bin/php".
 * It depends on where the executable has been installed.
 *
 * @author Ibrahim
 *
 * @version 1.1.0
 */
class TasksManager {
    /**
     * The password that is used to access and execute tasks.
     *
     * @var string
     *
     * @since 1.0
     */
    private $accessPass;
    /**
     * The task which is currently executing.
     *
     * @var BaseTask|null
     *
     * @since 1.0.4
     */
    private $activeTask;
    /**
     *
     * @var Command
     */
    private $command;
    /**
     * A variable that is set to true if task execution log is enabled.
     *
     * @var bool
     *
     * @since 1.0.1
     */
    private $isLogEnabled;
    /**
     * An array that contains strings which acts as log messages.
     *
     * @var array
     *
     * @since 1.0.8
     */
    private $logsArray;
    /**
     * An instance of this class
     *
     * @var TasksManager
     *
     * @since 1.0
     */
    private static $tasksManager;
    /**
     *
     * @var array
     *
     * @since 1.0.9
     */
    private $tasksNamesArr;
    /**
     * A queue which contains all tasks.
     *
     * @var Queue
     *
     * @since 1.0
     */
    private $tasksQueue;
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
        $this->tasksNamesArr = [];
        $this->logsArray = [];
        $this->isLogEnabled = false;
        $this->tasksQueue = new Queue();
        $this->setPasswordHelper('');
    }
    /**
     * Returns an object that represents the task which is currently being executed.
     *
     * @return BaseTask|null If there is a task which is being executed, the
     * method will return as an object.
     * If no task is being executed, the method will return null.
     *
     * @since 1.0.4
     */
    public static function activeTask() {
        return self::get()->activeTask;
    }
    /**
     * Creates new task using cron expression.
     *
     * The task will be created and scheduled only if the given cron expression
     * is valid. For more information on cron expressions, go to
     * https://en.wikipedia.org/wiki/Cron#CRON_expression. Note that
     * the method does not support year field. This means
     * the expression will have only 5 fields.
     *
     * @param string $when A cron expression.
     *
     * @param string $taskName An optional task name.
     *
     * @param callable $function A function to run when it is the time to execute
     * the task.
     *
     * @param array $funcParams An array of parameters that can be passed to the
     * function.
     *
     * @return bool If the task was created and scheduled, the method will
     * return true. Other than that, the method will return false.
     *
     * @since 1.0
     */
    public static function createTask(string $when = '*/5 * * * *', string $taskName = '', ?callable $function = null, array $funcParams = []) : bool {
        try {
            $task = new BaseTask($when);

            if ($function !== null) {
                $task->setOnExecution($function, $funcParams);
            }

            if (strlen($taskName) > 0) {
                $task->setTaskName($taskName);
            }

            return self::scheduleTask($task);
        } catch (Exception $ex) {
            return false;
        }
    }
    /**
     * Creates a daily task to execute every day at specific hour and minute.
     *
     * @param string $time A time in the form 'HH:MM'. HH can have any value
     * between 0 and 23 inclusive. MM can have any value between 0 and 59 inclusive.
     *
     * @param string $name An optional name for the task. Can be null.
     *
     * @param callable $func A function that will be executed once it is the
     * time to run the task.
     *
     * @param array $funcParams An optional array of parameters which will be passed to
     * the callback that will be executed when it's time to execute the task.
     *
     * @return bool If the task was created and scheduled, the method will
     * return true. Other than that, the method will return false.
     *
     * @since 1.0
     */
    public static function dailyTask(string $time, string $name, callable $func, array $funcParams = []): bool {
        $split = explode(':', $time);

        if (count($split) == 2 && is_callable($func)) {
            $task = new BaseTask();
            $task->setTaskName($name);

            if ($task->dailyAt($split[0], $split[1])) {
                $task->setOnExecution($func, $funcParams);

                return self::scheduleTask($task);
            }
        }

        return false;
    }
    /**
     * Returns the number of current day in the current  month as integer.
     *
     * This method is used by the class 'AbstractTask' to validate task
     * execution time.
     *
     * @return int An integer that represents current day number in
     * the current month.
     *
     * @since 1.0.2
     */
    public static function dayOfMonth() : int {
        return self::get()->timestamp['month-day'];
    }
    /**
     * Returns the number of current day in the current  week as integer.
     *
     * This method is used by the class 'AbstractTask' to validate task
     * execution time. The method will always return a value between 0 and 6
     * inclusive. 0 Means Sunday and 6 is for Saturday.
     *
     * @return int An integer that represents current day number in
     * the week.
     *
     * @since 1.0.2
     */
    public static function dayOfWeek() : int {
        return self::get()->timestamp['week-day'];
    }
    /**
     * Enable or disable logging for tasks execution.
     *
     * This method is also used to check if logging is enabled or not. If
     * execution log is enabled, a log file with the name 'tasks.log' will be
     * created in the folder '/logs'.
     *
     * @param bool|null $bool If set to true, a log file that contains the details
     * of the executed tasks will be created in 'logs' folder. Default value
     * is null.
     *
     * @return bool If logging is enabled, the method will return true.
     *
     * @since 1.0.1
     */
    public static function execLog(?bool $bool = null) : bool {
        if ($bool !== null) {
            self::get()->setLogEnabledHelper($bool);
        }

        return self::get()->isLogEnabledHelper();
    }
    /**
     * Returns a singleton of the class.
     *
     * @return TasksManager
     *
     * @since 1.0
     */
    public static function get(): TasksManager {
        if (self::$tasksManager === null) {
            self::$tasksManager = new TasksManager();
        }

        return self::$tasksManager;
    }
    /**
     * Returns the number of current hour in the day as integer.
     *
     * This method is used by the class 'AbstractTask' to validate task
     * execution time. The method will always return a value between 0 and 23
     * inclusive.
     *
     * @return int An integer that represents current hour number in
     * the day.
     * @since 1.0.2
     */
    public static function getHour() : int {
        return self::get()->timestamp['hour'];
    }
    /**
     * Returns the array that contains logged messages.
     *
     * The array will contain the messages which where logged using the method
     * <code>TasksManager::log()</code>
     *
     * @return array An array of strings.
     *
     * @since 1.0.8
     */
    public static function getLogArray() : array {
        return self::get()->logsArray;
    }
    /**
     * Returns the number of current minute in the current hour as integer.
     *
     * This method is used by the class 'AbstractTask' to validate task
     * execution time. The method will always return a value between 0 and 59
     * inclusive.
     *
     * @return int An integer that represents current minute number in
     * the current hour.
     *
     * @since 1.0.2
     */
    public static function getMinute() : int {
        return self::get()->timestamp['minute'];
    }
    /**
     * Returns the number of current month as integer.
     *
     * This method is used by the class 'AbstracTask' to validate task
     * execution time. The method will always return a value between 1 and 12
     * inclusive.
     *
     * @return int An integer that represents current month's number.
     * @since 1.0.2
     */
    public static function getMonth() : int {
        return self::get()->timestamp['month'];
    }
    /**
     * Gets the password that is used to protect tasks execution.
     *
     * The password is used to prevent unauthorized access to execute tasks.
     * The provided password must be 'sha256' hashed string. It is recommended
     * to hash the password externally then use the hash inside your code.
     *
     * @return string If the password is set, the method will return it.
     * If not set, the method will return the string 'NO_PASSWORD'.
     *
     */
    public static function getPassword() : string {
        return self::get()->getPasswordHelper();
    }
    public static function getTasks() : array {
        return self::get()->tasksQueue()->toArray();
    }
    /**
     * Returns a task given its name.
     *
     * @param string $taskName The name of the task.
     *
     * @return BaseTask|null If a task which has the given name was found,
     * the method will return an object of type 'AbstractTask' that represents
     * the task. Other than that, the method will return null.
     *
     * @since 1.0.5
     */
    public static function getTask(string $taskName) {
        $trimmed = trim($taskName);
        $retVal = null;

        if (strlen($trimmed) != 0) {
            $tasks = self::tasksQueue()->toArray();

            foreach ($tasks as $task) {

                if ($task->getTaskName() == $trimmed) {
                    $retVal = $task;
                    break;
                }
            }
        }

        return $retVal;
    }
    /**
     * Returns an array that contains the names of scheduled tasks.
     *
     * @return array An array that contains the names of scheduled tasks.
     *
     * @since 1.0.9
     */
    public static function getTasksNames() : array {
        return self::get()->tasksNamesArr;
    }
    /**
     * Returns the time at which tasks check was initialized.
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
    public static function getTimestamp() : string {
        $month = self::getMonth();

        if ($month < 10) {
            $month = '0'.$month;
        }
        $day = self::dayOfMonth();

        if ($day < 10) {
            $day = '0'.$day;
        }
        $hour = self::getHour();

        if ($hour < 10) {
            $hour = '0'.$hour;
        }
        $minute = self::getMinute();

        if ($minute < 10) {
            $minute = '0'.$minute;
        }

        return $month.'-'.$day.' '.$hour.':'.$minute;
    }
    /**
     * Creates routes to tasks web interface pages.
     *
     * This method is used to initialize the following routes:
     * <ul>
     * <li>/scheduler</li>
     * <li>/scheduler/login</li>
     * <li>/scheduler/apis/{action}</li>
     * <li>/scheduler/tasks</li>
     * <li>/scheduler/tasks/{task-name}</li>
     * </ul>
     *
     * @since 1.1.0
     */
    public static function initRoutes() {
        Router::addRoute([
            'path' => '/scheduler',
            'route-to' => TasksLoginPage::class,
            'routes' => [
                [
                    'path' => '/tasks',
                    'route-to' => ListTasksPage::class
                ],
                [
                    'path' => '/apis/{action}',
                    'route-to' => TasksServicesManager::class,
                    'as-api' => true
                ],
                [
                    'path' => '/login',
                    'route-to' => TasksLoginPage::class
                ],
                [
                    'path' => '/set-password',
                    'route-to' => SetPasswordPage::class
                ]
            ]
        ]);
    }
    /**
     * Appends a message to the array that contains logged messages.
     *
     * The main aim of the log is to help developers identify the issues which
     * might cause a task to fail. This method can be called in any place to
     * log a message while the code is executing.
     *
     * @param string $message A string that act as a log message. It will be
     * appended as passed without any changes.
     *
     * @param string $type The type of the message that will be logged. It can
     * be one of the following values:
     * <ul>
     * <li>none</li>
     * <li>error</li>
     * <li>success</li>
     * <li>info</li>
     * </ul>
     * Default is 'none'.
     *
     * @since 1.0.8
     */
    public static function log(string $message, string $type = 'none') {
        self::get()->logsArray[] = $message;

        if (self::get()->command !== null && self::get()->command->isArgProvided('--show-log')) {
            if ($type == 'success') {
                self::get()->command->success($message);
            } else if ($type == 'error') {
                self::get()->command->error($message);
            } else if ($type == 'info') {
                self::get()->command->info($message);
            } else {
                self::get()->command->println("%s", $message);
            }
        }
    }
    /**
     * Appends a message to the array that contains logged messages.
     *
     * @param string $msg A string that act as a log message. It will be
     * appended as passed without any changes. Note that if running in CLI,
     * this will appear as a error message
     */
    public static function logErr(string $msg) {
        self::log($msg, 'error');
    }
    /**
     * Appends a message to the array that contains logged messages.
     *
     * @param string $msg A string that act as a log message. It will be
     * appended as passed without any changes. Note that if running in CLI,
     * this will appear as a info message
     */
    public static function logInfo(string $msg) {
        self::log($msg, 'info');
    }
    /**
     * Appends a message to the array that contains logged messages.
     *
     * @param string $msg A string that act as a log message. It will be
     * appended as passed without any changes. Note that if running in CLI,
     * this will appear as a success message
     */
    public static function logSuccess(string $msg) {
        self::log($msg, 'success');
    }
    /**
     * Create a task that will be executed once every month.
     *
     * @param int $dayNumber The day of the month at which the task will be
     * executed on. It can have any value between 1 and 31 inclusive.
     *
     * @param string $time A string that represents the time of the day that
     * the task will execute on. The format of the time must be 'HH:MM'. where
     * HH can have any value from '00' up to '23' and 'MM' can have any value
     * from '00' up to '59'.
     *
     * @param string $name The name of the task.
     *
     * @param callable $func A function that will be executed when it's time to
     * run the task.
     *
     * @param array $funcParams An optional array of parameters which will be
     * passed to task function.
     *
     * @return bool If the task was scheduled, the method will return true.
     * If not, the method will return false.
     *
     * @since 1.0.3
     */
    public static function monthlyTask(int $dayNumber, string $time, string $name, callable $func, array $funcParams = []): bool {
        if ($dayNumber > 0 && $dayNumber < 32) {
            $split = explode(':', $time);

            if (count($split) == 2 && is_callable($func)) {
                $task = new BaseTask();
                $task->setTaskName($name);

                if ($task->everyMonthOn($dayNumber, $time)) {
                    $task->setOnExecution($func, $funcParams);

                    return self::scheduleTask($task);
                }
            }
        }

        return false;
    }
    /**
     * Register any task which exist in the folder 'tasks' of the application.
     *
     * Note that this method will register tasks only if the framework is running
     * using CLI or the constant 'SCHEDULER_THROUGH_HTTP' is set to true.
     */
    public static function registerTasks() {
        if (Runner::isCLI() || (defined('SCHEDULER_THROUGH_HTTP') && SCHEDULER_THROUGH_HTTP === true)) {
            App::autoRegister('Tasks', function (AbstractTask $task)
            {
                TasksManager::scheduleTask($task);
            });
        }
    }
    /**
     * Reset all attributes of tasks manager to defaults.
     */
    public static function reset() {
        self::get()->timestamp = [
            'month' => intval(date('m')),
            'month-day' => intval(date('d')),
            'week-day' => intval(date('w')),
            'hour' => intval(date('H')),
            'minute' => intval(date('i'))
        ];
        self::get()->tasksNamesArr = [];
        self::get()->logsArray = [];
        self::get()->isLogEnabled = false;
        self::get()->tasksQueue = new Queue();
        self::get()->setPasswordHelper('');
    }
    /**
     * Check each scheduled task and run it if it's time to run it.
     *
     * @param string $pass If tasks scheduler password is set, this value must be
     * provided. The given value will be hashed inside the body of the
     * method and then compared with the password which was set. Default
     * is empty string.
     *
     * @param string|null $taskName An optional task name. If specified, only
     * the given task will be checked. Default is null.
     *
     * @param bool $force If this attribute is set to true and a task name
     * was provided, the task will be forced to execute. Default is false.
     *
     * @param SchedulerCommand|null $command If scheduler is executed from CLI, this parameter is
     * provided to set custom execution attributes of a task.
     *
     * @return string|array If scheduler password is set and the given one is
     * invalid, the method will return the string 'INV_PASS'. If
     * a task name is specified and no task was found which has the given
     * name, the method will return the string 'TASK_NOT_FOUND'. Other than that,
     * the method will return an associative array which has the
     * following indices:
     * <ul>
     * <li><b>total-tasks</b>: Total number of scheduled tasks.</li>
     * <li><b>executed-count</b>: Number of executed tasks.</li>
     * <li><b>successfully-completed</b>: Number of successfully
     * completed tasks.</li>
     * <li><b>failed</b>: Number of tasks which did not
     * finish successfully.</li>
     * </ul>
     *
     * @since 1.0.6
     */
    public static function run(string $pass = '', ?string $taskName = null, bool $force = false, ?SchedulerCommand $command = null) {
        self::get()->command = $command;
        self::log('Running task(s) check...');
        $activeSession = SessionsManager::getActiveSession();
        $isSessionLogged = $activeSession !== null ? $activeSession->get('scheduler-is-logged-in') : false;
        $schedulerPass = TasksManager::getPassword();

        if ($schedulerPass != 'NO_PASSWORD' && $isSessionLogged !== true && hash('sha256',$pass) != $schedulerPass) {
            self::log('Error: Given password is incorrect.');
            self::log('Check finished.');

            return 'INV_PASS';
        }
        $xForce = $force === true;
        $retVal = [
            'total-tasks' => TasksManager::tasksQueue()->size(),
            'executed-count' => 0,
            'successfully-completed' => [],
            'failed' => [],
        ];

        if ($taskName !== null) {
            self::log("Forcing task '$taskName' to execute...");
            $task = self::getTask($taskName);

            if ($task instanceof AbstractTask) {
                self::runTaskHelper($retVal, $task, $xForce, $command);
            } else {
                self::log("Error: No task which has the name '$taskName' is found.");
                self::log('Check finished.');

                return 'TASK_NOT_FOUND';
            }
        } else {
            $tempQ = new Queue();

            while ($task = TasksManager::tasksQueue()->dequeue()) {
                $tempQ->enqueue($task);
                self::runTaskHelper($retVal, $task, $xForce, $command);
            }

            while ($task = $tempQ->dequeue()) {
                self::get()->tasksQueue->enqueue($task);
            }
        }
        self::log('Check finished.');

        return $retVal;
    }

    /**
     * Adds new task to tasks queue.
     *
     * @param AbstractTask $task An instance of the class 'AbstractTask'.
     *
     * @return bool If the task is added, the method will return true.
     *
     * @since 1.0
     */
    public static function scheduleTask(AbstractTask $task) : bool {
        return self::get()->addTaskHelper($task);
    }
    /**
     * Sets the number of day in the month at which the scheduler started to
     * execute tasks.
     *
     * This method is helpful for the developer to test if tasks will run on
     * the specified time or not.
     *
     * @param int $dayOfMonth The number of day. 1 for the first day of month and
     * 31 for the last day of the month.
     *
     * @since 1.1.1
     */
    public static function setDayOfMonth(int $dayOfMonth) {
        if ($dayOfMonth >= 1 && $dayOfMonth <= 31) {
            self::get()->timestamp['month-day'] = $dayOfMonth;
        }
    }
    /**
     * Sets the value of the week at which the scheduler started to
     * run.
     *
     * This method is helpful for the developer to test if tasks will run on
     * the specified time or not.
     *
     * @param int $val Numeric representation of the day of the week.
     * 0 for Sunday through 6 for Saturday.
     *
     * @since 1.1.1
     */
    public static function setDayOfWeek(int $val) {
        if ($val >= 0 && $val <= 6) {
            self::get()->timestamp['week-day'] = $val;
        }
    }
    /**
     * Sets the hour at which the scheduler started to
     * execute tasks.
     *
     * This method is helpful for the developer to test if tasks will run on
     * the specified time or not.
     *
     * @param int $hour The number of hour. Can be any value between 1 and
     * 23 inclusive.
     *
     * @since 1.1.1
     */
    public static function setHour(int $hour) {
        if ($hour >= 0 && $hour <= 23) {
            self::get()->timestamp['hour'] = $hour;
        }
    }
    /**
     * Sets the minute at which the scheduler started to
     * execute tasks.
     *
     * This method is helpful for the developer to test if tasks will run on
     * the specified time or not.
     *
     * @param int $minute The number of the minute. Can be any value from
     * 1 to 59.
     *
     * @since 1.1.1
     */
    public static function setMinute(int $minute) {
        if ($minute >= 0 && $minute <= 59) {
            self::get()->timestamp['minute'] = $minute;
        }
    }
    /**
     * Sets the month at which the scheduler started to
     * execute tasks.
     *
     * This method is helpful for the developer to test if tasks will run on
     * the specified time or not.
     *
     * @param int $month The number of the month. Can be any value
     * between 1 and 12 inclusive.
     *
     * @since 1.1.1
     */
    public static function setMonth(int $month) {
        if ($month >= 1 && $month <= 12) {
            self::get()->timestamp['month'] = $month;
        }
    }
    /**
     * Sets the password that is used to protect tasks execution.
     *
     * The password is used to prevent unauthorized access to execute tasks.
     * The provided password will be 'sha256' hashed.
     *
     * @param string $pass The password that will be used.
     */
    public static function setPassword(string $pass) {
        self::get()->setPasswordHelper($pass);
    }
    /**
     * Returns a queue of all queued tasks.
     *
     * @return Queue An object of type 'Queue' which contains all queued tasks.
     *
     * @since 1.0
     */
    public static function tasksQueue() : Queue {
        return self::get()->getQueueHelper();
    }
    /**
     * Creates a task that will be executed on specific time weekly.
     *
     * @param string $time A string in the format 'D-HH:MM'. 'D' can be a number
     * between 0 and 6 inclusive or a 3 characters day name such as 'sun'. 0 is
     * for Sunday and 6 is for Saturday.
     * 'HH' can have any value between 0 and 23 inclusive. MM can have any value
     * between 0 and 59 inclusive.
     *
     * @param string $name An optional name for the task. Can be null.
     *
     * @param callable|null $func A function that will be executed once it is the
     * time to run the task.
     *
     * @param array $funcParams An optional array of parameters which will be passed to
     * the function.
     *
     * @return bool If the task was created and scheduled, the method will
     * return true. Other than that, the method will return false.
     *
     * @since 1.0
     */
    public static function weeklyTask(string $time, string $name, callable $func, array $funcParams = []): bool {
        $split1 = explode('-', $time);

        if (count($split1) == 2) {
            $task = new BaseTask();
            $task->setTaskName($name);

            if ($task->weeklyOn($split1[0], $split1[1])) {
                $task->setOnExecution($func, $funcParams);

                return self::scheduleTask($task);
            }
        }

        return false;
    }
    /**
     *
     * @param AbstractTask $task
     * @return bool
     * @since 1.0
     */
    private function addTaskHelper(AbstractTask $task): bool {
        $retVal = false;

        if ($task instanceof AbstractTask) {
            if ($task->getTaskName() == 'Background Task') {
                $task->setTaskName('task-'.$this->tasksQueue()->size());
            }

            if (!in_array($task->getTaskName(), $this->tasksNamesArr)) {
                $retVal = $this->tasksQueue->enqueue($task);
                if ($retVal) {
                    $this->tasksNamesArr[] = $task->getTaskName();
                }
            }
        }

        return $retVal;
    }
    /**
     *
     * @return string
     * @since 1.0
     */
    private function getPasswordHelper(): string {
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
    private function getQueueHelper(): Queue {
        return $this->tasksQueue;
    }
    private function isLogEnabledHelper(): bool {
        return $this->isLogEnabled;
    }
    private function logExecHelper($forced, $task, File $file) {
        if ($forced) {
            $file->setRawData('Task \''.$task->getTaskName().'\' was forced to executed at '.date(DATE_RFC1123).". Request source IP: ".Util::getClientIP()."\n");
        } else {
            $file->setRawData('Task \''.$task->getTaskName().'\' automatically executed at '.date(DATE_RFC1123)."\n");
        }

        if ($task->isSuccess()) {
            $file->setRawData('Execution status: Successfully completed.'."\n");
        } else {
            $file->setRawData('Execution status: Failed to completed.'."\n");
        }
        $file->write();
    }

    private function logTaskExecution($task,$forced = false) {
        if ($this->isLogEnabled) {
            $logsPath = ROOT_PATH.DS.APP_DIR.DS.'sto'.DS.'logs';
            $logFile = $logsPath.DS.'tasks.log';
            $file = new File($logFile);
            $file->create(true);

            $this->logExecHelper($forced, $task, $file);
        }
    }

    /**
     *
     * @param array $retVal
     * @param AbstractTask $task
     * @param bool $xForce
     * @param SchedulerCommand|null $command
     */
    private static function runTaskHelper(array &$retVal, AbstractTask $task, bool $xForce, ?SchedulerCommand $command = null) {
        if ($task->isTime() || $xForce) {
            if ($command !== null) {
                $task->setCommand($command);

                foreach ($task->getArguments() as $attr) {
                    $command->addArg($attr->getName(), [
                        'default' => $attr->getDefault()
                    ]);
                    $val = $command->getArgValue($attr->getName());

                    if ($val !== null) {
                        $_POST[$attr->getName()] = $val;
                    }
                }
            }
            self::get()->setActiveTaskHelper($task);
        }

        if ($task->exec($xForce)) {
            self::get()->logTaskExecution($task,$xForce);
            $retVal['executed-count']++;

            if ($task->isSuccess() === true) {
                $retVal['successfully-completed'][] = $task->getTaskName();
            } else if ($task->isSuccess() === false) {
                $retVal['failed'][] = $task->getTaskName();
            }
        }
        self::get()->setActiveTaskHelper();
    }
    /**
     *
     * @param AbstractTask|null $task
     * @since 1.0.4
     */
    private function setActiveTaskHelper(?AbstractTask $task = null) {
        $this->activeTask = $task;

        if ($task !== null) {
            self::log('Active task: "'.$task->getTaskName().'" ...');
        }
    }
    private function setLogEnabledHelper(bool $bool) {
        $this->isLogEnabled = $bool;
    }
    /**
     *
     * @param string $pass
     * @since 1.0
     */
    private function setPasswordHelper(string $pass) {
        if (strlen($pass) != 0) {
            $this->accessPass = hash('sha256', $pass);
            return;
        }
        $this->accessPass = $pass;
    }
}
