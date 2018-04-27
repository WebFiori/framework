<?php
/**
 * An interface for any object that can be represented in JSON notation. The class 
 * follows the specifications found at https://www.json.org/index.html.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @since 1.2
 */
class JsonX {
    /**
     * An array of supported types.
     * @var array An array of supported types.
     * @since 1.0
     */
    const TYPES = array(
        'integer','string','double',
        'boolean','array','NULL','object'
    );
    /**
     * An array that contains JSON special characters.
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
     * @var array 
     * @since 1.0
     */
    private $attributes = array();
    /**
     * Adds a new value to the JSON string.
     * @param string $key The value of the key.
     * @param mixed $value The value of the key. It can be an integer, a double, 
     * a string, an array or an object. If <b>NULL</b> is given, the method will 
     * set the value at the given key to null.
     * @return boolean <b>TRUE</b> if the value is set. If the given value or key 
     * is invalid, the method will return <b>FALSE</b>.
     * @since 1.1
     */
    public function add($key, $value){
        if($value !== NULL){
            return $this->addArray($key, $value) ||
            $this->addBoolean($key, $value) ||
            $this->addNumber($key, $value) || 
            $this->addObject($key, $value) ||
            $this->addString($key, $value);
        }
        else{
            if(JsonX::isValidKey($key)){
                $this->attributes[$key] = 'null';
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Adds an object to the JSON string.
     * @param string $key The key value.
     * @param JsonI|Object $val The object that will be added.
     * @return boolean <p>TRUE</b> if the object is added. If the given 
     * value is an object that does not implement the interface <b>JsonI</b>, 
     * The function will try to extract object information based on its public 
     * functions. In that case, the generated JSON will be on the formate <b>{"prop-0":"prop-1","prop-n":"","":""}</b>. 
     * If the key value is invalid string, the method 
     * will return <b>FALSE</b>.
     * @since 1.0
     */
    public function addObject($key, $val){
        if(gettype($val) == 'object'){
            if(is_subclass_of($val, 'JsonI')){
                $this->attributes[$key] = ''.$val->toJSON();
                return TRUE;
            }
            else if($val instanceof JsonX){
                $this->attributes[$key] = $val;
            }
            else{
                $methods = get_class_methods($val);
                $count = count($methods);
                $json = new JsonX();
                set_error_handler(function() {});
                for($x = 0 ; $x < $count; $x++){
                    $propVal = $val->$methods[$x]();
                    if($propVal != NULL){
                        $json->add('prop-'.$x, $propVal);
                    }
                }
                $this->add($key, $json);
                restore_error_handler();
                return TRUE;
            }
        }
        return FALSE;
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
                $retVal .= '"'.$key.'":'.$val.',';
            }
            $index++;
        }
        $retVal .= '}';
        return $retVal;
    }
    /**
     * Adds a number to the JSON data.
     * @param string $key The name of the key.
     * @param int|double $value The value of the key. Note that if the given 
     * number is <b>INF</b> or <b>NAN</b>, The method will add them as a string.
     * @return boolean <b>TRUE</b> in case the number is added. If the given 
     * value is not a number or the key value is invalid string, the method 
     * will return <b>FALSE</b>. 
     * @since 1.0
     */
    public function addNumber($key,$value){
        $val_type = gettype($value);
        if(JsonX::isValidKey($key)){
            if($val_type == 'integer' || $val_type == 'double'){
                if(is_nan($value)){
                    return $this->addString($key, 'NAN');
                }
                else if($value == INF){
                    return $this->addString($key, 'INF');
                }
                $this->attributes[$key] = $value;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Adds a boolean value (true or false) to the JSON data.
     * @param string $key The name of the key.
     * @param boolean $val [Optional] <b>TRUE</b> or <b>FALSE</b>. If not specified, 
     * The default will be <b>TRUE</b>.
     * @return boolean <b>TRUE</b> in case the value is set. If the given 
     * value is not a boolean or the key value is invalid string, the method 
     * will return <b>FALSE</b>.
     * @since 1.0
     */
    public function addBoolean($key,$val=true){
        if(JsonX::isValidKey($key)){
            if(gettype($val) == 'boolean'){
                if($val == TRUE){
                    $this->attributes[$key] = 'true';
                }
                else{
                    $this->attributes[$key] = 'false';
                }
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Adds an array to the JSON.
     * @param string $key The name of the key.
     * @param array $value The array that will be added. If the given array 
     * is indexed array, all values will be added as single entity (e.g. [1, 2, 3]). 
     * If the array is associative, the values of the array will be added as 
     * objects.
     * @return boolean <b>FALSE</b> if the given key is invalid or the given value 
     * is not an array.
     */
    public function addArray($key, $value){
        if(JsonX::isValidKey($key)){
            if(gettype($value) == 'array'){
                $this->attributes[$key] = $this->arrayToJSONString($value);
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * A helper function used to parse arrays.
     * @param array $value
     * @return string A JSON string that represents the array.
     * @since 1.0
     */
    private function arrayToJSONString($value){
        $keys = array_keys($value);
        $keysCount = count($keys);
        $arr = '[';
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
                $arr .= $valueAtKey->toJSON().$comma;
            }
            else{
                if($keyType == 'integer'){
                    if($valueType == 'integer' || $valueType == 'double'){
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
                    else if($valueType == 'string'){
                        $arr .= '"'.JsonX::escapeJSONSpecialChars($valueAtKey).'"'.$comma;
                    }
                    else if($valueType == 'boolean'){
                        if($valueAtKey == TRUE){
                            $arr .= 'true'.$comma;
                        }
                        else{
                            $arr .= 'false'.$comma;
                        }
                    }
                    else if($valueType == 'array'){
                        $arr .= $this->arrayToJSONString($valueAtKey);
                    }
                }
                else{
                    $j = new JsonX();
                    $j->add($keys[$x], $valueAtKey);
                    $arr .= $j.$comma;
                }
            }
        }
        $arr.= ']';
        return $arr;
    }
    /**
     * Checks if the key is a valid key string.
     * @param string $key The key that will be validated.
     * @return boolean <b>TRUE</b> if the key is valid. False otherwise.
     * @since 1.0
     */
    private static function isValidKey($key){
        $key_type = gettype($key);
        return $key_type == 'string';
    }
    /**
     * Adds a new key to the JSON data with its value as string.
     * @param string $key The name of the key.
     * @param string $val The value of the string.
     * @return boolean <b>TRUE</b> in case the string is added. If the given value 
     * is not a string or the given key is invalid, the method will return <b>FALSE</b>.
     * @since 1.0
     */
    public function addString($key, $val){
        if(JsonX::isValidKey($key)){
            if(gettype($val) == 'string'){
                $this->attributes[$key] = '"'. JsonX::escapeJSONSpecialChars($val).'"';
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
    * Escape JSON special characters from string.
    * @param string $string A value of one of JSON object properties. If it is 
    * null,the method will return empty string.
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
