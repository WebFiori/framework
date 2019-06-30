<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, JsonX library.
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
namespace jsonx;
use jsonx\JsonI;
use jsonx\JsonX;
/**
 * An a class that can be used to create well formatted JSON strings. 
 * The class follows the specifications found at https://www.json.org/index.html.
 * @author Ibrahim
 * @since 1.2.1
 */
class JsonX {
    /**
     * An array of supported JOSN data types. 
     * The array has the following strings:
     * <ul>
     * <li>integer</li>
     * <li>string</li>
     * <li>double</li>
     * <li>boolean</li>
     * <li>NULL</li>
     * <li>object</li>
     * </ul>
     * @var array An array of supported JOSN data types.
     * @since 1.0
     */
    const TYPES = array(
        'integer','string','double',
        'boolean','array','NULL','object'
    );
    /**
     * An array that contains JSON special characters.
     * The array contains the following characters:
     * <ul>
     * <li>\</li>
     * <li>/</li>
     * <li>"</li>
     * <li>\t</li>
     * <li>\r</li>
     * <li>\n</li>
     * <li>\f</li>
     * </ul>
     * @var array JSON special characters.
     * @since 1.0
     */
    const SPECIAL_CHARS = array(
        //order of characters maters
        //first we must escape / and \
        '\\',"/",'"',"\t","\r","\n","\f"
    );
    /**
     * An array that contains escaped JSON special characters.
     * @var array escaped JSON special characters.
     * @since 1.0
     */
    const SPECIAL_CHARS_ESC = array(
        "\\\\","\/",'\"',"\\t","\\r","\\n","\\f"
    );
    /**
     * An array that contains JSON data.
     * This array will store the keys as indices and every value will be at 
     * each index.
     * @var array 
     * @since 1.0
     */
    private $attributes = array();
    /**
     * Adds a new value to the JSON string.
     * This method can be used to add an integer, a double, 
     * a string, an array or an object. If null is given, the method will 
     * set the value at the given key to null. If the given value or key is 
     * invalid, the method will not add the value and will return false.
     * @param string $key The value of the key.
     * @param mixed $value The value of the key.
     * @param array $options An associative array of options. Currently, the 
     * array has the following options: 
     * <ul>
     * <li><b>string-as-boolean</b>: A boolean value. If set to true and 
     * the given string is one of the following values, it will be added as 
     * a boolean:
     * <ul>
     * <li>true</li>
     * <li>false</li>
     * <li>t</li>
     * <li>f</li>
     * <li>Yes</li>
     * <li>No</li>
     * <li>On</li>
     * <li>Off</li>
     * <li>Y</li>
     * <li>N</li>
     * <li>Ok</li>
     * </ul> Default is false.</li>
     * <li><b>array-as-object</b>: A boolean value. If set to true, 
     * the array will be added as an object. Default is false.</li>
     * </ul>
     * @return boolean The method will return true if the value is set. 
     * If the given value or key is invalid, the method will return false.
     * @since 1.1
     */
    public function add($key, $value, $options=array(
        'string-as-boolean'=>false,
        'array-as-object'=>false
    )){
        if($value !== null){
            if(isset($options['string-as-boolean'])){
                $strAsbool = $options['string-as-boolean'] === true ? true : false;
            }
            else{
                $strAsbool = false;
            }
            if(isset($options['array-as-object'])){
                $arrAsObj = $options['array-as-object'] === true ? true : false;
            }
            else{
                $arrAsObj = false;
            }
            return $this->addString($key, $value,$strAsbool)||
                    $this->addArray($key, $value, $arrAsObj) ||
            $this->addBoolean($key, $value) ||
            $this->addNumber($key, $value) || 
            $this->addObject($key, $value);
        }
        else{
            $keyValidated = JsonX::_isValidKey($key);
            if($keyValidated !== false){
                $this->attributes[$keyValidated] = 'null';
                return true;
            }
        }
        return false;
    }
    /**
     * Adds an object to the JSON string.
     * The object that will be added can implement the interface JsonI to make 
     * the generated JSON string customizable. Also, the object can be of 
     * type JsonX. If the given value is an object that does not implement the 
     * interface JsonI or it is not of type JsonX, 
     * The method will try to extract object information based on its "getXxxxx()" public 
     * methods. In that case, the generated JSON will be on the formate 
     * <b>{"prop-0":"prop-1","prop-n":"","":""}</b>.
     * @param string $key The key value.
     * @param JsonI|JsonX|Object $val The object that will be added.
     * @return boolean The method will return true if the object is added. 
     * If the key value is invalid string, the method will return false.
     * @since 1.0
     */
    public function addObject($key, $val){
        $keyValidated = self::_isValidKey($key);
        if($keyValidated !== false && gettype($val) == 'object'){
            if(is_subclass_of($val, 'jsonx\JsonI')){
                $this->attributes[$keyValidated] = ''.$val->toJSON();
                return true;
            }
            else if($val instanceof JsonX){
                $this->attributes[$keyValidated] = $val;
            }
            else{
                $methods = get_class_methods($val);
                $count = count($methods);
                $json = new JsonX();
                $propNum = 0;
                set_error_handler(function() {});
                for($x = 0 ; $x < $count; $x++){
                    $funcNm = substr($methods[$x], 0, 3);
                    if(strtolower($funcNm) == 'get'){
                        $propVal = call_user_func(array($val, $methods[$x]));
                        if($propVal !== false && $propVal !== null){
                            $json->add('prop-'.$propNum, $propVal);
                            $propNum++;
                        }
                    }
                }
                $this->add($keyValidated, $json);
                restore_error_handler();
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if the current JsonX instance has the given key or not.
     * @param string $key The value of the key.
     * @return boolean The method will return true if the 
     * key exists. false if not.
     * @since 1.2
     */
    public function hasKey($key) {
        $key = trim($key);
        if(strlen($key) != 0){
            if(isset($this->attributes[$key])){
                return true;
            }
        }
        return false;
    }
    /**
     * Returns a string that represents the value at the given key.
     * @param string $key The value of the key.
     * @return string|null The method will return a string that 
     * represents the value. If the key does not exists,  the method will 
     * return null.
     * @since 1.2
     */
    public function get($key) {
        $key = trim($key);
        if($this->hasKey($key)){
            return $this->attributes[$key];
        }
        return null;
    }
    /**
     * Returns the data on the object as a JSON string.
     * @return string
     */
    public function __toString() {
        $retVal = '{';
        $count = count($this->attributes);
        $index = 0;
        foreach($this->attributes as $key => $val){
            if($index + 1 == $count){
                $retVal .= '"'.$key.'":'.$val;
            }
            else{
                $retVal .= '"'.$key.'":'.$val.', ';
            }
            $index++;
        }
        $retVal .= '}';
        return $retVal;
    }
    /**
     * Adds a number to the JSON data.
     * Note that if the given number is the constant <b>INF</b> or the constant 
     * <b>NAN</b>, The method will add them as a string.
     * @param string $key The name of the key.
     * @param int|double $value The value of the key.
     * @return boolean The method will return true in case the number is 
     * added. If the given value is not a number or the key value is invalid 
     * string, the method 
     * will return false. 
     * @since 1.0
     */
    public function addNumber($key,$value){
        $val_type = gettype($value);
        $keyValidated = self::_isValidKey($key);
        if($keyValidated !== false){
            if($val_type == 'integer' || $val_type == 'double'){
                if(is_nan($value)){
                    return $this->addString($keyValidated, 'NAN');
                }
                else if($value == INF){
                    return $this->addString($keyValidated, 'INF');
                }
                $this->attributes[$keyValidated] = $value;
                return true;
            }
        }
        return false;
    }
    private function _stringAsBoolean($str){
        $lower = strtolower($str);
        $boolTypes = array(
            't'=>true,
            'f'=>false,
            'yes'=>true,
            'no'=>false,
            'true'=>true,
            'false'=>false,
            'on'=>true,
            'off'=>false,
            'y'=>true,
            'n'=>false,
            'ok'=>true
        );
        if(isset($boolTypes[$lower])){
            return $boolTypes[$lower];
        }
        return 'INV';
    }
    /**
     * Adds a boolean value (true or false) to the JSON data.
     * @param string $key The name of the key.
     * @param boolean $val true or false. If not specified, 
     * The default will be true.
     * @return boolean The method will return true in case the value is set. 
     * If the given value is not a boolean or the key value is invalid string, 
     * the method will return false.
     * @since 1.0
     */
    public function addBoolean($key,$val=true){
        $keyValidated = JsonX::_isValidKey($key);
        if($keyValidated !== false){
            if(gettype($val) == 'boolean'){
                if($val == true){
                    $this->attributes[$keyValidated] = 'true';
                }
                else{
                    $this->attributes[$keyValidated] = 'false';
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Adds an array to the JSON.
     * @param string $key The name of the key.
     * @param array $value The array that will be added.
     * @param boolean $asObject If this parameter is set to true, 
     * the array will be added as an object in JSON string. Default is true.
     * @return boolean The method will return false if the given key is invalid 
     * or the given value is not an array.
     */
    public function addArray($key, $value,$asObject=true){
        $keyValidated = JsonX::_isValidKey($key);
        if($keyValidated !== false){
            if(gettype($value) == 'array'){
                $this->attributes[$keyValidated] = $this->_arrayToJSONString($value,$asObject);
                return true;
            }
        }
        return false;
    }
    /**
     * A helper method used to parse arrays.
     * @param array $value
     * @return string A JSON string that represents the array.
     * @since 1.0
     */
    private function _arrayToJSONString($value,$asObject=false){
        $keys = array_keys($value);
        $keysCount = count($keys);
        if($asObject === true){
            $arr = '{';
        }
        else{
            $arr = '[';
        }
        for($x = 0 ; $x < $keysCount ; $x++){
            if($x + 1 == $keysCount){
                $comma = '';
            }
            else{
                $comma = ', ';
            }
            $valueAtKey = $value[$keys[$x]];
            $keyType = gettype($keys[$x]);
            $valueType = gettype($valueAtKey);
            //echo '$x = '.$x.'<br/>';
            //echo '$keys[$x] = '.$keys[$x].'<br/>';
            //echo '$valueAtKey = '.$valueAtKey.'</br>';
            //echo '$keyType = '.$keyType.'<br/>';
            //echo '$valueType = '.$valueType.'<br/><br/>';
            if($valueAtKey instanceof JsonI){
                if($asObject === true){
                    $arr .= '"'.$keys[$x].'":'.$valueAtKey->toJSON().$comma;
                }
                else{
                    $arr .= $valueAtKey->toJSON().$comma;
                }
            }
            else if($valueAtKey instanceof JsonX){
                if($asObject === true){
                    $arr .= '"'.$keys[$x].'":'.$valueAtKey.$comma;
                }
                else{
                    $arr .= $valueAtKey.$comma;
                }
            }
            else{
                if($keyType == 'integer'){
                    if($valueType == 'integer' || $valueType == 'double'){
                        if($asObject === true){
                            if(is_nan($valueAtKey)){
                                $arr .= '"'.$keys[$x].'":"NAN"'.$comma;
                            }
                            else if($valueAtKey == INF){
                                $arr .= '"'.$keys[$x].'":"INF"'.$comma;
                            }
                            else{
                                $arr .= '"'.$keys[$x].'":'.$valueAtKey.$comma;
                            }
                        }
                        else{
                            if(is_nan($valueAtKey)){
                                $arr .= '"NAN"'.$comma;
                            }
                            else if($valueAtKey == INF){
                                $arr .= '"INF"'.$comma;
                            }
                            else{
                                $arr .= $valueAtKey.$comma;
                            }
                        }
                    }
                    else if($valueType == 'string'){
                        if($asObject === true){
                            $asBool = $this->_stringAsBoolean($valueAtKey);
                            if($asBool === true || $asBool === false){
                                $toAdd = $asBool === true ? 'true'.$comma : 'false'.$comma;
                                $arr .= '"'.$keys[$x].'":'.$toAdd;
                            }
                            else{
                                $arr .= '"'.$keys[$x].'":"'.JsonX::escapeJSONSpecialChars($valueAtKey).'"'.$comma;
                            }
                        }
                        else{
                            $asBool = $this->_stringAsBoolean($valueAtKey);
                            if($asBool === true || $asBool === false){
                                $toAdd = $asBool === true ? 'true'.$comma : 'false'.$comma;
                                $arr .= $toAdd;
                            }
                            else{
                                $arr .= '"'.JsonX::escapeJSONSpecialChars($valueAtKey).'"'.$comma;
                            }
                        }
                    }
                    else if($valueType == 'boolean'){
                        if($asObject === true){
                            if($valueAtKey == true){
                                $arr .= '"'.$keys[$x].'":true'.$comma;
                            }
                            else{
                                $arr .= '"'.$keys[$x].'":false'.$comma;
                            }
                        }
                        else{
                            if($valueAtKey == true){
                                $arr .= 'true'.$comma;
                            }
                            else{
                                $arr .= 'false'.$comma;
                            }
                        }
                    }
                    else if($valueType == 'array'){
                        if($asObject === true){
                            $arr .= '"'.$keys[$x].'":'.$this->_arrayToJSONString($valueAtKey,$asObject).$comma;
                        }
                        else{
                            $arr .= $this->_arrayToJSONString($valueAtKey,$asObject).$comma;
                        }
                    }
                    else if($valueType == 'NULL'){
                        if($asObject === true){
                            $arr .= '"'.$keys[$x].'":'.'null'.$comma;
                        }
                        else{
                            $arr .= 'null'.$comma;
                        }
                    }
                    else if($valueType == 'object'){
                        if($asObject === true){
                            if($valueAtKey instanceof JsonX){
                                $arr .= '"'.$keys[$x].'":'.$valueAtKey.$comma;
                            }
                            else{
                                $methods = get_class_methods($valueAtKey);
                                $count = count($methods);
                                $json = new JsonX();
                                $propNum = 0;
                                set_error_handler(function() {});
                                for($y = 0 ; $y < $count; $y++){
                                    $funcNm = substr($methods[$y], 0, 3);
                                    if(strtolower($funcNm) == 'get'){
                                        $propVal = call_user_func(array($valueAtKey, $methods[$y]));
                                        if($propVal !== false && $propVal !== null){
                                            $json->add('prop-'.$propNum, $propVal);
                                            $propNum++;
                                        }
                                    }
                                }
                                $arr .= '"'.$keys[$x].'":'.$json.$comma;
                            }
                        }
                        else{
                            if($valueAtKey instanceof JsonX){
                                $arr .= $valueAtKey.$comma;
                            }
                            else{
                                $methods = get_class_methods($valueAtKey);
                                $count = count($methods);
                                $json = new JsonX();
                                $propNum = 0;
                                set_error_handler(function() {});
                                for($y = 0 ; $y < $count; $y++){
                                    $funcNm = substr($methods[$y], 0, 3);
                                    if(strtolower($funcNm) == 'get'){
                                        $propVal = call_user_func(array($valueAtKey, $methods[$y]));
                                        if($propVal !== false && $propVal !== null){
                                            $json->add('prop-'.$propNum, $propVal);
                                            $propNum++;
                                        }
                                    }
                                }
                                $arr .= $json.$comma;
                            }
                        }
                    }
                }
                else{
                    if($asObject === true){
                        $arr .= '"'.$keys[$x].'":';
                        $type = gettype($valueAtKey);
                        if($type == 'string'){
                            $asBool = $this->_stringAsBoolean($valueAtKey);
                            if($asBool === true || $asBool === false){
                                $result = $asBool === true ? 'true'.$comma : 'false'.$comma;
                                $arr .= $result;
                            }
                            else{
                                $arr .= '"'.self::escapeJSONSpecialChars($valueAtKey).'"'.$comma;
                            }
                        }
                        else if($type == 'integer' || $type == 'double'){
                            $arr .= $valueAtKey.$comma;
                        }
                        else if($type == 'boolean'){
                            $arr .= $valueAtKey === true ? 'true'.$comma : 'false'.$comma;
                        }
                        else if($type == 'NULL'){
                            $arr .= 'null'.$comma;
                        }
                        else if($type == 'array'){
                            $result = $this->_arrayToJSONString($valueAtKey, $asObject);
                            $arr .= $result.$comma;
                        }
                        else if($type == 'object'){
                            if($valueAtKey instanceof JsonX){
                                $arr .= $valueAtKey.$comma;
                            }
                            else{
                                $methods = get_class_methods($valueAtKey);
                                $count = count($methods);
                                $json = new JsonX();
                                $propNum = 0;
                                set_error_handler(function() {});
                                for($y = 0 ; $y < $count; $y++){
                                    $funcNm = substr($methods[$y], 0, 3);
                                    if(strtolower($funcNm) == 'get'){
                                        $propVal = call_user_func(array($valueAtKey, $methods[$y]));
                                        if($propVal !== false && $propVal !== null){
                                            $json->add('prop-'.$propNum, $propVal);
                                            $propNum++;
                                        }
                                    }
                                }
                                $arr .= $json.$comma;
                            }
                        }
                        else{
                            $arr .= 'null'.$comma;
                        }
                    }
                    else{
                        $j = new JsonX();
                        $j->add($keys[$x], $valueAtKey);
                        $arr .= $j.$comma;
                    }
                }
            }
        }
        if($asObject === true){
            $arr.= '}';
        }
        else{
            $arr.= ']';
        }
        return $arr;
    }
    /**
     * Checks if the key is a valid key string.
     * The key is invalid if its an empty string.
     * @param string $key The key that will be validated.
     * @return boolean|string If the key is valid, it will be returned 
     * after trimmed. If not valid, false is returned.
     * @since 1.0
     */
    private static function _isValidKey($key){
        $trimmedKey = trim($key);
        if(strlen($trimmedKey) != 0){
            return $trimmedKey;
        }
        return false;
    }
    /**
     * Adds a new key to the JSON data with its value as string.
     * @param string $key The name of the key. Must be non empty string.
     * @param string $val The value of the string. Note that if the given string 
     * is one of the following and the parameter <b>$toBool</b> is set to true, 
     * it will be converted into boolean (case insensitive).
     * <ul>
     * <li>yes => <b>true</b></li>
     * <li>no => <b>false</b></li>
     * <li>y => <b>true</b></li>
     * <li>n => <b>false</b></li>
     * <li>t => <b>true</b></li>
     * <li>f => <b>false</b></li>
     * <li>true => <b>true</b></li>
     * <li>false => <b>false</b></li>
     * <li>on => <b>true</b></li>
     * <li>off => <b>false</b></li>
     * <li>ok => <b>true</b></li>
     * </ul>
     * @param boolean $toBool If set to true and the string represents a boolean 
     * value, then the string will be added as a boolean. Default is false.
     * @return boolean The method will return true in case the string is added. 
     * If the given value is not a string or the given key is invalid or the 
     * parameter <b>$toBool</b> is set to true and given string is not a boolean, the 
     * method will return false.
     * @since 1.0
     */
    public function addString($key, $val,$toBool=false){
        $keyValidated = JsonX::_isValidKey($key);
        if($keyValidated !== false){
            if(gettype($val) == 'string'){
                if($toBool === true){
                    $boolVal = $this->_stringAsBoolean($val);
                    if($boolVal === true || $boolVal === false){
                        return $this->addBoolean($keyValidated, $boolVal);
                    }
                }
                else{
                    $this->attributes[$keyValidated] = '"'. JsonX::escapeJSONSpecialChars($val).'"';
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Creates and returns a well formatted JSON string that will be created using 
     * provided data.
     * @return string A well formatted JSON string that will be created using 
     * provided data.
     * 
     */
    public function toJSONString() {
        return $this.'';
    }
    /**
    * Escape JSON special characters from string.
    * If the given string is null,the method will return empty string.
    * @param string $string A value of one of JSON object properties. 
    * @return string An escaped version of the string.
    * @since 1.0
    */
   public static function escapeJSONSpecialChars($string){
       $escapedJson = '';
       $string = ''.$string;
       if($string){
           $count = count(JsonX::SPECIAL_CHARS);
           for($i = 0 ; $i < $count ; $i++){
               if($i == 0){
                   $escapedJson = str_replace(JsonX::SPECIAL_CHARS[$i], JsonX::SPECIAL_CHARS_ESC[$i], $string);
               }
               else{
                   $escapedJson = str_replace(JsonX::SPECIAL_CHARS[$i], JsonX::SPECIAL_CHARS_ESC[$i], $escapedJson);
               }
           }
       }
       return $escapedJson;
   }
}
