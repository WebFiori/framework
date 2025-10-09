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

use Exception;

/**
 * A class which provide basic implementation for scheduling tasks.
 *
 * This class used to provide basic implementation for the class 'AbstractTask'.
 * It is recommended to not use this class in creating custom tasks. The recommended
 * option is to extend the class 'AbstractTask'.
 * @author Ibrahim
 * @version 1.0.9
 */
class BaseTask extends AbstractTask {
    /**
     * An array which contains the events that will be executed if it is the time
     * to execute the task.
     * @var array
     * @since 1.0
     */
    private $events;


    /**
     * Creates new instance of the class.
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
     * @throws Exception
     * @since 1.0
     */
    public function __construct(string $when = '* * * * *') {
        parent::__construct('SCHEDULER-TASK', $when);
        $this->events = [];
        $this->events['on'] = [];
        $this->events['on']['func'] = null;
        $this->events['on']['params'] = [];

        $this->events['on-failure'] = [];
        $this->events['on-failure']['func'] = null;
        $this->events['on-failure']['params'] = [];
    }
    /**
     * A method that does nothing.
     */
    public function afterExec() {
    }
    /**
     * Execute the task.
     *
     * @return null|boolean The return value of the method will depend on the
     * closure which is set to execute. If no closure is set, the method will
     * return null. If it is set, the return value of the closure will be returned
     * by this method.
     *
     * @since 1.0
     */
    public function execute() {
        $result = null;

        if ($this->getOnExecution() !== null) {
            $result = call_user_func($this->getOnExecution(), $this->events['on']['params']);
        }

        return $result ;
    }

    /**
     * Returns a callable which represents the code that will be
     * executed when it's time to run the task.
     *
     * @return callable|null A callable which represents the code that will be
     * executed when it's time to run the task.
     *
     * @since 1.0.3
     */
    public function getOnExecution() {
        return $this->events['on']['func'];
    }
    /**
     * Run the closure which is set to execute if the task is failed.
     */
    public function onFail() {
        if (is_callable($this->events['on-failure']['func'])) {
            call_user_func($this->events['on-failure']['func'], $this->events['on-failure']['params']);
        }
    }
    /**
     * A method that does nothing.
     */
    public function onSuccess() {
    }

    /**
     * Sets the event that will be fired in case it is time to execute the task.
     *
     * @param callable $func The function that will be executed if it is the
     * time to execute the task. This function can have a return value If the function
     * returned null or true, then it means the task was successfully executed.
     * If it returns false, this means the task did not execute successfully.
     *
     * @param array $funcParams An array which can hold some parameters that
     * can be passed to the function.
     *
     * @since 1.0
     */
    public function setOnExecution(callable $func, array $funcParams = []) {
        if (is_callable($func)) {
            $this->events['on']['func'] = $func;

            if (gettype($funcParams) == 'array') {
                $this->events['on']['params'] = $funcParams;
            }
        }
    }
    /**
     * Sets a function to call in case the task function has returned false.
     *
     * @param callable $func The function that will be executed.
     *
     * @param array $params An array of parameters that will be passed to the
     * function.
     *
     * @since 1.0.5
     */
    public function setOnFailure(callable $func, array $params = []) {
        if (is_callable($func)) {
            $this->events['on-failure']['func'] = $func;

            if (gettype($params) == 'array') {
                $this->events['on-failure']['params'] = $params;
            }
        }
    }
}
