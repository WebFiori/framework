<?php
namespace webfiori\framework\cli;

use webfiori\framework\cli\OutputStream;
/**
 * A stream that uses array as its source of output.
 * 
 * This stream is mainly used when the developer would like to test his commands
 * using the class 'CommandRunner'.
 *
 * @author Ibrahim
 */
class ArrayOutputStream implements OutputStream {
    private $outputArr;
    public function __construct() {
        $this->outputArr = [];
    }
    /**
     * Sends a line as output to the array.
     * 
     * @param string $str The string that represents the output.
     * 
     * @param array $_ Any extra formatting options.
     */
    public function println($str, ...$_) {
        $toPass = [
            $this->asString($str)."\n"
        ];

        foreach ($_ as $val) {
            $toPass[] = $val;
        }
        call_user_func_array([$this, 'prints'], $toPass);
    }
    /**
     * Sends a string to the stream.
     * 
     * This method is similar to php's 'prints' function.
     * 
     * @param string $str The string that will be printed.
     * 
     * @param type $_ Any extra parameters that the string needs.
     */
    public function prints($str, ...$_) {
        $arrayToPass = [
            $str
        ];

        foreach ($_ as $val) {
            $type = gettype($val);

            if ($type != 'array') {
                $arrayToPass[] = $val;
            }
        }
        
        $this->outputArr[] = call_user_func_array('sprintf', $arrayToPass);
    }
    /**
     * Returns the array that holds all output values.
     * 
     * @return array The array will have the output with selected formatting
     * options.
     */
    public function getOutputArray() {
        return $this->outputArr;
    }
    /**
     * Removes all stored output.
     */
    public function reset() {
        $this->outputArr = [];
    }
    private function asString($var) {
        $type = gettype($var);

        if ($type == 'boolean') {
            return $var === true ? 'true' : 'false';
        } else if ($type == 'null') {
            return 'null';
        }

        return $var;
    }
}
