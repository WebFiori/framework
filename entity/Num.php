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
     * A constant that indicates a binary number is invalid.
     * @since 1.0
     */
    const INV_BINARY = 'invalid_binary_number';
    /**
     * A constant that indicates a hexadecimal number is invalid.
     * @since 1.0
     */
    const INV_HEX = 'invalid_hexadecimal_number';
    /**
     * A constant that indicates an integer number is invalid.
     * @since 1.0
     */
    const INV_DECIMAL = 'invalid_decimal_number';
    /**
     * An array that contains integer values as keys along binary representation 
     * as value.
     * @since 1.0
     */
    const BINARY_DIGITS = array(
        0=>'0',1=>'1'
    );
    /**
     * A function that is used to extend a binary number with extra zeros or ones.
     * @param string $binary The string representation of the binary number.
     * @param int $count The number of zeros or ones that will be added to 
     * the binary number.
     * @param string $val The value that will be used to extend the number. 
     * It can be '0' or '1'. 
     * In case of invalid value or no value given, default is '0'.
     * @param boolean $left A boolean to indicate where the zeros or ones will 
     * be added. <b>TRUE</b> to add the numbers to the left and <b>FALSE</b> 
     * to add numbers to the right. Default is <b>TRUE</b>.
     * @return string The new extended binary number. If the given binary 
     * string is invalid, the function will return <b>Num::INV_BINARY</b>.
     * @since 1.0
     */
    public static function extendBinary($binary,$count,$val='0',$left=true){
        if(Num::isBinary($binary)){
            if($val != '0' && $val != '1'){
                $val = '0';
            }
            if($left === TRUE){
                for($x = 0 ; $x < $count ; $x++){
                    $binary = $val.$binary;
                }
            }
            else{
                for($x = 0 ; $x < $count ; $x++){
                    $binary = $binary.$val;
                }
            }
            return $binary;
        }
        return Num::INV_BINARY;
    }
    
    public static function hexSub($hex1,$hex2){
        
    }
    public static function binarySub($binary1,$binary2) {
        if(Num::isBinary($binary1)){
            if(Num::isBinary($binary2)){
                $len1 = strlen($binary1);
                $len2 = strlen($binary2);
                if($len1 > $len2){
                    $binary2 = Num::extendBinary($binary2, $len1 - $len2, $binary2[0]);
                    $len2 = strlen($binary2);
                }
                else if($len1 < $len2){
                    $binary1 = Num::extendBinary($binary1, $len2 - $len1, $binary1[0]);
                    $len1 = strlen($binary1);
                }
                if($binary1[0] == '0'){
                    //num - num or 
                    //num - (-num) = num + num
                    $b2Inv = Num::invertBinary($binary2);
                    $binary2 = Num::binaryAdd($b2Inv, '01');
                    $result = Num::binaryAdd($binary1, $binary2);
                    if($result[0] == 1){
                        return substr($result, 1);
                    }
                    return $result;
                }
                else{
                    return Num::binaryAdd($binary1, $binary2);
                }
            }
        }
    }
    /**
     * Adds two binary numbers.
     * @param string $binary1 The first binary number as string of zeros and ones 
     * represented in two's complement.
     * @param string $binary2 The second binary number as string of zeros and ones 
     * represented in two's complement.
     * @return string The result of adding the two numbers. If one or both 
     * binary numbers are invalid, the function will return <b>Num::INV_BINARY</b>.
     * @since 1.0
     */
    public static function binaryAdd($binary1,$binary2) {
        if(Num::isBinary($binary1)){
            if(Num::isBinary($binary2)){
                $len1 = strlen($binary1);
                $len2 = strlen($binary2);
                if($len1 > $len2){
                    $binary2 = Num::extendBinary($binary2, $len1 - $len2, $binary2[0]);
                    $len2 = strlen($binary2);
                }
                else if($len1 < $len2){
                    $binary1 = Num::extendBinary($binary1, $len2 - $len1, $binary1[0]);
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
//                if($carry != 0){
//                    $result = '1'.$result;
//                }
//                if($binary1[0] == '0' && $binary2[0] == '0' && $result[0] == '1'){
//                    $result = '0'.$result;
//                }
                return $result;
            }
        }
        return Num::INV_BINARY;
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
     * representation. If the given parameter is not an integer, the function will 
     * return <b>Num::INV_DECIMAL</b>.
     * @since 1.0
     */
    public static function intToBinary($val){
        if(gettype($val) == 'integer'){
            $isNeg = $val < 0 ? TRUE : FALSE;
            if($isNeg){
                $val = $val * -1;
            }
            $binary = Num::_intToBinay($val);
            if($isNeg){
                $padded = Num::extendBinary($binary, 4 - strlen($binary) % 4, '0');
                $inv = Num::invertBinary($padded);
                return Num::binaryAdd($inv, '01');
            }
            else{
                $padded = Num::extendBinary($binary, 4 - strlen($binary) % 4, '0');
                return $padded;
            }
        }
        return Num::INV_DECIMAL;
    }
    /**
     * Converts a binary string to its hexadecimal representation. 
     * @param string $binary A string of binary digits.
     * @param boolean $zeroExt [Optional] If the number of digits in the given binary string 
     * is not a multiple of 4 and this attribute is set to <b>TRUE</b>, an additional 
     * zeros will be added to the left of the string till its length is multiple of 4. 
     * If the attribute is set to <b>FALSE</b>, an additional 
     * ones will be added to the left of the string till its length is multiple of 4. 
     * Default is <b>TRUE</b>.
     * @return string The hexadecimal representation of the given binary string.
     * @since 1.0
     */
    public static function binaryToHex($binary,$zeroExt=true) {
        if(Num::isBinary($binary)){
            $len = strlen($binary);
            if($len % 4 != 0){
                if($zeroExt == TRUE){
                    $binary = Num::extendBinary($binary, 4 - $len % 4, '0');
                }
                else{
                    $binary = Num::extendBinary($binary, 4 - $len % 4, '1');
                }
            }
            $loopLen = strlen($binary) / 4;
            $retVal = '';
            for($x = 0 ; $x < $loopLen ; $x++){
                $retVal .= Num::binaryToHexDigit(substr($binary, $x * 4, 4));
            }
            return $retVal;
        }
        return Num::INV_BINARY;
    }
    /**
     * Converts a binary string to its signed integer value.
     * @param string $binary A binary string (such as '0010101').
     * @return int|string If the given string is a valid binary number, the 
     * integer value of the binary number is returned. If the given binary 
     * string is invalid binary number, the function will return <b>Num::INV_BINARY</b>.
     * @since 1.0
     */
    public static function binaryToInt($binary) {
        if(Num::isBinary($binary)){
            $len = strlen($binary);
            if($len % 4 != 0){
                $binary = Num::extendBinary($binary, 3 - $len % 4, $binary[0]);
            }
            $isNeg = $binary[0] == '1' ? TRUE : FALSE;
            if($isNeg){
                $inv = Num::invertBinary($binary);
                $binary = Num::binaryAdd($inv, '01');
            }
            $newLen = strlen($binary);
            $retVal = 0;
            for($x = 0 ; $x < $newLen ; $x++){
                if($binary[$x] == 1){
                    $retVal += pow(2,$newLen - $x - 1);
                }
            }
            if($isNeg === TRUE){
                return $retVal * -1;
            }
            return $retVal;
        }
        return Num::INV_BINARY;
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
    /**
     * Returns the binary representation of a hexadecimal number as string.
     * @param string $hex A number written in hexadecimal (such as 'FA2E3B').
     * @return string The binary representation of the given hexadecimal number. 
     * if the given number is invalid, the function will return <b>Num::INV_HEX</b>.
     * @since 1.0
     */
    public static function hexToBinary($hex) {
        if(Num::isHex($hex)){
            $retVal = '';
            $len = strlen($hex);
            for($x = 0 ; $x < $len ; $x++){
                $retVal .= Num::hexToBinaryDigits(strtoupper($hex[$x]));
            }
            return $retVal;
        }
        return Num::INV_HEX;
    }
    /**
     * Returns the signed integer value of a hexadecimal number.
     * @param string $hex A number written in hexadecimal (such as 'FA2E3B').
     * @return int|string The signed integer value of a hexadecimal number. 
     * if the given number is invalid, the function will return <b>Num::INV_HEX</b>.
     * @since 1.0
     */
    public static function hexToInt($hex) {
        $binary = Num::hexToBinary($hex);
        $result = $binary == Num::INV_HEX ? $binary : Num::binaryToInt($binary);
        return $result;
    }
    /**
     * Converts a signed integer to its hexadecimal representation.
     * @param int $val The integer that will be converted.
     * @return string A string that representing the integer 
     * in hexadecimal. The returned number will be represented in 2's complement 
     * representation. If the given parameter is not an integer, the function will 
     * return <b>Num::INV_DECIMAL</b>.
     * @since 1.0
     */
    public static function intToHex($val){
        if(gettype($val) == 'integer'){
            $isNeg = $val < 0 ? TRUE : FALSE;
            $binary = Num::intToBinary($val);
            $len = strlen($binary);
            if($len % 4 != 0){
                $binary = Num::extendBinary($binary, 4 - $len % 4, $binary[0]);
            }
            $retVal = '';
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
        return Num::INV_DECIMAL;
    }
    /**
     * Converts a hexadecimal digit to its binary representation.
     * @param string $hexDigit The hexadecimal digit.
     * @return string A string of 4 binary digits that represents the hexadecimal digit.
     * @since 1.0
     */
    private static function hexToBinaryDigits($hexDigit){
        $count = count(Num::HEX_DIGITS);
        for($x = 0 ; $x < $count ; $x++){
            if(Num::HEX_DIGITS[$x] == $hexDigit){
                $asB = Num::intToBinary($x);
                if(strlen($asB) == 8){
                    return substr($asB, 4);
                }
                return $asB;
            }
        }
    }
    /**
     * Converts a string of 4 binary digits to its hex digit representation.
     * @param string $val A string of 4 binary digits.
     * @return string A single digit that represents the 4 binary digits.
     * @since 1.0
     */
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
$start = -1;
$end = 0;
for($x = $start; $x <= $end ; $x++){
    //test2($x,1);
}
test2(-1, -1);
test2(-1, 0);
test2(0, -1);
test2(0, 0);
test2(1, 0);
test2(0, 1);
test2(1, 1);
function test2($num1,$num2){
    echo '------Num1 = '.$num1.' Num2 = '.$num2.'--------<br/>';
    $num1AsBinary = Num::intToBinary($num1);
    echo 'Num::intToBinary('.$num1.') = '.$num1AsBinary.'<br/>';
    $num2AsBinary = Num::intToBinary($num2);
    echo 'Num::intToBinary('.$num2.') = '.$num2AsBinary.'<br/>';
    $num1BplusNum2B = Num::binaryAdd($num1AsBinary, $num2AsBinary);
    echo 'Num::binaryAdd('.$num1AsBinary.','.$num2AsBinary.') = '.$num1BplusNum2B.'<br/>';
    $bResultAsInt = Num::binaryToInt($num1BplusNum2B);
    echo 'Num::binaryToInt('.$num1BplusNum2B.') = '.$bResultAsInt.'<br/>';
    
    $num1BsubNum2B = Num::binarySub($num1AsBinary, $num2AsBinary);
    echo 'Num::binarySub('.$num1AsBinary.','.$num2AsBinary.') = '.$num1BsubNum2B.'<br/>';
    $bSubResultAsInt = Num::binaryToInt($num1BsubNum2B);
    echo 'Num::binaryToInt('.$num1BsubNum2B.') = '.$bSubResultAsInt.'<br/><br/>';
}
function test($num){
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
