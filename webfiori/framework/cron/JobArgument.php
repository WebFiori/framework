<?php
namespace webfiori\framework\cron;

use webfiori\json\JsonI;
use webfiori\json\Json;
use InvalidArgumentException;
/**
 * A class that represents execution argument of a job.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 * 
 * @since 2.3.1
 */
class JobArgument implements JsonI {
    /**
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $name;
    /**
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $description;
    /**
     * Creates new instance of the class.
     * 
     * @param string $name The name of the argument. It will be considered invalid 
     * if it contains one of the following characters: '=', '&', '#' and '?'.
     * 
     * @param string $desc A string that describes how the argument will affect 
     * job execution. It must be non-empty string in order to be set.
     * 
     * @since 1.0
     */
    public function __construct($name, $desc = '') {
        $this->setName($name);
        if (!$this->setDescription($desc)) {
            $this->setDescription('NO DESCRIPTION');
        }
    }
    /**
     * Returns the value of job argument.
     * 
     * The method will search for the value of the argument in the array $_POST. 
     * Note that the index that will be checked is the name of the argument.
     * 
     * @return string|null If the value of the argument is set, it will be returned 
     * as string. Other than that, null is returned.
     * 
     * @since 1.0
     */
    public function getValue() {
        $name = $this->getName();
        $uName = str_replace(' ', '_', $name);
        $retVal = null;
        $filtered = false;
        
        if (isset($_POST[$name])) {
            $filtered = filter_var(urldecode($_POST[$name]), FILTER_SANITIZE_STRING);
        } else if (isset($_POST[$uName])) {
            $filtered = filter_var(urldecode($_POST[$uName]), FILTER_SANITIZE_STRING);
        }
        
        if ($filtered !== false) {
            $retVal = $filtered;
        }
        
        return $retVal;
    }
    /**
     * Sets a description for the argument.
     * 
     * @param string $desc A string that describes how the argument will affect 
     * job execution. It must be non-empty string in order to be set.
     * 
     * @return boolean If the description is set, the method will return true. 
     * false if not set.
     * 
     * @since 1.0
     */
    public function setDescription($desc) {
        $trimmed = trim($desc);
        
        if (strlen($trimmed) > 0) {
            $this->description = $trimmed;
            return true;
        }
        return false;
    }
    /**
     * Sets the name of the argument.
     * 
     * @param string $name The name of the argument. It will be considered invalid 
     * if it contains one of the following characters: '=', '&', '#' and '?'.
     * 
     * @throws InvalidArgumentException If the name of the argument is invalid.
     */
    public function setName($name) {
        $nTrim = trim($name);
        
        if (!$this->_validateName($nTrim)) {
            if (strlen($nTrim) == 0) {
                throw new InvalidArgumentException('Invalid argument name: <empty string>');
            } else {
                throw new InvalidArgumentException('Invalid argument name: '.$nTrim);
            }
        }
        $this->name = $nTrim;
    }
    /**
     * 
     * @param type $val
     * @return boolean
     */
    private function _validateName($val) {
        $len = strlen($val);

        if ($len > 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $val[$x];

                if ($char == '=' || $char == '&' || $char == '#' || $char == '?') {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Returns the name of the argument.
     * 
     * @return string The name of the argument.
     * 
     * @since 1.0
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Returns argument description.
     * 
     * @return string A string that describes how the argument will affect job 
     * execution. Default return value is 'NO DESCRIPTION'.
     *  
     * @since 1.0
     */
    public function getDescription() {
        return $this->description;
    }
    /**
     * Returns an object that represents the argument in JSON.
     * 
     * @return Json An object that holds the following JSON attributes:
     * <ul>
     * <li>name</li>
     * <li>description</li>
     * </ul>
     * 
     * @since 1.0
     */
    public function toJSON() {
        $json = new Json([
            'name' => $this->getName(),
            'description' => $this->getDescription()
        ]);
        $json->setPropsStyle('snake');
        return $json;
    }

}
