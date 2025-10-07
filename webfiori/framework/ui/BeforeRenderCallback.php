<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2022 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\ui;

use WebFiori\Collections\Comparable;
/**
 * A class which is used to represent before render callbacks that can be assigned
 * to a web page.
 *
 * @author Ibrahim
 */
class BeforeRenderCallback implements Comparable {
    private $callback;
    private $id;
    private $isExecuted;
    private $params;
    private $priority;

    /**
     * Creates new instance of the class.
     *
     * @param callable $func An executable PHP function. The first
     * parameter of the function will be always an instance of the
     * class 'WebPage'.
     *
     * @param int $priority A positive number that represents the priority of
     * the callback. Large number means that
     * the callback has higher priority. This means a callback with priority
     * 100 will have higher priority than a callback with priority 80. If
     * a negative number is provided, 0 will be set as its priority.
     *
     * @param array $params An optional array that can hold extra parameters to
     * pass to the callback.
     *
     */
    public function __construct(callable $func, int $priority = 0, array $params = []) {
        $this->setCallback($func, $params);
        $this->setPriority($priority >= 0 ? $priority : 0);
        $this->isExecuted = false;
        $this->setID(hash('sha256', microtime().''.$priority.random_bytes(20)));
    }
    /**
     * Sets the callback.
     * 
     * @param callable $func A function to be executed.
     * 
     * @param array $params An optional array that can hold extra parameters to
     * pass to the callback.
     */
    public function setCallback(callable $func, array $params = []) {
        $this->callback = $func;
        $this->params = $params;
    }
    /**
     * Execute the callback.
     *
     * @param WebPage $owner The page at which the callback belongs to.
     */
    public function call(WebPage $owner) {
        if (!$this->isExecuted()) {
            $paramsArr = array_merge([$owner], $this->params);
            call_user_func_array($this->callback, $paramsArr);
            $this->isExecuted = true;
        }
    }
    /**
     * Compare the callback with another one.
     *
     * @param BeforeRenderCallback $other
     *
     * @return int If current callback has higher priority, the method will return
     * a positive number. Else if the two have same priority, zero is returned.
     * Other than that, a negative value is returned if the callback has less
     * priority.
     */
    public function compare($other): int {
        return $this->getPriority() - $other->getPriority();
    }
    /**
     * Returns the identifier of the callback.
     *
     * @return string The identifier of the callback.
     */
    public function getID() : string {
        return $this->id;
    }
    /**
     * Returns the priority of the callback.
     *
     * @return int Priority of the callback. Large number means that
     * the callback has higher priority.
     */
    public function getPriority() : int {
        return $this->priority;
    }
    /**
     * Checks if the callback was executed or not.
     *
     * This method is used to tell if the method BeforeRenderCallback::call()
     * was executed or not.
     *
     * @return bool The method will return true if the callback was executed.
     * False other wise.
     */
    public function isExecuted() : bool {
        return $this->isExecuted;
    }
    /**
     * Sets a unique identifier for the callback.
     *
     * @param string $id A unique identifier for the callback.
     */
    public function setID(string $id) {
        $this->id = $id;
    }
    /**
     * Sets the priority of the callback.
     *
     * The priority must be a positive number in order to be set.
     *
     * @param int $priority Priority of the callback. Large number means that
     * the callback has higher priority. This means a callback with priority
     * 100 will have higher priority than a callback with priority 80.
     */
    public function setPriority(int $priority) {
        if ($priority >= 0) {
            $this->priority = $priority;
        }
    }
}
