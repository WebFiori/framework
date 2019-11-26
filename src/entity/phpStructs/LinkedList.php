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
use Iterator;
/**
 * A class that represents a linked list data structure.
 *
 * @author Ibrahim 
 * @version 1.4.3
 */
class LinkedList implements Countable, Iterator{
    /**
     * A node which is used for iterator related methods.
     * @var Node
     * @since 1.4.3 
     */
    private $iteratorEl;
    /**
     * The maximum number of elements the list can have.
     * @var int 
     * @since 1.4.1
     */
    private $maxEls;
    /**
     * A null guard for the methods that return null reference.
     * @since 1.4
     */
    private $null;
    /**
     * The first node in the list.
     * @var Node
     * @since 1.0 
     */
    private $head;
    /**
     * The last node in the list.
     * @var Node 
     * @since 1.0
     */
    private $tail;
    /**
     * The number of elements in the node.
     * @var int
     * @since 1.0 
     */
    private $size;
    /**
     * Creates new instance of the class.
     * @param int $max The maximum number of elements that the list can hold. 
     * If 0 or a negative number is given, the list will be able to hold 
     * unlimited number of elements.
     */
    public function __construct($max=0) {
        $this->null = null;
        $this->head = null;
        $this->tail = null;
        $this->size = 0;
        if(gettype($max) == 'integer'){
            $this->maxEls = $max;
        }
        else{
            $this->maxEls = -1;
        }
    }
    /**
     * Returns the number of maximum elements the list can hold.
     * @return int If the maximum number of elements was set to 0 or a 
     * negative number, the method will return -1 which indicates 
     * that the list can have any number of elements. Other than that, 
     * the method will return the maximum number of elements.
     * @since 1.4.1
     */
    public function max(){
        if($this->maxEls <= 0){
            return -1;
        }
        return $this->maxEls;
    }
    /**
     * Returns the number of elements in the list.
     * Calling this method is simply like calling the method LinkedList::size().
     * @return int The number of elements in the list.
     * @since 1.4.2
     */
    public function count() {
        return $this->size();
    }
    /**
     * Checks if the list can hold more elements or not.
     * @return boolean true if the list can hold more elements.
     * @since 1.4.1
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
     * Returns the number of times a given element has appeared on the list.
     * @param mixed $el The element that will be counted.
     * @return int The number of times the element has appeared on the list. If 
     * the element does not exist, 0 is returned.
     * @since 1.0
     */
    public function countElement(&$el){
        $count = 0;
        if($this->size() == 1){
            if($this->head->data() === $el){
                $count++;
            }
        }
        else{
            $node = $this->head;
            while ($node != null){
                if($node->data() === $el){
                    $count++;
                }
                $node = $node->next();
            }
        }
        return $count;
    }
    /**
     * Checks if a given element is in the list or not.
     * Note that the method uses strict equality operator '===' to check 
     * for element existence.
     * @param mixed $el The element that will be checked.
     * @return boolean true if the element is on the list. Other than that, 
     * the method will return false.
     * @since 1.0
     */
    public function contains(&$el){
        if($this->size() == 0){
            return false;
        }
        else if($this->size() == 1){
            return $this->head->data() === $el;
        }
        else{
            $node = $this->head;
            while($node != null){
                if($node != null && $node->data() === $el){
                    return true;
                }
                $node = $node->next();
            }
            return false;
        }
    }
    /**
     * Returns the first element that was added to the list.
     * @return mixed The first element that was added to the list. If the list 
     * is empty, The method will return null.
     * @since 1.1
     */
    public function &getFirst(){
        if($this->size() >= 1){
            $data = &$this->head->data();
            return $data;
        }
        return $this->null;
    }
    /**
     * Returns the last element that was added to the list.
     * @return mixed The last element that was added to the list. If the list 
     * is empty, The method will return null.
     * @since 1.1
     */
    public function &getLast(){
        if($this->size() == 1){
            $data = &$this->getFirst();
        }
        else if($this->size() > 1){
            $data = &$this->tail->data();
        }
        else{
            $data = $this->null;
        }
        return $data;
    }
    /**
     * Removes all of the elements from the list.
     * @since 1.1
     */
    public function clear(){
        $this->head = null;
        $this->tail = null;
        $this->size = 0;
    }
    /**
     * Returns the element at the specified index.
     * @return mixed The element at the specified index. If the list 
     * is empty or the given index is out of list bounds, The method will 
     * return null.
     * @since 1.1
     */
    public function &get($index){
        if(gettype($index) == 'integer'){
            if($index < $this->size() && $index > -1){
                if($index == 0){
                    $first = &$this->getFirst();
                    return $first;
                }
                else if($index == $this->size() - 1){
                    $last = &$this->getLast();
                    return $last;
                }
                else{
                    $nextNode = $this->head->next();
                    $node = $this->head;
                    for($i = 1 ; ; $i++){
                        if($i == $index){
                            $data = &$nextNode->data();
                            return $data;
                        }
                        $node = $nextNode;
                        $nextNode = $nextNode->next();
                    }
                }
            }
        }
        return $this->null;
    }
    /**
     * Returns an array that contains the elements of the list.
     * @return array An array that contains the elements of the list.
     * @since 1.3
     */
    public function toArray() {
        $array = [];
        if($this->size() == 1){
            array_push($array, $this->head->data());
        }
        else if($this->size() == 0){
            
        }
        else{
            $node = $this->head;
            while ($node->next() != null){
                array_push($array, $node->data());
                $node = $node->next();
            }
            array_push($array, $node->data());
        }
        return $array;
    }
    /**
     * Sort the elements of the list using insertion sort algorithm.
     * @param boolean $ascending If set to true, list elements 
     * will be sorted in ascending order (From lower to higher). Else, 
     * they will be sorted in descending order (From higher to lower). Default is 
     * true.
     * @return boolean The method will return true if list 
     * elements have been sorted. The only cases that the method 
     * will return false is when the list has an object which does 
     * not implement the interface Comparable or it has a mix of objects and primitive types.
     * @since 1.3
     */
    public function insertionSort($ascending=true) {
        $array = $this->toArray();
        $count = count($array);
        $hasObject = false;
        for($i = 0 ; $i < $count ; $i++){
            $val = $array[$i];
            $j = $i - 1;
            if(gettype($val) == 'object'){
                $hasObject = true;
                if($val instanceof Comparable){
                    while($j >= 0 && $val->compare($array[$j]) < 0){
                        $array[$j + 1] = $array[$j];
                        $j--;
                    }
                    $array[$j + 1] = $val;
                }
                else{
                    return false;
                }
            }
            else if(!$hasObject){
                while($j >= 0 && $array[$j] > $val){
                    $array[$j + 1] = $array[$j];
                    $j--;
                }
                $array[$j + 1] = $val;
            }
            else{
                return false;
            }
	}
        while ($this->size() != 0){
            $this->remove(0);
        }
        if($ascending === true){
            foreach ($array as $val){
                $this->add($val);
            }
        }
        else{
            $count = count($array);
            for($x = $count - 1 ; $x > -1 ; $x--){
                $this->add($array[$x]);
            }
        }
        return true;
    }
    /**
     * Removes an element given its index.
     * If the given index is in the range [0, LinkedList::size() - 1] 
     * the element at the given index is returned. If the list is empty or the given 
     * index is out of the range, the method will return null. Also the 
     * method will return null if the given index is not an integer.
     * @param int $index The index of the element.
     * @return mixed The element that was removed. null if no element is removed.
     * @since 1.0
     */
    public function &remove($index){
        if(gettype($index) == 'integer'){
            if($index < $this->size() && $index > -1){
                if($index == 0){
                    return $this->removeFirst();
                }
                else if($index == $this->size() - 1){
                    return $this->removeLast();
                }
                else{
                    $nextNode = $this->head->next();
                    $node = $this->head;
                    for($i = 1 ; ; $i++){
                        if($i == $index){
                            $data = $nextNode->data();
                            $node->setNext($nextNode->next());
                            $this->_reduceSize();
                            return $data;
                        }
                        $node = $nextNode;
                        $nextNode = $nextNode->next();
                    }
                }
            }
        }
        return $this->null;
    }
    /**
     * Removes the first element in the list.
     * @return mixed If the list has elements, the first element is returned. 
     * If the list is empty, the method will return null.
     * @since 1.0
     */
    public function &removeFirst() {
        if($this->size() == 1){
            $data = &$this->head->data();
            $this->head = null;
            $this->tail = null;
            $this->_reduceSize();
            return $data;
        }
        else if($this->size() > 1){
            $data = &$this->head->data();
            $this->head = &$this->head->next();
            $this->_reduceSize();
            return $data;
        }
        return $this->null;
    }
    /**
     * Removes the last element in the list.
     * @return mixed If the list has elements, the last element is returned. 
     * If the list is empty, the method will return null.
     * @since 1.0
     */
    public function &removeLast(){
        if($this->size() == 1){
            return $this->removeFirst();
        }
        else if($this->size() > 1){
            $nextNode = &$this->head->next();
            $node = $this->head;
            while ($nextNode->next() != null){
                $node = $nextNode;
                $nextNode = &$nextNode->next();
            }
            $data = &$nextNode->data();
            $null = null;
            $node->setNext($null);
            $this->tail = $node;
            $this->_reduceSize();
            return $data;
        }
        return $this->null;
    }
    /**
     * Reduce the size of the list.
     * called after removing an element.
     * @since 1.3
     */
    private function _reduceSize() {
        if($this->size > 0){
            $this->size--;
        }
    }
    /**
     * Removes a specific element from the list.
     * The method will remove the first occurrence of the element if it is 
     * repeated. Note that the method use strict comparison to check for equality.
     * @param mixed $val The element that will be removed.
     * @return mixed The method will return The element after removal if the given element 
     * is removed. Other than that, the method will return null.
     * @since 1.0
     */
    public function &removeElement(&$val){
        if($this->size() == 1){
            if($this->head->data() === $val){
                $el = &$this->head->data();
                if($this->removeFirst() != null){
                    return $el;
                }
            }
        }
        else if($this->size() > 1){
            if($this->head->data() === $val){
                $el = &$this->head->data();
                if($this->removeFirst() != null){
                    return $el;
                }
            }
            else if($this->tail->data() === $val){
                $el = &$this->tail->data();
                if($this->removeLast() !== null){
                    return $el;
                }
            }
            else{
                $node = $this->head;
                $nextNode = &$this->head->next();
                while ($nextNode != null){
                    $data = &$nextNode->data();
                    if($data === $val){
                        $node->setNext($nextNode->next());
                        $this->_reduceSize();
                        return $data;
                    }
                    $node = $nextNode;
                    $nextNode = &$nextNode->next();
                }
            }
        }
        return $this->null;
    }
    /**
     * Returns the index of an element.
     * Note that the method is using strict comparison operator to search (===). 
     * The method will return the index of the first match it found if the list 
     * contain the same element more than once.
     * @param mixed $el The element to search for.
     * @return int The index of the element if found. If the list does not contain 
     * the element or is empty, the method will return -1.
     * @since 1.2
     */
    public function indexOf($el){
        if($this->size() == 1){
            return $this->head->data() === $el ? 0 : -1;
        }
        else if($this->size() == 0){
            return -1;
        }
        else{
            $tmpIndex = 0;
            $node = $this->head;
            while ($node->next() != null){
                if($node->data() === $el){
                    return $tmpIndex;
                }
                $node = &$node->next();
                $tmpIndex++;
            }
            if($node->data() === $el){
                return $tmpIndex;
            }
        }
        return -1;
    }
    /**
     * Replace an element with new one.
     * @param mixed $oldEl The element that will be replaced.
     * @param mixed $newEl The element that will replace the old one.
     * @return boolean The method will return true if replaced. 
     * if the element is not replaced, the method will return false.
     * @since 1.2
     */
    public function replace(&$oldEl,&$newEl){
        if($this->size() == 1){
            if($this->head->data() === $oldEl){
                $this->head->setData($newEl);
                return true;
            }
        }
        else if($this->size() > 1){
            if($this->head->data() === $oldEl){
                $this->head->setData($newEl);
                return true;
            }
            else{
                $node = $this->head;
                $nextNode = &$this->head->next();
                while ($nextNode != null){
                    $data = &$nextNode->data();
                    if($data === $oldEl){
                        $nextNode->setData($newEl);
                        return true;
                    }
                    $node = $nextNode;
                    $nextNode = &$nextNode->next();
                }
            }
        }
        return false;
    }
    /**
     * Adds new element to the list.
     * @param mixed $el The element that will be added. It can be of any type.
     * @return boolean true if the element is added. The method will return 
     * false only if the list accepts a limited number of elements and that 
     * number has been reached.
     * @since 1.0
     */
    public function add(&$el){
        if($this->validateSize()){
            if($this->head == null){
                $this->head = new Node($el);
                $this->size = 1;
                return true;
            }
            else if($this->size() == 1){
                $this->tail = new Node($el);
                $this->head->setNext($this->tail);
                $this->size++;
                return true;
            }
            else{
                $node = $this->head;
                while ($node->next() != null){
                    $node = $node->next();
                }
                $this->tail = new Node($el);
                $node->setNext($this->tail);
                $this->size++;
                return true;
            }
        }
        return false;
    }
    /**
     * Returns the number of elements in the list.
     * @return int The number of elements in the list.
     * @since 1.0
     */
    public function size(){
        return $this->size;
    }
    /**
     * Returns a string that represents the list and its element.
     * @return string A string that represents the list and its element.
     */
    public function __toString() {
        $retVal = "List[\n";
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
     * Returns the element that the iterator is currently is pointing to.
     * This method is only used if the list is used in a 'foreach' loop. 
     * The developer should not call it manually unless he knows what he 
     * is doing.
     * @return mixed The element that the iterator is currently is pointing to.
     * @since 1.4.3 
     */
    public function current() {
        if($this->iteratorEl !== null){
            return $this->iteratorEl->data();
        }
        return null;
    }
    /**
     * Returns the current node in the iterator.
     * This method is only used if the list is used in a 'foreach' loop. 
     * The developer should not call it manually unless he knows what he 
     * is doing.
     * @return Node|null An object of type 'Node' or null if the list is empty or 
     * the iterator is finished.
     * @since 1.4.3 
     */
    public function key() {
        return $this->iteratorEl;
    }
    /**
     * Returns the next element in the iterator.
     * This method is only used if the list is used in a 'foreach' loop. 
     * The developer should not call it manually unless he knows what he 
     * is doing.
     * @return mixed|null The next element in the iterator. If the iterator is 
     * finished or the list is empty, the method will return null.
     * @since 1.4.3 
     */
    public function next() {
        $this->iteratorEl = $this->iteratorEl->next();
        if($this->iteratorEl !== null){
            return $this->iteratorEl->data();
        }
        return null;
    }
    /**
     * Return iterator pointer to the first element in the list.
     * This method is only used if the list is used in a 'foreach' loop. 
     * The developer should not call it manually unless he knows what he 
     * is doing.
     * @since 1.4.3 
     */
    public function rewind() {
        $this->iteratorEl = $this->head;
    }
    /**
     * Checks if the iterator has more elements or not.
     * This method is only used if the list is used in a 'foreach' loop. 
     * The developer should not call it manually unless he knows what he 
     * is doing.
     * @return boolean If there is a next element, the method 
     * will return true. False otherwise.
     * @since 1.4.3 
     */
    public function valid() {
        return $this->iteratorEl !== null;
    }

}
