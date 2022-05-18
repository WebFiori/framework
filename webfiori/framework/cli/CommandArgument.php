<?php
namespace webfiori\framework\cli;

/**
 * A class which is used to represent command line argument.
 *
 * @author Ibrahim
 */
class CommandArgument {
    private $isOptional;
    private $default;
    private $description;
    private $allowedVals;
    private $value;
    private $name;
    public function __construct() {
        $this->name = 'arg';
        $this->isOptional = false;
        $this->allowedVals = [];
        $this->default = '';
        $this->description = '';
    }
    /**
     * Returns an array that contains all allowed argument values.
     * 
     * @return array An array that contains all allowed argument values.
     */
    public function getAllowedValues() : array {
        return $this->allowedVals;
    }
    /**
     * Adds a value to the set of allowed argument values.
     * 
     * @param string $val A string that represents the value.
     */
    public function addAllowedValue(string $val) {
        $trim = trim($val);
        if (!in_array($this->allowedVals, $trim)) {
            $this->allowedVals[] = $trim;
        }
    }
    /**
     * Checks if the argument is optional or not.
     * 
     * @return bool If the argument is set as optional, the method will return
     * true. False if not optional. Default is false.
     */
    public function isOptional() : bool {
        return $this->isOptional;
    }
    /**
     * Make the argument as optional argument or mandatory.
     * 
     * @param bool $optional True to make it optional. False to make it mandatory.
     */
    public function setIsOptional(bool $optional) {
        $this->isOptional = $optional;
    }
    /**
     * Returns the default value of the argument.
     * 
     * @return string The default value of the argument. Default return value is
     * empty string.
     */
    public function getDefault() : string {
        return $this->default;
    }
    /**
     * Sets the description of the argument.
     * 
     * The value is used by the command 'help' to show argument help.
     * 
     * @param string $desc A string that represents the description of the argument.
     */
    public function setDescription(string $desc) {
        $this->description = trim($desc);
    }
    /**
     * Returns a string that represents the description of the argument.
     * 
     * The value is used by the command 'help' to show argument help.
     * 
     * @return string A string that represents the description of the argument.
     * Default is empty string.
     */
    public function getDescription() : string {
        return $this->description;
    }
    /**
     * Returns the value of the argument as provided in the terminal.
     * 
     * @return string|null If set, the method will return its value as string.
     * If not set, null is returned.
     */
    public function getValue() {
        return $this->value;
    }
    /**
     * Sets the value of the argument.
     * 
     * @param string $val The value to set.
     */
    public function setValue(string $val) {
        $allowed = $this->getAllowedValues();
        
        if (count($allowed) == 0 
                || in_array($allowed, $val) 
                || ($val == $this->getDefault() && $this->getDefault() != '')) {
            $this->value = $val;
            return true;
        }
        return false;
    }
    /**
     * Sets the name of the argument.
     * 
     * @param string $name A string such as '--config' or similar.
     * 
     * @return boolean If set, the method will return true. False otherwise.
     */
    public function setName(string $name) : bool {
        $trimmed = trim($name);
        if (strlen($trimmed) == 0) {
            return false;
        }
        $this->name = $trimmed;
    }
    /**
     * Returns the name of the argument.
     * 
     * 
     * @return string The name of the argument. Default return value is 'arg'.
     */
    public function getName() : string {
        return $this->name;
    }
}
