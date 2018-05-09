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
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require '../Node.php';
require '../LinkedList.php';
runAddTests();
runRemoveTests();
runRemoveELTests();
runCountTests();
function runCountTests(){
    countTest();
    countTest(array(NULL,NULL,1,2,3,NULL,NULL), NULL, 4);
    countTest(array(NULL,NULL,1,2,3,"2",NULL,NULL), 2, 1);
    countTest(array(NULL,NULL,1,2,3,NULL,700), 700, 1);
    countTest(array(NULL,NULL,1,2,3,NULL,NULL), 6, 0);
    countTest(array('NULL',NULL,1,'2',3,'NULL',NULL), 'NULL', 2);
}
function runRemoveTests(){
    removeTest();
    removeTest(array(1,2,3,4,5), 4);
    removeTest(array(1,2,3,4,5,'44'), 44);
    removeTest(array(1,2,NULL,4,5,'44'),  1);
    removeTest(array('464'), 1);
    removeTest(array(464,6565,'vv'=>'77','xx'=>'876',77,99,6600), 6);
}
function runRemoveELTests(){
    removeElTest();
    removeElTest(array(1,2,3,4,4,5), 4, TRUE);
    removeElTest(array(1,2,3,4,5,'44'), 44, FALSE);
    removeElTest(array(1,2,NULL,4,4,5,'44'), NULL, TRUE);
    removeElTest(array('464'), 464, FALSE);
    removeElTest(array(464), 464, TRUE);
}
function runAddTests(){
    addTest();
    addTest(array('hello'),1);
    addTest(array('hello',1,2,7),4);
    addTest(array(0,1,2,3,"43x"=>4,54,'nice'=>'c'),7);
}
function removeTest($els=array(),$index=0){
    $list = new LinkedList();
    echo '<b style="background-color:gray">--Testing the function "LinkedList::remove($index)--</b>"<br/>';
    if(count($els) > $index && $index > -1){
        echo 'Expected List Size After Removing: '.(count($els) - 1).'<br/>';
    }
    else{
        echo 'Expected List Size After Removing: '.(count($els)).'<br/>';
    }
    foreach ($els as $val){
        echo 'Adding: '.$val.' ';
        if($list->add($val)){
            echo '(TRUE)<br/>';
        }
        else{
            echo '(FALSE)<br/>';
        }
    }
    echo $list.'<br/>';
    echo 'List size after adding: '.$list->size().'<br/>';
    echo 'Removing element at "'.$index.'"<br/>';
    $el = $list->remove($index);
    echo $list.'<br/>';
    echo 'List size after removing: '.$list->size().'<br/>';
    echo 'Test result: ';
    if(!$list->contains($el)){
        echo '<b style="color:green">PASS</b><br/>';
    }
    else{
        echo '<b style="color:red">FAIL</b><br/>';
    }
}
function removeElTest($els=array(),$elToremove=null,$expResult=false){
    $list = new LinkedList();
    echo '<b style="background-color:gray">--Testing the function "LinkedList::removeElement($el)--</b>"<br/>';
    if($expResult == TRUE){
        echo 'Expected List Size After Removing: '.(count($els) - 1).'<br/>';
    }
    else{
        echo 'Expected List Size After Removing: '.(count($els)).'<br/>';
    }
    foreach ($els as $val){
        echo 'Adding: '.$val.' ';
        if($list->add($val)){
            echo '(TRUE)<br/>';
        }
        else{
            echo '(FALSE)<br/>';
        }
    }
    echo $list.'<br/>';
    echo 'List size after adding: '.$list->size().'<br/>';
    echo 'Removing "'.$elToremove.' ('. gettype($elToremove).')"<br/>';
    $result = $list->removeElement($elToremove);
    echo $list.'<br/>';
    echo 'List size after removing: '.$list->size().'<br/>';
    echo 'Test result: ';
    if($result == $expResult){
        echo '<b style="color:green">PASS</b><br/>';
    }
    else{
        echo '<b style="color:red">FAIL</b><br/>';
    }
}
function addTest($els=array(),$expSize=0) {
    $list = new LinkedList();
    echo '<b style="background-color:gray">--Testing the function "LinkedList::add($el)--</b>"<br/>';
    echo 'Expected List Size After Adding: '.$expSize.'<br/>';
    foreach ($els as $val){
        echo 'Adding: '.$val.' ';
        if($list->add($val)){
            echo '(TRUE)<br/>';
        }
        else{
            echo '(FALSE)<br/>';
        }
    }
    echo $list.'<br/>';
    echo 'List size after adding: '.$list->size().'<br/>';
    echo 'Test result: ';
    if($list->size() == $expSize){
        echo '<b style="color:green">PASS</b><br/>';
    }
    else{
        echo '<b style="color:red">FAIL</b><br/>';
    }
}
function countTest($els=array(),$elToCount=NULL,$expResult=0){
    $list = new LinkedList();
    echo '<b style="background-color:gray">--Testing the function "LinkedList::count($el)--</b>"<br/>';
    echo 'Expected result: '.$expResult.'<br/>';
    echo 'Element to count: "'.$elToCount.'" ('. gettype($elToCount).')<br/>';
    foreach ($els as $val){
        echo 'Adding: '.$val.' ';
        if($list->add($val)){
            echo '(TRUE)<br/>';
        }
        else{
            echo '(FALSE)<br/>';
        }
    }
    echo $list.'<br/>';
    $count = $list->count($elToCount);
    echo 'Count = '.$count.'<br/>';
    echo 'Test result: ';
    if($count == $expResult){
        echo '<b style="color:green">PASS</b><br/>';
    }
    else{
        echo '<b style="color:red">FAIL</b><br/>';
    }
}
