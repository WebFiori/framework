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
require '../Stack.php';
class StackTest{
    private $stack;
    public function __construct($max=0) {
        echo '<b style="background-color:green">-------------New Test----------------</b><br/>';
        echo 'Creating new stack with max = '.$max.'<br/>';
        $this->stack = new Stack($max);
    }
    public function stack(){
        return $this->stack;
    }

    public function pushTest($el,$expSize,$expPushResult){
        echo '<b style="background-color:gray">--Testing the function "Stack::push($el)"--</b><br/>';
        echo 'Expected Size: '.$expSize.'<br/>';
        echo 'Expected return value: ';
        $exp = ($expPushResult) ? 'TRUE<br/>':'FALSE<br/>';
        echo $exp;
        echo $this->stack.'<br/>';
        echo 'Pushing the element "'.$el.'" to the stack.<br/>';
        $boolPush = $this->stack->push($el);
        $pushResult = $boolPush ? 'TRUE<br/>':'FALSE</br/>';
        $newSize = $this->stack->size();
        echo $this->stack.'<br/>';
        echo 'New Stack Size: '.$newSize.'<br/>';
        echo 'Push result: '.$pushResult;
        echo 'Test Result: ';
        if($boolPush == $expPushResult && $expSize == $newSize){
            echo '<b style="color:green">PASS</b><br/>';
        }
        else{
            echo '<b style="color:red">FAIL</b><br/>';
        }
    }
    public function popTest($expSize){
        echo '<b style="background-color:gray">--Testing the function "Stack::pop()"--</b><br/>';
        echo 'Expected Size: '.$expSize.'<br/>';
        $expected = $this->stack->peek();
        echo 'Expected return value: '.$expected.' ('. gettype($expected).')<br/>';
        echo $this->stack.'<br/>';
        echo 'Poping an element from the stack.<br/>';
        $poped = $this->stack->pop();
        $newSize = $this->stack->size();
        echo $this->stack.'<br/>';
        echo 'New Stack Size: '.$newSize.'<br/>';
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
    $test1->pushTest('1', $test1->stack()->size()+1, TRUE);
    $test1->pushTest(NULL, $test1->stack()->size(), FALSE);
    $test1->pushTest('1', $test1->stack()->size()+1, TRUE);
    $test1->pushTest(1, $test1->stack()->size()+1, TRUE);
    $test1->pushTest(1, $test1->stack()->size()+1, TRUE);
    $test1->pushTest(1, $test1->stack()->size()+1, TRUE);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest(0);
}
function test2(){
    $test1 = new StackTest(3);
    $test1->pushTest(1, $test1->stack()->size()+1, TRUE);
    $test1->pushTest('NULL', $test1->stack()->size()+1, TRUE);
    $test1->pushTest('Nice', $test1->stack()->size()+1, TRUE);
    $test1->pushTest(1, $test1->stack()->size(), FALSE);
    $test1->pushTest(1, $test1->stack()->size(), FALSE);
    $test1->pushTest(1, $test1->stack()->size(), FALSE);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest($test1->stack()->size()-1);
    $test1->pushTest('Nice, it works', $test1->stack()->size()+1, TRUE);
    $test1->pushTest('oh', $test1->stack()->size()+1, TRUE);
    $test1->pushTest('Can\'t push', $test1->stack()->size(), FALSE);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest($test1->stack()->size()-1);
    $test1->popTest(0);
}
test1();
test2();