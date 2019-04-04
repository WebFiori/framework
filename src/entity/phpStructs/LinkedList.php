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
/**
 * A class that represents a linked list.
 *
 * @author Ibrahim 
 * @version 1.4
 */
class LinkedList {
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
     */
    public function __construct() {
        $this->null = NULL;
        $this->head = NULL;
        $this->tail = NULL;
        $this->size = 0;
    }
    /**
     * Returns the number of times a given element has appeared on the list.
     * @param mixed $el The element that will be counted.
     * @return int The number of times the element has appeared on the list.
     * @since 1.0
     */
    public function count(&$el){
        $count = 0;
        if($this->size() == 1){
            if($this->head->data() === $el){
                $count++;
            }
        }
        else{
            $node = $this->head;
            while ($node != NULL){
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
     * @return boolean TRUE if the element is on the list. Other than that, 
     * the method will return FALSE.
     * @since 1.0
     */
    public function contains(&$el){
        if($this->size() == 0){
            return FALSE;
        }
        else if($this->size() == 1){
            return $this->head->data() === $el;
        }
        else{
            $node = $this->head;
            while($node != NULL){
                $node = $node->next();
                if($node != null && $node->data() === $el){
                    return TRUE;
                }
            }
            return FALSE;
        }
    }
    /**
     * Returns the first element that was added to the list.
     * @return mixed The first element that was added to the list. If the list 
     * is empty, The method will return NULL.
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
     * is empty, The method will return NULL.
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
        $this->head = NULL;
        $this->tail = NULL;
        $this->size = 0;
    }
    /**
     * Returns the element at the specified index.
     * @return mixed The element at the specified index. If the list 
     * is empty or the given index is out of list bounds, The method will 
     * return NULL.
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
        $array = array();
        if($this->size() == 1){
            array_push($array, $this->head->data());
        }
        else if($this->size() == 0){
            
        }
        else{
            $node = $this->head;
            while ($node->next() != NULL){
                array_push($array, $node->data());
                $node = $node->next();
            }
            array_push($array, $node->data());
        }
        return $array;
    }
    /**
     * Sort the elements of the list using insertion sort algorithm.
     * @param boolean $ascending If set to TRUE, list elements 
     * will be sorted in ascending order (From lower to higher). Else, 
     * they will be sorted in descending order (From higher to lower).
     * @return boolean The method will return TRUE if list 
     * elements have been sorted. The only case that the method 
     * will return FALSE is when the list has an object which does 
     * not implement the interface Comparable.
     * @since 1.3
     */
    public function insertionSort($ascending=true) {
        $array = $this->toArray();
        $count = count($array);
        for($i = 0 ; $i < $count ; $i++){
            $val = $array[$i];
            $j = $i - 1;
            if(gettype($val) == 'object'){
                if($val instanceof Comparable){
                    while($j >= 0 && $val->compare($array[$j]) < 0){
                        $array[$j + 1] = $array[$j];
                        $j--;
                    }
                    $array[$j + 1] = $val;
                }
                else{
                    return FALSE;
                }
            }
            else{
                while($j >= 0 && $array[$j] > $val){
                    $array[$j + 1] = $array[$j];
                    $j--;
                }
                $array[$j + 1] = $val;
            }
	}
        while ($this->size() != 0){
            $this->remove(0);
        }
        if($ascending === TRUE){
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
        return TRUE;
    }
    /**
     * Removes an element given its index.
     * If the given index is in the range [0, LinkedList::size() - 1] 
     * the element at the given index is returned. If the list is empty or the given 
     * index is out of the range, the method will return NULL. Also the 
     * method will return NULL if the given index is not an integer.
     * @param int $index The index of the element.
     * @return mixed The element that was removed. NULL if no element is removed.
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
     * If the list is empty, the method will return NULL.
     * @since 1.0
     */
    public function &removeFirst() {
        if($this->size() == 1){
            $data = &$this->head->data();
            $this->head = NULL;
            $this->tail = NULL;
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
     * If the list is empty, the method will return NULL.
     * @since 1.0
     */
    public function &removeLast(){
        if($this->size() == 1){
            return $this->removeFirst();
        }
        else if($this->size() > 1){
            $nextNode = &$this->head->next();
            $node = $this->head;
            while ($nextNode->next() != NULL){
                $node = $nextNode;
                $nextNode = &$nextNode->next();
            }
            $data = &$nextNode->data();
            $null = NULL;
            $node->setNext($null);
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
     * is removed. Other than that, the method will return NULL.
     * @since 1.0
     */
    public function &removeElement(&$val){
        if($this->size() == 1){
            if($this->head->data() === $val){
                $el = &$this->head->data();
                if($this->removeFirst() != NULL){
                    return $el;
                }
            }
        }
        else if($this->size() > 1){
            if($this->head->data() === $val){
                $el = &$this->head->data();
                if($this->removeFirst() != NULL){
                    return $el;
                }
            }
            else{
                $node = $this->head;
                $nextNode = &$this->head->next();
                while ($nextNode != NULL){
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
     * @param mixed $el The element to search for.
     * @return int The index of the element if found. If the list does not contain 
     * the element, the method will return -1.
     * @since 1.2
     */
    public function indexOf(&$el){
        if($this->size() == 1){
            return $this->head->data() === $el ? 0 : -1;
        }
        else if($this->size() == 0){
            return -1;
        }
        else{
            $tmpIndex = 0;
            $node = $this->head;
            while ($node->next() != NULL){
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
     * @return boolean The method will return TRUE if replaced. 
     * if the element is not replaced, the method will return FALSE.
     * @since 1.2
     */
    public function replace(&$oldEl,&$newEl){
        if($this->size() == 1){
            if($this->head->data() === $oldEl){
                $this->head->setData($newEl);
                return TRUE;
            }
        }
        else if($this->size() > 1){
            if($this->head->data() === $oldEl){
                $this->head->setData($newEl);
                return TRUE;
            }
            else{
                $node = $this->head;
                $nextNode = &$this->head->next();
                while ($nextNode != NULL){
                    $data = &$nextNode->data();
                    if($data === $oldEl){
                        $nextNode->setData($newEl);
                        return TRUE;
                    }
                    $node = $nextNode;
                    $nextNode = &$nextNode->next();
                }
            }
        }
        return FALSE;
    }
    /**
     * Adds new element to the list.
     * @param mixed $el The element that will be added. It can be of any type.
     * @return boolean TRUE if the element is added.
     * @since 1.0
     */
    public function add(&$el){
        if($this->head == NULL){
            $this->head = new Node($el);
            $this->size = 1;
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
     * @return string A string that represents the list and its element. The 
     * string will be wrapped inside a 'pre' html element to make it well 
     * formatted and viewable in the web browser.
     */
    public function __toString() {
        $retVal = '<pre>List['."\n";
        $node = $this->head;
        $index = 0;
        while ($node != NULL){
            $data = $node->data();
            if($node->next() == NULL){
                $retVal .= '    '.$index.'=>'.$data.'('. gettype($data).")\n";
            }
            else{
                $retVal .= '    '.$index.'=>'.$data.'('. gettype($data)."),\n";
            }
            $index++;
            $node = $node->next();
        }
        $retVal .= ']</pre>';
        return $retVal;
    }
}
