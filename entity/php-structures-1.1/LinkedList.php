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
 * A class that represents a linked list.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class LinkedList {
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
     * Creats new instance of the class.
     */
    public function __construct() {
        $this->head = NULL;
        $this->tail = NULL;
        $this->size = 0;
    }
    /**
     * Returns the number of times a given element has appeared on the list.
     * @param mixed $el The element that will be checked.
     * @return int The number of times the element has appeared on the list.
     * @since 1.0
     */
    public function count($el){
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
     * @param mixed $el The element that will be checked. The comparison is based 
     * on strict equality operator '==='.
     * @return boolean <b>TRUE</b> if the element is on the list. Other than that, 
     * the function will return <b>FALSE</b>.
     * @since 1.0
     */
    public function contains($el){
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
     * is empty, The function will return <b>NULL</b>.
     * @since 1.1
     */
    public function getFirst(){
        if($this->size() >= 1){
            return $this->head->data();
        }
        return NULL;
    }
    /**
     * Returns the last element that was added to the list.
     * @return mixed The last element that was added to the list. If the list 
     * is empty, The function will return <b>NULL</b>.
     * @since 1.1
     */
    public function getLast(){
        if($this->size() == 1){
            return $this->getFirst();
        }
        else if($this->size() > 1){
            return $this->tail->data();
        }
        return NULL;
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
     * is empty or the given index is not in the list, The function will 
     * return <b>NULL</b>.
     * @since 1.1
     */
    public function get($index){
        if(gettype($index) == 'integer'){
            if($index < $this->size() && $index > -1){
                if($index == 0){
                    return $this->getFirst();
                }
                else if($index == $this->size() - 1){
                    return $this->getLast();
                }
                else{
                    $nextNode = $this->head->next();
                    $node = $this->head;
                    for($i = 1 ; ; $i++){
                        if($i == $index){
                            $data = $nextNode->data();
                            return $data;
                        }
                        $node = $nextNode;
                        $nextNode = $nextNode->next();
                    }
                }
            }
        }
        return NULL;
    }
    /**
     * Removes an element given its index.
     * @param int $index The index of the element.
     * @return mixed If the given index is in the range <b>[0, LinkedList::size() - 1]</b>, 
     * the element at the given index is returned. If the list is empty or the given 
     * index is out of the range, the function will return <b>NULL</b>. Also the 
     * function will return <b>NULL</b> if the given index is not an integer.
     * @since 1.0
     */
    public function remove($index){
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
                            $this->size--;
                            return $data;
                        }
                        $node = $nextNode;
                        $nextNode = $nextNode->next();
                    }
                }
            }
        }
        return NULL;
    }
    /**
     * Removes the first element in the list.
     * @return mixed If the list has elements, the first element is returned. 
     * If the list is empty, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function removeFirst() {
        if($this->size() == 1){
            $data = $this->head->data();
            $this->head = NULL;
            $this->tail = NULL;
            $this->size--;
            return $data;
        }
        else if($this->size() > 1){
            $data = $this->head->data();
            $this->head = $this->head->next();
            $this->size--;
            return $data;
        }
        return NULL;
    }
    /**
     * Removes the last element in the list.
     * @return mixed If the list has elements, the last element is returned. 
     * If the list is empty, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function removeLast(){
        if($this->size() == 1){
            return $this->removeFirst();
        }
        else if($this->size() > 1){
            $nextNode = $this->head->next();
            $node = $this->head;
            while ($nextNode->next() != NULL){
                $node = $nextNode;
                $nextNode = $nextNode->next();
            }
            $data = $nextNode->data();
            $node->setNext(NULL);
            return $data;
        }
        return NULL;
    }
    /**
     * Removes a specific element from the list.
     * @param mixed $val The element that will be removed. The function 
     * will remove the first occurrence of the element if it is repeated. Note 
     * that the function use strict comparison to check for equality.
     * @return mixed The function will return <b>TRUE</b> if the given element 
     * is removed. Other than that, the function will return <b>FALSE</b>.
     * @since 1.0
     */
    public function removeElement($val){
        if($this->size() == 1){
            if($this->head->data() === $val){
                if($this->removeFirst() != NULL){
                    return TRUE;
                }
            }
        }
        else if($this->size() > 1){
            if($this->head->data() === $val){
                if($this->removeFirst() != NULL){
                    return TRUE;
                }
            }
            else{
                $node = $this->head;
                $nextNode = $this->head->next();
                while ($nextNode != NULL){
                    $data = $nextNode->data();
                    if($data === $val){
                        $node->setNext($nextNode->next());
                        $this->size--;
                        return TRUE;
                    }
                    $node = $nextNode;
                    $nextNode = $nextNode->next();
                }
            }
        }
        return FALSE;
    }
    /**
     * Adds new element to the list.
     * @param mixed $el The element that will be added. It can be of any type.
     * @return boolean <b>TRUE</b> if the element is added.
     * @since 1.0
     */
    public function add($el){
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
    public function __toString() {
        $retVal = 'List[';
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
