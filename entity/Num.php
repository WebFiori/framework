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
 * A utility class that contains number elated operations Such as base conversion 
 * and so on.
 *
 * @author Ibrahim
 * @version 1.0
 */
class Num {
    /**
     * An array that contains integer values as keys along hex representation 
     * as value.
     * @since 1.0
     */
    const HEX_DIGITS = array(
        0=>'0',1=>'1',2=>'2',3=>'3',
        4=>'4',5=>'5',6=>'6',7=>'7',
        8=>'8',9=>'9',10=>'A',11=>'B',
        12=>'C',13=>'D',14=>'E',15=>'F',
    );
    /**
     * An array that contains integer values as keys along binary representation 
     * as value.
     * @since 1.0
     */
    const BINARY_DIGITS = array(
        0=>'0',1=>'1'
    );
    /**
     * A function that is used to pad a binary number with extra zeros.
     * @param string $binay
     * @param type $zerosCount
     * @return string
     * @since 1.0
     */
    private static function padWithZeros($binay,$zerosCount){
        for($x = 0 ; $x < $zerosCount ; $x++){
            $binay = '0'.$binay;
        }
        return $binay;
    }
    /**
     * A function that is used to pad a binary number with extra ones.
     * @param string $binay
     * @param type $onesCount
     * @return string
     * @since 1.0
     */
    private static function padWithOnes($binay,$onesCount){
        for($x = 0 ; $x < $onesCount ; $x++){
            $binay = '1'.$binay;
        }
        return $binay;
    }
    public static function binarySub($binary1,$binary2) {
        
    }
    public static function hexSub($hex1,$hex2){
        
    }
    public static function binaryAdd($binary1,$binary2) {
        if(Num::isBinary($binary1)){
            if(Num::isBinary($binary2)){
                $len1 = strlen($binary1);
                $len2 = strlen($binary2);
                if($len1 > $len2){
                    $binary2 = Num::padWithZeros($binary2, $len1 - $len2);
                    $len2 = strlen($binary2);
                }
                else if($len1 < $len2){
                    $binary1 = Num::padWithZeros($binary1, $len2 - $len1);
                    $len1 = strlen($binary1);
                }
                $carry = 0;
                $result = '';
                for($x = $len1 - 1 ; $x >= 0 ; $x--){
                    $r = intval($binary1[$x]) + intval($binary2[$x]) + $carry;
                    if($r == 0 || $r == 1){
                        $result = $r.''.$result;
                        $carry = 0;
                    }
                    else if($r == 2){
                        $result = '0'.$result;
                        $carry = 1;
                    }
                    else if($r == 3){
                        $result = '1'.$result;
                        $carry = 1;
                    }
                }
                if($carry != 0){
                    $result = '1'.$result;
                }
                return $result;
            }
        }
        return FALSE;
    }
    public static function hexAdd($hex1,$hex2){
        
    }
    /**
     * Checks if a given sequence of characters represents a binary number or 
     * not.
     * @param string $binaryStr A string of zeros and ones (such as '10101101'). 
     * @return boolean <b>TRUE</b> if the given string represents a binary number. 
     * <b>FALSE</b> if not. If empty string is supplied, The function will return <b>FALSE</b>.
     * @since 1.0
     */
    public static function isBinary($binaryStr) {
        $len = strlen($binaryStr);
        if($len != 0){
            for($x = 0 ; $x < $len ; $x++){
                if($binaryStr[$x] != '0' && $binaryStr[$x] != '1'){
                    return FALSE;
                }
            }
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Checks if a given sequence of characters represents a binary number or 
     * not.
     * @param string $hexStr A string that represents a number in hexadecimal (such as 'FE12A'). 
     * @return boolean <b>TRUE</b> if the given string represents a hexadecimal number. 
     * <b>FALSE</b> if not. If empty string is supplied, The function will return <b>FALSE</b>.
     * @since 1.0
     */
    public static function isHex($hexStr) {
        $len = strlen($hexStr);
        if($len != 0){
            for($x = 0 ; $x < $len ; $x++){
                if(!in_array(strtoupper($hexStr[$x]), self::HEX_DIGITS)){
                    return FALSE;
                }
            }
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Converts a signed integer to its binary representation.
     * @param int $val The integer that will be converted.
     * @return string A string of zeros and ones representing the integer 
     * in binary. The returned number will be represented in 2's complement 
     * representation.
     * @since 1.0
     */
    private static function _intToBinay($val){
        $div = (int)($val / 2);
        $rem = $val % 2;
        if($div == 0){
            return Num::BINARY_DIGITS[$rem];
        }
        else{
            $retVal = Num::_intToBinay($div);
        }
        return $retVal.Num::BINARY_DIGITS[$rem];
    }
    /**
     * A helper function. Used to invert a binary string.
     * @param string $b A string of zeros and ones.
     * @return string A string at which one is turned into zero and 
     * zero turned into one.
     * @since 1.0
     */
    private static function invertBinary($b){
        $len = strlen($b);
        for($x = 0 ; $x < $len ; $x++){
            if($b[$x] == '1'){
                $b[$x] = '0';
            }
            else{
                $b[$x] = '1';
            }
        }
        return $b;
    }
    /**
     * Converts a signed integer to its binary representation.
     * @param int $val The integer that will be converted.
     * @return string A string of zeros and ones representing the integer 
     * in binary. The returned number will be represented in 2's complement 
     * representation.
     * @since 1.0
     */
    public static function intToBinary($val){
        $isNeg = $val < 0 ? TRUE : FALSE;
        if($isNeg){
            $val = $val * -1;
        }
        $binary = Num::_intToBinay($val);
        $padded = Num::padWithZeros($binary, 4 - strlen($binary) % 4);
        if($isNeg){
            $inv = Num::invertBinary($padded);
            return Num::binaryAdd($inv, '1');
        }
        return $padded;
    }
    public static function binaryToInt($binary) {
        
    }
    /**
     * Converts a signed integer to its hexadecimal representation.
     * @param int $val The integer that will be converted.
     * @return string A string that representing the integer 
     * in hexadecimal. The returned number will be represented in 2's complement 
     * representation.
     * @since 1.0
     */
    private static function _intToHex($val){
        $div = (int)($val / 16);
        $rem = $val % 16;
        if($div == 0){
            return Num::HEX_DIGITS[$rem];
        }
        else{
            $retVal = self::_intToHex($div);
        }
        return $retVal.Num::HEX_DIGITS[$rem];
    }
    public static function hexToInt($binary) {
        
    }
    /**
     * Converts a signed integer to its hexadecimal representation.
     * @param int $val The integer that will be converted.
     * @return string A string that representing the integer 
     * in hexadecimal. The returned number will be represented in 2's complement 
     * representation.
     * @since 1.0
     */
    public static function intToHex($val){
        $isNeg = $val < 0 ? TRUE : FALSE;
        $binary = Num::intToBinary($val);
        $retVal = '';
        $len = strlen($binary);
        for($x = 0 ; $x < $len ; $x += 4){
            $bits = substr($binary, $x, 4);
            $hexDigit = Num::binaryToHexDigit($bits);
            if($x == 0 && $isNeg && $hexDigit != 'F'){
                $retVal .= 'F';
            }
            $retVal .= $hexDigit;
        }
        return $retVal;
    }
    private static function binaryToHexDigit($val) {
        $index = 0;
        for($x = 3 ; $x >= 0 ; $x--){
            if($val[$x] == 1){
                if($x == 3){
                    $index += 1;
                }
                else{
                    $index += pow(2, 3 - $x);
                }
            }
        }
        return Num::HEX_DIGITS[$index];
    }
}
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
$start = -254;
$end = 254;
for($x = $start; $x <= $end ; $x++){
    $conv = Num::intToBinary($x);
    $conv2 = Num::intToHex($x);
    echo $x.' as binary = '.$conv.'<br/>';
    echo $x.' as hex = '.$conv2.'<br/>';
}

$b1 = '1111';
$b2 = '1';
echo $b1 .' + '. $b2 .' = '.Num::binaryAdd($b1, $b2).'<br/>';
