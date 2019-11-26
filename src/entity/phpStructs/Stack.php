<?php

/* 
 * The MIT License
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh, phpStructs.
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
namespace phpStructs;
use Countable;
/**
 * A class that represents a stack data structure.
 *
 * @author Ibrahim
 * @version 1.1.1
 */
class Stack implements Countable{
    /**
     * A null guard for the methods that return null reference.
     * @since 1.1
     */
    private $null;
    /**
     * The bottom node of the stack.
     * @var Node
     * @since 1.0 
     */
    private $head;
    /**
     * The top node of the stack.
     * @var Node
     * @since 1.0 
     */
    private $tail;
    /**
     * The number of elements in the stack.
     * @var Node
     * @since 1.0 
     */
    private $size;
    /**
     * The maximum number of elements the stack can hold.
     * @var int
     * @since 1.0 
     */
    private $max;
    /**
     * Constructs a new instance of the class.
     * @param int $max The maximum number of elements the stack can hold. 
     * if a negative number is given or 0, the stack will have unlimited number 
     * of elements. Also if the given value is not an integer, the maximum will be set 
     * to unlimited. Default is 0.
     */
    public function __construct($max=0) {
        $this->null = null;
        $this->head = null;
        $this->tail = null;
        $this->size = 0;
        if(gettype($max) == 'integer'){
            $this->max = $max;
        }
        else{
            $this->max = 0;
        }
    }
    /**
     * Returns the number of maximum elements the stack can hold.
     * @return int If the maximum number of elements was set to 0 or a 
     * negative number, the method will return -1 which indicates that 
     * the stack can have infinite number of elements. Other than that, 
     * the method will return the maximum number of elements.
     * @since 1.0
     */
    public function max(){
        if($this->max <= 0){
            return -1;
        }
        return $this->max;
    }
    /**
     * Adds new element to the top of the stack.
     * @param mixed $el The element that will be added. If it is null, the 
     * method will not add it.
     * @return boolean The method will return true if the element is added. 
     * The method will return false only in two cases, If the maximum 
     * number of elements is reached and trying to add new one or the given element 
     * is null.
     * @since 1.0
     */
    public function push($el) {
        if($el !== null){
            if($this->validateSize()){
                if($this->size() == 0){
                    $this->head = new Node($el);
                    $this->size++;
                    return true;
                }
                else if($this->size() == 1){
                    $this->tail = new Node($el);
                    $this->head->setNext($this->tail);
                    $this->size++;
                    return true;
                }
                else{
                    $node = $this->tail;
                    $this->tail = new Node($el);
                    $node->setNext($this->tail);
                    $this->size++;
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Returns the element that exist on the top of the stack.
     * This method will return the last element that was added to the stack.
     * @return mixed The element at the top. If the stack is empty, the method 
     * will return null.
     * @since 1.0
     */
    public function &peek(){
        if($this->size() == 1){
            return $this->head->data();
        }
        else if($this->size() > 1){
            return $this->tail->data();
        }
        else{
            return $this->null;
        }
    }
    /**
     * Removes an element from the top of the stack.
     * The method will remove the last element that was added to the stack.
     * @return mixed The element after removal from the stack. If the stack is 
     * empty, the method will return null.
     * @since 1.0
     */
    public function &pop(){
        if($this->size() == 0){
            return $this->null;
        }
        else if($this->size() == 1){
            $data = $this->head->data();
            $this->head = null;
            $this->tail = null;
            $this->size--;
            return $data;
        }
        else{
            $node = $this->head;
            $nextNode = $this->head->next();
            while ($nextNode->next() !== null){
                $node = $nextNode;
                $nextNode = $nextNode->next();
            }
            $data = $nextNode->data();
            $null = null;
            $node->setNext($null);
            $this->tail = $node;
            $this->size--;
            return $data;
        }
    }
    /**
     * Checks if the stack can hold more elements or not.
     * @return boolean true if the stack can hold more elements.
     * @since 1.0
     */
    private function validateSize(){
        $max = $this->max();
        if($max == -1){
            return true;
        }
        if($max > $this->size()){
            return true;
        }
        return false;
    }
    
    /**
     * Returns the number of elements in the stack.
     * @return int The number of elements in the stack.
     * @since 1.0
     */
    public function size(){
        return $this->size;
    }
    /**
     * Returns a string that represents the stack and its element.
     * @return string A string that represents the stack and its element.
     */
    public function __toString() {
        $retVal = "Stack[\n";
        $node = $this->head;
        $index = 0;
        while ($node != null){
            $data = $node->data();
            $dataType = gettype($data);
            if($node->next() == null){
                if($dataType == 'object' || $dataType == 'array'){
                    $retVal .= '    ['.$index.']=>('.$dataType.")\n";
                }
                else{
                    $retVal .= '    ['.$index.']=>'.$data.'('.$dataType.")\n";
                }
            }
            else{
                if($dataType == 'object' || $dataType == 'array'){
                    $retVal .= '    ['.$index.']=>('.$dataType."),\n";
                }
                else{
                    $retVal .= '    ['.$index.']=>'.$data.'('.$dataType."),\n";
                }
            }
            $index++;
            $node = $node->next();
        }
        $retVal .= ']';
        return $retVal;
    }
    /**
     * Returns the number of elements in the stack.
     * This one is similar to calling the method "Queue::<a href="#size">size()</a>".
     * @return int Number of elements in the stack.
     * @since 1.1.1
     */
    public function count() {
        return $this->size();
    }
}