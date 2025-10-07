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

use InvalidArgumentException;
use WebFiori\Cli\Command;
use webfiori\framework\cli\CLIUtils;
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
     * @var Command
     */
    private $ownerCommand;
    /**
     * Creates new instance of the class.
     *
     * @param Command $owner The command that owns the reader. Its used to read
     * user input and send output.
     */
    public function __construct(Command $owner) {
        $this->ownerCommand = $owner;
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
    public function getName(?string $suffix = null, $errMsg = 'Invalid class name is given.') {
        return $this->getOwner()->readClassName('Enter a name for the new class:', $suffix, $errMsg);
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
        return CLIUtils::readNamespace($this->getOwner(), $defaultNs, 'Enter an optional namespace for the class:');
    }
    /**
     * Returns the command that owns the instance.
     *
     * @return Command
     */
    public function getOwner() {
        return $this->ownerCommand;
    }
    /**
     * Prompts the user to enter class information including name, namespace and path.
     *
     * @param string $defaultNs An optional default namespace to use in case the
     * user did not provide a one. Note that this also will be the default path.
     *
     * @param string|null $suffix An optional string which will be appended to the
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
    public function readClassInfo(?string $defaultNs = null, ?string $suffix = null) {
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
     * Constructs class path based on its namespace.
     */
    private function getPath($default) {
        $fixedPath = ROOT_PATH.DS.trim(trim(str_replace('\\', DS, str_replace('/', DS, $default)),'/'),'\\');

        if (!Util::isDirectory($fixedPath, true)) {
            throw new InvalidArgumentException("Unable to create class at $default");
        }

        return $fixedPath;
    }
}
