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

use webfiori\collections\Comparable;
/**
 * A class which is used to represent before render callbacks that can be assigned
 * to a web page.
 *
 * @author Ibrahim
 */
class BeforeRenderCallback implements Comparable {
    private $params;
    private $callback;
    private $priority;
    private $id;

    /**
     * Creates new instance of the class.
     * 
     * @param callable $func An executable PHP function. The first
     * parameter of the function will be always an instance of the
     * class 'WebPage'.
     * 
     * @param array $params An optional array that can hold extra parameters to
     * pass to the callback.
     * 
     * @param int $priority A positive number that represents the priority of
     * the callback. Large number means that
     * the callback has higher priority. This means a callback with priority
     * 100 will have higher priority than a callback with priority 80. If
     * a negative number is provided, 0 will be set as its priority.
     */
    public function __construct(callable $func, array $params, int $priority = 0) {
        $this->callback = $func;
        $this->params = $params;
        $this->priority = $priority >= 0 ? $priority : 0;
    }
    /**
     * Sets a unique identifier for the callback.
     * 
     * @param int $id A unique identifier for the callback.
     */
    public function setID(int $id) {
        $this->id = $id;
    }
    /**
     * Returns the identifier of the callback.
     * 
     * @return int The identifier of the callback.
     */
    public function getID() : int {
        return $this->id;
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
    /**
     * Execute the callback.
     * 
     * @param WebPage $owner The page at which the callback belongs to.
     */
    public function call(WebPage $owner) {
        $paramsArr = array_merge([$owner], $this->params);
        call_user_func_array($this->callback, $paramsArr);
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

}
