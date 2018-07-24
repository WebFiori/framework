<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
/**
 * A class that represents a stack data structure.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class Queue{
    /**
     * A null guard for the functions that return null reference.
     * @since 1.1
     */
    private $null;
    /**
     * The first element in the queue.
     * @var Node
     * @since 1.0 
     */
    private $head;
    /**
     * The last queued element.
     * @var Node
     * @since 1.0 
     */
    private $tail;
    /**
     * The number of elements in the queue.
     * @var int
     * @since 1.0 
     */
    private $size;
    /**
     * The maximum number of elements in the queue.
     * @var int If the value is 0 or a negative number, the maximum number of 
     * in the queue will be unlimited.
     * @since 1.0 
     */
    private $max;
    /**
     * Constructs a new instance of the class.
     * @param int $max [Optional] The maximum number of elements the queue can hold. 
     * if a negative number is given or 0, the queue will have unlimited number 
     * of elements. Also if the given value is not an integer, the maximum will be set 
     * to unlimited. Default is 0.
     */
    public function __construct($max=0) {
        $this->head = NULL;
        $this->tail = NULL;
        $this->null = NULL;
        $this->size = 0;
        if(gettype($max) == 'integer'){
            $this->max = $max;
        }
        else{
            $this->max = 0;
        }
    }
    /**
     * Adds new element to the bottom of the queue.
     * @param mixed $el The element that will be added. If it is NULL, the 
     * function will not add it.
     * @return boolean The function will return TRUE if the element is added. 
     * The function will return FALSE only in two cases, If the maximum 
     * number of elements is reached and trying to add new one or the given element 
     * is NULL.
     * @since 1.0
     */
    public function enqueue(&$el){
        if($this->validateSize()){
            if($el != NULL){
                if($this->size() == 0){
                    $this->head = new Node($el);
                    $this->size++;
                    return TRUE;
                }
                else if($this->size() == 1){
                    $this->tail = new Node($el);
                    $this->head->setNext($this->tail);
                    $this->size++;
                    return TRUE;
                }
                else{
                    $node = $this->head;
                    while ($node->next() != NULL){
                        $node = $node->next();
                    }
                    $this->tail = new Node($el);
                    $node->setNext($this->tail);
                    $this->size++;
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * Returns the number of maximum elements the queue can hold.
     * @return int If the maximum number of elements was set to 0 or a 
     * negative number, the function will return -1 which indicates 
     * that the queue can have any number of elements. Other than that, 
     * the function will return the maximum number of elements.
     * @since 1.0
     */
    public function max(){
        if($this->max <= 0){
            return -1;
        }
        return $this->max;
    }
    /**
     * Returns the element that exist on the top of the queue.
     * @return mixed The element at the top. If the stack is empty, the function 
     * will return NULL.
     * @since 1.0
     */
    public function &peek(){
        if($this->size() >= 1){
            return $this->head->data();
        }
        else{
            return $this->null;
        }
    }
    /**
     * Removes the top element from the stack.
     * @return mixed The element after removal from the queue. If the queue is 
     * empty, the function will return NULL.
     * @since 1.0
     */
    public function &dequeue(){
        if($this->size > 1){
            $data = $this->head->data();
            $this->head = $this->head->next();
            $this->size--;
            return $data;
        }
        else if($this->size == 1){
            $data = $this->head->data();
            $this->head = NULL;
            $this->tail = NULL;
            $this->size--;
            return $data;
        }
        else{
            return $this->null;
        }
    }
    /**
     * Returns the number of elements in the queue.
     * @return int The number of elements in the queue.
     * @since 1.0
     */
    public function size(){
        return $this->size;
    }
    /**
     * Checks if the queue can hold more elements or not.
     * @return boolean TRUE if the queue can hold more elements.
     * @since 1.0
     */
    private function validateSize(){
        $max = $this->max();
        if($max == -1){
            return TRUE;
        }
        if($max > $this->size()){
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns a string that represents the queue and its element.
     * @return string A string that represents the queue and its element. The 
     * string will be wrapped inside a 'pre' html element to make it well 
     * formatted and viewable in the web browser.
     */
    public function __toString() {
        $retVal = 'Queue[';
        $node = $this->head;
        while ($node != NULL){
            $data = $node->data();
            if($node->next() == NULL){
                $retVal .= $data.'('. gettype($data).')';
            }
            else{
                $retVal .= $data.'('. gettype($data).'), ';
            }
            $node = $node->next();
        }
        $retVal .= ']';
        return $retVal;
    }
}