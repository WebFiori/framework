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
    public function __construct(string $name = 'arg') {
        if (!$this->setName($name)) {
            $this->name = 'arg';
        }
        $this->isOptional = false;
        $this->allowedVals = [];
        $this->default = '';
        $this->description = '';
    }
    /**
     * Sets a string as default value for the argument.
     * 
     * @param string $default A string that will be set as default value if the
     * argument is not provided in terminal. Note that the value will be trimmed.
     */
    public function setDefault(string $default) {
        $this->default = trim($default);
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
        if (!in_array($trim, $this->getAllowedValues())) {
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
     * If not set, null is returned. Note that if the argument is provided in
     * terminal but its value is not set, the returned value will be empty 
     * string.
     */
    public function getValue() {
        return $this->value;
    }
    /**
     * Sets the value of the argument.
     * 
     * @param string $val The value to set. Note that spaces in the provided value
     * will be trimmed.
     */
    public function setValue(string $val) {
        $allowed = $this->getAllowedValues();
        
        if (count($allowed) == 0 || in_array($val, $allowed)) {
            $this->value = trim($val);
            return true;
        }
        return false;
    }
    /**
     * Sets the name of the argument.
     * 
     * @param string $name A string such as '--config' or similar. It must be
     * non-empty string and have no spaces.
     * 
     * @return boolean If set, the method will return true. False otherwise.
     */
    public function setName(string $name) : bool {
        $trimmed = trim($name);
        if (strlen($trimmed) == 0 || strpos($trimmed, ' ') !== false) {
            return false;
        }
        $this->name = $trimmed;
        return true;
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
