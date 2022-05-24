<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\CLICommand;
use webfiori\framework\Util;
/**
 * A class which is used to read class information as prompt from any input stream
 * which is set by the class 'Runner'.
 *
 * @author Ibrahim
 */
class ClassInfoReader {
    /**
     * 
     * @var CLICommand
     */
    private $ownerCommand;
    /**
     * Creates new instance of the class.
     * 
     * @param CLICommand $owner The command that owns the reader. Its used to read
     * user input and send output.
     */
    public function __construct(CLICommand $owner) {
        $this->ownerCommand = $owner;
    }
    /**
     * Returns the command that owns the instance.
     * 
     * @return CLICommand
     */
    public function getOwner() {
        return $this->ownerCommand;
    }
    /**
     * Reads and returns a string that represents the location at which the class will be
     * created at.
     * 
     * @param string $default A default value for the path.
     * 
     * @return string A string that represents the location at which the class will be
     * created at.
     */
    public function getPath($default) {
        $validPath = false;

        do {
            clearstatcache();
            $path = $this->getOwner()->getInput("Where would you like to store the class? (must be a directory inside '".ROOT_DIR."')", $default);
            $fixedPath = ROOT_DIR.DS.trim(trim(str_replace('\\', DS, str_replace('/', DS, $path)),'/'),'\\');

            if (Util::isDirectory($fixedPath, true)) {
                $validPath = true;
            } else {
                $this->getOwner()->error('Provided direcory is not a directory or it does not exist.');
            }
        } while (!$validPath);

        return $fixedPath;
    }
    /**
     * Prompts the user to enter class information including name, namespace and path.
     * 
     * @param string $defaultNs An optional default namespace to use in case the
     * user did not provide a one. Note that this also will be the default path.
     * 
     * @param string $suffix An optional string which will be appended to the
     * name of the class.
     * 
     * @return array The method will return an array that contains 3 indices: 
     * <ul>
     * <li><b>name</b>: The name of the class.</li>
     * <li><b>namespace</b>: The namespace of the class. It will be empty string if no 
     * namespace is entered.</li>
     * <li><b>path</b>: The location at which the class will be created.</li>
     * </ul>
     */
    public function readClassInfo($defaultNs = null, $suffix = null) {
        $classExist = true;

        do {
            $className = $this->getName($suffix);
            $ns = $this->getNamespace($defaultNs);
            $classWithNs = $ns.'\\'.$className;
            $classExist = class_exists($classWithNs);

            if ($classExist) {
                $this->getOwner()->error('A class in the given namespace which has the given name was found.');
            }
        } while ($classExist);
        $isFileExist = true;

        do {
            $path = $this->getPath($ns);

            if (file_exists($path.DS.$className.'.php')) {
                $this->getOwner()->warning('A file which has the same as the class name was found.');
                $isReplace = $this->getOwner()->confirm('Would you like to override the file?', false);

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
    /**
     * Reads and returns a string that represents the namespace at which the class
     * will be added to.
     * 
     * @param string $defaultNs A default value for the namespace.
     * 
     * @return string A string that represents the namespace at which the class
     * will be added to.
     */
    public function getNamespace($defaultNs) {
        $isNameValid = false;

        do {
            $ns = str_replace('/','\\',trim($this->getOwner()->getInput('Enter an optional namespace for the class:', $defaultNs)));
            $isNameValid = $this->_validateNamespace($ns);

            if (!$isNameValid) {
                $this->getOwner()->error('Invalid namespace is given.');
            }
        } while (!$isNameValid);

        return trim($ns,'\\');
    }
    /**
     * Reads and returns a string that represents the name of the class that will be created.
     * 
     * @param string|null $suffix An optional string to append to the name of the class
     * if it does not exist. For example, If the user input is 'Users' and the
     * value of the suffix is 'Table', the returned value will be 'UsersTable'.
     * 
     * @return string A string that represents the name of the class.
     */
    public function getName($suffix = null) {
        $isNameValid = false;

        do {
            $className = trim($this->getOwner()->getInput('Enter a name for the new class:'));
            
            if ($suffix !== null) {
                $subSuffix = substr($className, strlen($className) - strlen($suffix));
                
                if ($subSuffix != $suffix) {
                    $className .= $suffix;
                }
            }
            
            $isNameValid = $this->_validateClassName($className);

            if (!$isNameValid) {
                $this->getOwner()->error('Invalid class name is given.');
            }
        } while (!$isNameValid);

        return $className;
    }
    private function _validateClassName($name) {
        $len = strlen($name);

        if ($len > 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $name[$x];

                if ($x == 0 && $char >= '0' && $char <= '9') {
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
    private function _validateNamespace($ns) {
        if ($ns == '\\') {
            return true;
        }
        $split = explode('\\', $ns);

        foreach ($split as $subNs) {
            $len = strlen($subNs);

            for ($x = 0 ; $x < $len ; $x++) {
                $char = $subNs[$x];

                if ($x == 0 && $char >= '0' && $char <= '9') {
                    return false;
                }

                if (!(($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9') || $char == '_')) {
                    return false;
                }
            }
        }

        return true;
    }
}
