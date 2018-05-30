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
require_once '../../root.php';
$GLOBALS['NUM_OF_TESTS'] = 0;
$GLOBALS['PASSED_TESTS'] = 0;
$GLOBALS['FAILED_TESTS'] = 0;
$start = -8;
$end = 8;
maxBinayIntTest();
minBinayIntTest();
minHexIntTest();
maxHexIntTest();
for($x = $start; $x <= $end ; $x++){
    for($y = $start ; $y <= $end ; $y++){
        //binarySubTest($x, $y, ($x - $y));
    }
}

printTestResults();
function binaryAddTest($num1,$num2, $expInt){
    $GLOBALS['NUM_OF_TESTS']++;
    echo '------Num1 = '.$num1.' Num2 = '.$num2.' exp = '.$expInt.'--------<br/>';
    $num1AsBinary = Num::intToBinary($num1);
    echo 'Num::intToBinary('.$num1.') = '.$num1AsBinary.'<br/>';
    $num2AsBinary = Num::intToBinary($num2);
    echo 'Num::intToBinary('.$num2.') = '.$num2AsBinary.'<br/>';
    $num1BplusNum2B = Num::binaryAdd($num1AsBinary, $num2AsBinary);
    echo 'Num::binaryAdd('.$num1AsBinary.','.$num2AsBinary.') = '.$num1BplusNum2B.'<br/>';
    $bResultAsInt = Num::binaryToInt($num1BplusNum2B);
    echo 'Num::binaryToInt('.$num1BplusNum2B.') = '.$bResultAsInt.'<br/>';
    if($bResultAsInt == ($num1 - $num2)){
        echo 'Test Result: <b style="color:green">PASS</b><br/>';
        $GLOBALS['PASSED_TESTS']++;
    }
    else{
        echo 'Test Result: <b style="color:red">FAIL</b><br/>';
        $GLOBALS['FAILED_TESTS']++;
    }
}
function binarySubTest($num1,$num2, $expInt){
    $GLOBALS['NUM_OF_TESTS']++;
    echo '------ Num1 = '.$num1.' Num2 = '.$num2.' exp = '.$expInt.'--------<br/>';
    $num1AsBinary = Num::intToBinary($num1);
    echo 'Num::intToBinary('.$num1.') = '.$num1AsBinary.'<br/>';
    $num2AsBinary = Num::intToBinary($num2);
    echo 'Num::intToBinary('.$num2.') = '.$num2AsBinary.'<br/>';
    $num1BplusNum2B = Num::binarySub($num1AsBinary, $num2AsBinary);
    echo 'Num::binarySub('.$num1AsBinary.','.$num2AsBinary.') = '.$num1BplusNum2B.'<br/>';
    $bResultAsInt = Num::binaryToInt($num1BplusNum2B);
    echo 'Num::binaryToInt('.$num1BplusNum2B.') = '.$bResultAsInt.'<br/>';
    if($bResultAsInt == $expInt){
        echo 'Test Result: <b style="color:green">PASS</b><br/>';
        $GLOBALS['PASSED_TESTS']++;
    }
    else{
        echo 'Test Result: <b style="color:red">FAIL</b><br/>';
        $GLOBALS['FAILED_TESTS']++;
    }
}
function coversionTest($num){
    echo '-------Given number: '.$num.'--------<br/>';
    $numAsB = Num::intToBinary($num);
    echo 'Num::intToBinary('.$num.') = '.$numAsB.'<br/>';
    $numAsHex = Num::intToHex($num);
    echo 'Num::intToHex('.$num.') = '.$numAsHex.'<br/>';
    $bNumAsInt = Num::binaryToInt($numAsB);
    echo 'Num::binaryToInt('.$numAsB.') = '.$bNumAsInt.'<br/>';
    $HexAsB = Num::hexToBinary($numAsHex);
    echo 'Num::hexToBinary('.$numAsHex.') = '.$HexAsB.'<br/>';
    $bAsHex = Num::binaryToHex($numAsB);
    echo 'Num::binaryToHex('.$numAsB.') = '.$bAsHex.'<br/>';
    $HexAsI = Num::hexToInt($numAsHex);
    echo 'Num::hexToInt('.$numAsHex.') = '.$HexAsI.'<br/>';
}

