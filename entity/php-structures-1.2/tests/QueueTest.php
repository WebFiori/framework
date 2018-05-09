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
require '../Queue.php';
class StackTest{
    private $queue;
    public function __construct($max=0) {
        echo '<b style="background-color:green">-------------New Test----------------</b><br/>';
        echo 'Creating new queue with max = '.$max.'<br/>';
        $this->queue = new Queue($max);
    }
    public function queue(){
        return $this->queue;
    }

    public function enqueueTest($el,$expSize,$expPushResult){
        echo '<b style="background-color:gray">--Testing the function "Queue::enqueue($el)"--</b><br/>';
        echo 'Expected Size: '.$expSize.'<br/>';
        echo 'Expected return value: ';
        $exp = ($expPushResult) ? 'TRUE<br/>':'FALSE<br/>';
        echo $exp;
        echo $this->queue.'<br/>';
        echo 'Ading the element "'.$el.'" to the queue.<br/>';
        $boolPush = $this->queue->enqueue($el);
        $pushResult = $boolPush ? 'TRUE<br/>':'FALSE</br/>';
        $newSize = $this->queue->size();
        echo $this->queue.'<br/>';
        echo 'New Size: '.$newSize.'<br/>';
        echo 'Result: '.$pushResult;
        echo 'Test Result: ';
        if($boolPush == $expPushResult && $expSize == $newSize){
            echo '<b style="color:green">PASS</b><br/>';
        }
        else{
            echo '<b style="color:red">FAIL</b><br/>';
        }
    }
    public function dequeueTest($expSize){
        echo '<b style="background-color:gray">--Testing the function "Queue::dequeue()"--</b><br/>';
        echo 'Expected Size: '.$expSize.'<br/>';
        $expected = $this->queue->peek();
        echo 'Expected return value: '.$expected.' ('. gettype($expected).')<br/>';
        echo $this->queue.'<br/>';
        echo 'Removent an element from the queue.<br/>';
        $poped = $this->queue->dequeue();
        $newSize = $this->queue->size();
        echo $this->queue.'<br/>';
        echo 'New Size: '.$newSize.'<br/>';
        echo 'Pop result: '.$poped.' ('. gettype($poped).')<br/>';
        echo 'Test Result: ';
        if($poped === $expected && $expSize == $newSize){
            echo '<b style="color:green">PASS</b><br/>';
        }
        else{
            echo '<b style="color:red">FAIL</b><br/>';
        }
    }
}
function test1(){
    $test1 = new StackTest();
    $test1->enqueueTest('1', $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest(NULL, $test1->queue()->size(), FALSE);
    $test1->enqueueTest('1', $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest(1, $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest(1, $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest(1, $test1->queue()->size()+1, TRUE);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest(0);
}
function test2(){
    $test1 = new StackTest(3);
    $test1->enqueueTest(1, $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest('NULL', $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest('Nice', $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest(1, $test1->queue()->size(), FALSE);
    $test1->enqueueTest(1, $test1->queue()->size(), FALSE);
    $test1->enqueueTest(1, $test1->queue()->size(), FALSE);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->enqueueTest('Nice, it works', $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest('oh', $test1->queue()->size()+1, TRUE);
    $test1->enqueueTest('Can\'t push', $test1->queue()->size(), FALSE);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest($test1->queue()->size()-1);
    $test1->dequeueTest(0);
}
test1();
test2();