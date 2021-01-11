<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh, phMysql library.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace webfiori\framework\cli;

use webfiori\database\Table;
use webfiori\framework\Util;
/**
 * A command which is used to automate some of the common tasks such as 
 * creating table classes or controllers.
 * Note that this feature is Experimental and might have issues. Also, it 
 * might be removed in the future.
 * @author Ibrahim
 * @version 1.0
 */
class CreateCommand extends CLICommand {
    public function __construct() {
        parent::__construct('create', [
            '--what' => [
                'description' => 'An optional parameter which is used to specify what '
                .'would you like to create. Possible values are: "e" for entity, "t" for '
                .'database table.',
                'optional' => true,
                'values' => [
                    'e','t','ws'
                ]
            ],
            '--table-class' => [
                'optional' => true,
            ]
        ], 'Creates a system entity (middleware, web service, background process ...).');
    }

    public function _createEntityFromQuery() {
        $tableClassNameValidity = false;
        $tableClassName = $this->getArgValue('--table-class');

        do {
            if (strlen($tableClassName) == 0) {
                $tableClassName = $this->getInput('Enter table class name (include namespace):');
            }

            if (!class_exists($tableClassName)) {
                $this->error('Class not found.');
                $tableClassName = null;
                continue;
            }
            $tableObj = new $tableClassName();

            if (!$tableObj instanceof Table) {
                $this->error('The given class is not a child of the class "webfiori\database\Table".');
                $tableClassName = '';
                continue;
            }
            $tableClassNameValidity = true;
        } while (!$tableClassNameValidity);
        $this->println('We need from you to give us entity class information.');
        $classInfo = $this->getClassInfo('app\\entity','app'.DS.'entity');
        $implJsonI = $this->confirm('Would you like from your class to implement the interface JsonI?', true);
        $this->println('Generating your entity...');

        if (strlen($classInfo['namespace']) == 0) {
            $this->warning('The entity class will be added to the namespace "app\database".');
            $classInfo['namespace'] = 'app\\database';
        }
        $mapper = $tableObj->getEntityMapper();
        $mapper->setPath($classInfo['path']);
        $mapper->setNamespace($classInfo['namespace']);
        $mapper->setEntityName($classInfo['name']);
        $mapper->setUseJsonI($implJsonI);
        $mapper->create();
        $this->success('Entity class created.');

        return 0;
    }

    public function exec() {
        $what = $this->getArgValue('--what');
        $create = null;

        if ($what !== null) {
            if ($what == 'e') {
                $this->_createEntityFromQuery();
            } else if ($what == 't') {
                $create = new CreateTable($this);
            } else if ($what == 'ws') {
                $create = new CreateWebService($this);
            }
        } else {
            $options = [
                'Database table class.',
                'Entity class from table.',
                'Web service.',
                'Background job.',
                'Middleware.',
                'Database table from class.',
                'Quit.'
            ];
            $answer = $this->select('What would you like to create?', $options, count($options) - 1);

            if ($answer == 'Quit.') {
                return 0;
            } else if ($answer == 'Database table class.') {
                $create = new CreateTableObj($this);
            } else if ($answer == 'Entity class from table.') {
                return $this->_createEntityFromQuery();
            } else if ($answer == 'Web service.') {
                $create = new CreateWebService($this);
            } else if ($answer == 'Database table from class.') {
                $create = new CreateTable($this);
            } else if ($answer == 'Middleware.') {
                $create = new CreateMiddleware($this);
                return true;
            } else if ($answer == 'Background job.') {
                $create = new CreateCronJob($this);
                return true;
            } else {
                $this->info('Not implemented yet.');
            }
        }
    }
    /**
     * Prompts the user to enter class information such as it is name.
     * This method is useful in case we would like to create a class.
     * @return array The method will return an array that contains 3 indices: 
     * <ul>
     * <li><b>name</b>: The name of the class.</li>
     * <li><b>namespace</b>: The namespace of the class. It will be empty string if no 
     * namespace is entered.</li>
     * <li><b>path</b>: The location at which the class will be created.</li>
     * </ul>
     * @since 1.0
     */
    public function getClassInfo($defaltNs = null, $defaultLoc = null) {
        $classExist = true;

        do {
            $className = $this->_getClassName();
            $ns = $this->_getNamespace($defaltNs);
            $classWithNs = $ns.'\\'.$className;
            $classExist = class_exists($classWithNs);

            if ($classExist) {
                $this->error('A class in the given namespace which has the given name was found.');
            }
        } while ($classExist);
        $isFileExist = true;

        do {
            $path = $this->_getClassPath($defaultLoc);

            if (file_exists($path.DS.$className.'.php')) {
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
    private function _getClassPath($default) {
        $validPath = false;

        do {
            clearstatcache();
            $path = $this->getInput("Where would you like to store the class? (must be a directory inside '".ROOT_DIR."')", $default);
            $fixedPath = ROOT_DIR.DS.trim(trim(str_replace('\\', DS, str_replace('/', DS, $path)),'/'),'\\');

            if (Util::isDirectory($fixedPath, true)) {
                $validPath = true;
            } else {
                $this->error('Provided direcory is not a directory or it does not exist.');
            }
        } while (!$validPath);

        return $fixedPath;
    }

    private function _getNamespace($defaultNs) {
        $isNameValid = false;

        do {
            $ns = str_replace('/','\\',trim($this->getInput('Enter an optional namespace for the class:', $defaultNs)));
            $isNameValid = $this->_validateNamespace($ns);

            if (!$isNameValid) {
                $this->error('Invalid namespace is given.');
            }
        } while (!$isNameValid);

        return trim($ns,'\\');
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
