<?php
namespace webfiori\entity\cli;
use webfiori\entity\Util;
use webfiori\entity\AutoLoader;
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
    public function getClassInfo() {
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
        $isFileExist = true;
        do {
            $path = $this->_getClassPath();
            if (file_exists($path.DIRECTORY_SEPARATOR.$className.'.php')) {
                $this->warning('A file which has the same as the class name was found.');
                $isReplace = $this->confirm('Would you like to override the file?', false);
                if ($isReplace) {
                    $isFileExist = false;
                }
            } else {
                $isFileExist = false;
            }
        } while ($isFileExist);
        return [
            'name' => $className,
            'namespace' => $ns,
            'path' => $path
        ];
    }
    public function _createQueryClass() {
        $classInfo = $this->getClassInfo();
        Util::print_r($classInfo);
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
    private function _getClassPath() {
        $validPath = false;
        do {
            $path = $this->getInput("Where would you like to store the class? (must be a directory inside '".ROOT_DIR."')");
            $fixedPath = ROOT_DIR.DIRECTORY_SEPARATOR.trim(trim($path,'/'),'\\');
            if (Util::isDirectory($fixedPath)) {
                if (!in_array($fixedPath, AutoLoader::getFolders())) {
                    $this->error('Provoded directory is not part of autoload directories.');
                } else {
                    $validPath = true;
                }
            } else {
                $this->error('Provided direcory is not a directory or it does not exist.');
            }
            
        } while (!$validPath);
        return $fixedPath;
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