function maxBinayIntTest(){
    echo '---------------maxBinayIntTest()----------------<br/>';
    $GLOBALS['NUM_OF_TESTS']++;
    $b = Num::getPHPMaxBinaryInt();
    echo 'Maximum integer in binay = '.$b.'<br/>';
    $bAsInt = Num::binaryToInt($b);
    echo 'Num::binaryToInt('.$b.') = '.$bAsInt.'<br/>';
    if($bAsInt == PHP_INT_MAX){
        echo 'Test Result: <b style="color:green">PASS</b><br/>';
        $GLOBALS['PASSED_TESTS']++;
    }
    else{
        echo 'Test Result: <b style="color:red">FAIL</b><br/>';
        $GLOBALS['FAILED_TESTS']++;
    }
}

function minBinayIntTest(){
    echo '---------------minBinayIntTest()----------------<br/>';
    $GLOBALS['NUM_OF_TESTS']++;
    $b = Num::getPHPMinBinaryInt();
    echo 'Minimum integer in binay = '.$b.'<br/>';
    $bAsInt = Num::binaryToInt($b);
    echo 'Num::binaryToInt('.$b.') = '.$bAsInt.'<br/>';
    if($bAsInt == PHP_INT_MIN){
        echo 'Test Result: <b style="color:green">PASS</b><br/>';
        $GLOBALS['PASSED_TESTS']++;
    }
    else{
        echo 'Test Result: <b style="color:red">FAIL</b><br/>';
        $GLOBALS['FAILED_TESTS']++;
    }
}

function maxHexIntTest(){
    echo '---------------maxHexIntTest()----------------<br/>';
    $GLOBALS['NUM_OF_TESTS']++;
    $b = Num::getPHPMaxHexInt();
    echo 'Maximum integer in hex = '.$b.'<br/>';
    $bAsInt = Num::hexToInt($b);
    echo 'Num::hexToInt('.$b.') = '.$bAsInt.'<br/>';
    if($bAsInt == PHP_INT_MAX){
        echo 'Test Result: <b style="color:green">PASS</b><br/>';
        $GLOBALS['PASSED_TESTS']++;
    }
    else{
        echo 'Test Result: <b style="color:red">FAIL</b><br/>';
        $GLOBALS['FAILED_TESTS']++;
    }
}

function minHexIntTest(){
    echo '---------------minHexIntTest()----------------<br/>';
    $GLOBALS['NUM_OF_TESTS']++;
    $b = Num::getPHPMinHexInt();
    echo 'Minimum integer in hex = '.$b.'<br/>';
    $bAsInt = Num::hexToInt($b);
    echo 'Num::hexToInt('.$b.') = '.$bAsInt.'<br/>';
    if($bAsInt == PHP_INT_MIN){
        echo 'Test Result: <b style="color:green">PASS</b><br/>';
        $GLOBALS['PASSED_TESTS']++;
    }
    else{
        echo 'Test Result: <b style="color:red">FAIL</b><br/>';
        $GLOBALS['FAILED_TESTS']++;
    }
}

function printTestResults(){
    echo '<b>Number of tests:<b> '.$GLOBALS['NUM_OF_TESTS'].'<br/>';
    echo '<b>Number of passed tests:<b> '.$GLOBALS['PASSED_TESTS'].'<br/>';
    echo '<b>Number of failed tests:<b> '.$GLOBALS['FAILED_TESTS'].'<br/>';
    echo '<b>Success rate:<b> '.($GLOBALS['PASSED_TESTS']/$GLOBALS['NUM_OF_TESTS']*100).'%<br/>';
    echo '<b>Failure rate:<b> '.($GLOBALS['FAILED_TESTS']/$GLOBALS['NUM_OF_TESTS']*100).'%<br/>';
}


