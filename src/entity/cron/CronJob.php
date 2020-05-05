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

/**
 * A class thar represents a cron job.
 * This class used to provide basic implementation for the class 'AbstractJob'. 
 * It is recommended to not use this class in creating custom jobs. The recommended 
 * option is to extend the class 'AbstractJob'. 
 * @author Ibrahim
 * @version 1.0.9
 */
class CronJob extends AbstractJob{

    /**
     * An array which contains the events that will be executed if it is the time 
     * to execute the job.
     * @var array
     * @since 1.0 
     */
    private $events;


    /**
     * Creates new instance of the class.
     * @param string $when A cron expression. An exception will be thrown if 
     * the given expression is invalid. Default is '* * * * *' which means run 
     * the job every minute.
     * @param boolean $autoReg If set to true, the job will be scheduled without 
     * having to add it to jobs queue. Default is false.
     * @throws Exception
     * @since 1.0
     */
    public function __construct($when = '* * * * *') {
        parent::__construct('CRON-JOB', $when);
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
        
    }

    /**
     * Returns a callable which represents the code that will be 
     * executed when its time to run the job.
     * @return Callable|null A callable which represents the code that will be 
     * executed when its time to run the job.
     * @since 1.0.3
     */
    public function getOnExecution() {
        return $this->events['on']['func'];
    }

    /**
     * Sets the event that will be fired in case it is time to execute the job.
     * @param callable $func The function that will be executed if it is the 
     * time to execute the job. This function can have a return value If the function 
     * returned null or true, then it means the job was successfully executed. 
     * If it returns false, this means the job did not execute successfully.
     * @param array $funcParams An array which can hold some parameters that 
     * can be passed to the function.
     * @since 1.0
     */
    public function setOnExecution($func,$funcParams = []) {
        if (is_callable($func)) {
            $this->events['on']['func'] = $func;

            if (gettype($funcParams) == 'array') {
                $this->events['on']['params'] = $funcParams;
            }
        }
    }
    /**
     * Sets a function to call in case the job function has returned false.
     * @param callable $func The function that will be executed.
     * @param array $params An array of parameters that will be passed to the 
     * function.
     * @since 1.0.5
     */
    public function setOnFailure($func,$params = []) {
        if (is_callable($func)) {
            $this->events['on-failure']['func'] = $func;

            if (gettype($params) == 'array') {
                $this->events['on-failure']['params'] = $params;
            }
        }
    }
    /**
     * A method that does nothing.
     */
    public function afterExec() {
        
    }
    /**
     * A method that does nothing.
     */
    public function beforeExec() {
        
    }
    /**
     * Execute the job.
     * @return null|boolean The return value of the method will depend on the 
     * closure which is set to execute. If no closure is set, the method will 
     * return null. If it is set, the return value of the closure will be returned 
     * by this method.
     * @since 1.0
     */
    public function execute() {
        $result = null;
        if($this->getOnExecution() !== null){
            $result = call_user_func($this->getOnExecution(), $this->events['on']['params']);
        }
        return $result ;
    }
    /**
     * Run the closure which is set to execute if the job is failed.
     */
    public function onFail() {
        if(is_callable($this->events['on-failure']['func'])){
            call_user_func($this->events['on-failure']['func'], $this->events['on-failure']['params']);
        }
    }
    /**
     * A method that does nothing.
     */
    public function onSuccess() {
        
    }

}
