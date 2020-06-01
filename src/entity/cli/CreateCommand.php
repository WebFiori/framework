<?php
namespace webfiori\entity\cli;

/**
 * A command which is used to automate some of the common tasks such as 
 * creating query classes or controllers.
 *
 * @author Ibrahim
 * @version 1.0
 */
class CreateCommand extends CLICommand {
    
    public function __construct() {
        parent::__construct('create', [], 'Creates a query class, entity, API or a controller');
    }
    public function exec(): int {
        $options = [
            'Query class.',
            'Quit.'
        ];
        $answer = $this->select('What would you like to create?', $options, count($options) - 1);
        if ($answer == 'Nothing.') {
            return 0;
        } else if ($answer == 'Query class.') {
            return $this->_createQueryClass();
        }
        
    }
    public function _createQueryClass() {
        $classExist = true;
        do {
            $className = $this->_getClassName();
            $ns = $this->_getNamespace();
            $classWithNs = $ns.'\\'.$className;
            $classExist = class_exists($classWithNs);
            if ($classExist) {
                $this->error('A class in the given namespace which has the given name was found.');
            }
        } while ($classExist);
        
        return 0;
    }
    private function _getNamespace() {
        $isNameValid = false;
        do {
            $ns = str_replace('/','\\',trim($this->getInput('Enter an optional namespace for the class:', '\\')));
            $isNameValid = $this->_validateNamespace($ns);
            if (!$isNameValid) {
                $this->error('Invalid namespace is given.');
            }
        } while (!$isNameValid);
        return trim($ns,'\\');
    }
    private function _validateNamespace($ns) {
        if ($ns == '\\') {
            return true;
        }
        $split = explode('\\', $ns);
        foreach ($split as $subNs) {
            $len = strlen($subNs);
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $subNs[$x];
                if ($x == 0 && (($char >= '0' && $char <= '9'))) {
                    return false;
                }
                if (!(($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9') || $char == '_')) {
                    return false;
                }
            }
        }
        return true;
    }
    private function _getClassName() {
        $isNameValid = false;
        do {
            $className = trim($this->getInput('Enter a name for the new class:'));
            $isNameValid = $this->_validateClassName($className);
            if (!$isNameValid) {
                $this->error('Invalid class name is given.');
            }
        } while (!$isNameValid);
        return $className;
    }
    private function _validateClassName($name) {
        $len = strlen($name);
        if ($len > 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $name[$x];
                if ($x == 0 && (($char >= '0' && $char <= '9'))) {
                    return false;
                }
                if (!(($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9') || $char == '_')) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
