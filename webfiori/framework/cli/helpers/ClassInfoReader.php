<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\helpers;

use webfiori\cli\CLICommand;
use webfiori\framework\Util;
use webfiori\framework\writers\ClassWriter;
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
     * Constructs class path based on its namespace.
     */
    private function getPath($default) {
        $fixedPath = ROOT_DIR.DS.trim(trim(str_replace('\\', DS, str_replace('/', DS, $default)),'/'),'\\');
        if (!Util::isDirectory($fixedPath, true)) {
            throw new \InvalidArgumentException("Unable to create class at $default");
        }
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
        return self::readNamespace($this->getOwner(), $defaultNs, 'Enter an optional namespace for the class:');
    }
    /**
     * Reads and validates class namespace.
     * 
     * @param CLICommand $c The command that will be used to read the input from.
     * 
     * @param string $defaultNs An optional string that will be used as default
     * namespace if no input is provided.
     * 
     * @param string $prompt The text that will be shown to the user as prompt for
     * the namespace.
     * 
     * @return string A validated string that represents a namespace.
     */
    public static function readNamespace(CLICommand $c, string $defaultNs = '\\', $prompt = 'Enter class namespace:') : string {
        $isNameValid = false;

        do {
            $ns = str_replace('/','\\',trim($c->getInput($prompt, $defaultNs)));
            $isNameValid = ClassWriter::isValidNamespace($ns);

            if (!$isNameValid) {
                $c->error('Invalid namespace is given.');
            }
        } while (!$isNameValid);

        return trim($ns,'\\');
    }
    /**
     * Reads and validates class name.
     * 
     * @param CLICommand $c The command that will be used to read the input from.
     * 
     * @param string|null $suffix An optional string to append to class name.
     * 
     * @param string $prompt The text that will be shown to the user as prompt for
     * class name.
     * 
     * @return string A string that represents a valid class name.
     */
    public static function readName(CLICommand $c, string $suffix = null, string $prompt = 'Enter class name:') : string {
        $isNameValid = false;

        do {
            $className = trim($c->getInput($prompt));
            
            if ($suffix !== null) {
                $subSuffix = substr($className, strlen($className) - strlen($suffix));
                
                if ($subSuffix != $suffix) {
                    $className .= $suffix;
                }
            }
            
            $isNameValid = ClassWriter::isValidClassName($className);

            if (!$isNameValid) {
                $c->error('Invalid class name is given.');
            }
        } while (!$isNameValid);

        return $className;
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
        return self::readName($this->getOwner(), $suffix, 'Enter a name for the new class:');
    }
}
