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
namespace webfiori\entity\cli;

use Error;
use phMysql\MySQLColumn;
use phMysql\MySQLQuery;
use restEasy\WebService;
use restEasy\APIFilter;
use restEasy\RequestParameter;
use webfiori\entity\AutoLoader;
use webfiori\entity\Util;
use webfiori\WebFiori;
use webfiori\logic\Controller;
/**
 * A command which is used to automate some of the common tasks such as 
 * creating query classes or controllers.
 * Note that this feature is Experimental and might have issues. Also, it 
 * might be removed in the future.
 * @author Ibrahim
 * @version 1.0
 */
class CreateCommand extends CLICommand {
    public function __construct() {
        parent::__construct('create', [], 'Creates a query class, entity, API or a controller (Experimental).');
    }
    /**
     * 
     * @param MySQLQuery $query
     */
    public function _addFks($query) {
        $addMoreFks = true;

        do {
            $refQuery = null;
            $refQueryName = $this->getInput('Enter the name of the referenced query class (with namespace):');
            try {
                $refQuery = new $refQueryName();
            } catch (Error $ex) {
                $this->error($ex->getMessage());
                continue;
            }

            if ($refQuery instanceof MySQLQuery) {
                $fkName = $this->getInput('Enter a name for the foreign key:');
                $fkCols = $this->_getFkCols($query);
                $fkArr = [];

                foreach ($fkCols as $colKey) {
                    $fkArr[$colKey] = $this->select('Select the column that will be referenced by the column \''.$colKey.'\':', $refQuery->getTable()->colsKeys());
                }
                $onUpdate = $this->select('Choose on update condition:', [
                    'cascade', 'restrict', 'set null', 'set default', 'no action'
                ], 1);
                $onDelete = $this->select('Choose on delete condition:', [
                    'cascade', 'restrict', 'set null', 'set default', 'no action'
                ], 1);
                $added = $query->getTable()->addReference($refQuery, $fkArr, $fkName, $onUpdate, $onDelete);

                if ($added) {
                    $this->success('Foreign key added.');
                } else {
                    $this->success('Unable to add the key.');
                }
            } else {
                $this->error('The given class is not an instance of the class \'MySQLQuery\'.');
            }

            $addMoreFks = $this->confirm('Would you like to add another foreign key?');
        } while ($addMoreFks);
    }
    public function _createEntityFromQuery() {
        $queryClassNameValidity = false;

        do {
            $queryClassName = $this->getInput('Enter query class name (include namespace):');

            if (!class_exists($queryClassName)) {
                $this->error('Class not found.');
                continue;
            }
            $queryObj = new $queryClassName();

            if (!$queryObj instanceof MySQLQuery) {
                $this->error('The given class is not a child of the class "MySQLQuery".');
                continue;
            }
            $queryClassNameValidity = true;
        } while (!$queryClassNameValidity);
        $this->println('We need from you to give us entity class information.');
        $classInfo = $this->getClassInfo();
        $implJsonI = $this->confirm('Would you like from your class to implement the interface JsonI?', true);
        $this->println('Generating your entity...');

        if (strlen($classInfo['namespace']) == 0) {
            $this->warning('The entity class will be added to the namespace "phMysql\entity".');
        }
        $queryObj->getTable()->createEntityClass([
            'store-path' => $classInfo['path'],
            'namespace' => $classInfo['namespace'],
            'class-name' => $classInfo['name'],
            'implement-jsoni' => $implJsonI
        ]);
        $this->success('Entity class created.');

        return 0;
    }
    public function _createWebServices() {
        $classInfo = $this->getClassInfo();
        $addServices = true;
        $servicesObj = new ServicesHolder();

        do {
            $serviceObj = new WebService('');
            $this->_setServiceName($serviceObj);
            $serviceObj->addRequestMethod($this->select('Request method:', WebService::METHODS, 0));
            $servicesObj->addAction($serviceObj, $this->confirm('Does the service require authorization?', false));

            if ($this->confirm('Would you like to add request parameters to the service?', false)) {
                $this->_addParamsToService($serviceObj);
            }

            $addServices = $this->confirm('Would you like to add another web service?');
        } while ($addServices);


        $this->println('Creating the class...');
        $servicesCreator = new WebServicesWriter($servicesObj, $classInfo);
        $servicesCreator->writeClass();
        $this->println('Class created.');
    }
    public function exec() {
        $options = [
            'Query class.',
            'Entity class from query.',
            'Controller class.',
            'Web services set.',
            'Database table from query class.',
            'Quit.'
        ];
        $answer = $this->select('What would you like to create?', $options, count($options) - 1);

        if ($answer == 'Quit.') {
            return 0;
        } else if ($answer == 'Query class.') {
            return $this->_createQueryClass();
        } else if ($answer == 'Entity class from query.') {
            return $this->_createEntityFromQuery();
        } else if ($answer == 'Controller class.') {
            return $this->_createController();
        } else if ($answer == 'Web services set.') {
            return $this->_createWebServices();
        } else if ($answer == 'Database table from query class.') {
            $this->_createDbTable();
        }
    }
    private function _createDbTable() {
        $dbConnections = array_keys(WebFiori::getConfig()->getDBConnections());

        if (count($dbConnections) != 0) {
            $dbConn = $this->select('Select database connection:', $dbConnections);
            $queryClassNameValidity = false;
            
            do {
                $queryClassName = $this->getInput('Enter query class name (include namespace):');

                if (!class_exists($queryClassName)) {
                    $this->error('Class not found.');
                    continue;
                }
                $queryObj = new $queryClassName();

                if (!$queryObj instanceof MySQLQuery) {
                    $this->error('The given class is not a child of the class "MySQLQuery".');
                    continue;
                }
                $queryClassNameValidity = true;
            } while (!$queryClassNameValidity);
            
            $tempController = new Controller();
            $tempController->setConnection($dbConn);
            $queryObj->createTable();
            $conn = WebFiori::getConfig()->getDBConnection($dbConn);
            $this->prints('The following query will be executed on the database ');
            $this->println($conn->getDBName(),[
                'color' => 'yellow'
            ]);
            $this->println($queryObj->getQuery(), [
                'color' => 'lightblue'
            ]);
            if ($this->confirm('Continue?', true)) {
                if ($tempController->excQ($queryObj)) {
                    $this->success('Database table created.');
                } else {
                    $this->error('Unable to create database table.');
                    $err = $tempController->getDBErrDetails();
                    $this->println("Error Code: %s\nError Message: %s", $err['error-code'], $err['error-message']);
                }
            }
            
        } else {
            $this->error('No database connections available. You must specify the connection manually later.');
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
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _addColComment($colObj) {
        if ($this->confirm('Would you like to add your own comment about the column?', false)) {
            $comment = $this->getInput('Enter your comment:');

            if (strlen($comment) != 0) {
                $colObj->setComment($comment);
            }
        }
        $this->success('Column added.');
    }
    /**
     * 
     * @param WebService $serviceObj
     */
    private function _addParamsToService($serviceObj) {
        $addMore = true;

        do {
            $paramObj = new RequestParameter('h');
            $paramObj->setType($this->select('Choose parameter type:', APIFilter::TYPES, 0));
            $this->_setParamName($paramObj);
            $added = $serviceObj->addParameter($paramObj);
 
            if ($added) {
                $this->success('New parameter added to the service \''.$serviceObj->getName().'\'.');
            } else {
                $this->warning('The parameter was not added.');
            }
            $addMore = $this->confirm('Would you like to add another parameter?');
        } while ($addMore);
    }
    private function _createController() {
        $classInfo = $this->getClassInfo();

        if ($this->confirm('Would you like to associate the controller with query class?', false)) {
            $classInfo['linked-query'] = $this->_getControllerQuery();
        }
        $dbConnections = array_keys(WebFiori::getConfig()->getDBConnections());

        if (count($dbConnections) != 0) {
            $classInfo['db-connection'] = $this->select('Select database connection:', $dbConnections);
        } else {
            $this->warning('No database connections available. You must specify the connection manually later.');
        }
        $writer = new ControllerClassWriter($classInfo);
        $writer->writeClass();
        $this->success('Class created.');

        return 0;
    }
    private function _createQueryClass() {
        $classInfo = $this->getClassInfo();

        $tempQuery = new MySQLQuery();
        $addMoreCols = true;
        $this->_setTableName($tempQuery);
        $this->_setTableComment($tempQuery);
        $addDefaultCols = $this->confirm('Would you like to include default columns? Default columns include "id", "created-on" and "last-updated".', false);

        if ($addDefaultCols) {
            $tempQuery->getTable()->addDefaultCols();
        }
        $this->println('Now we have to add columns to the table.');

        do {
            $colKey = $this->getInput('Enter a name for column key:');
            $colDatatype = $this->select('Select column data type:', MySQLColumn::DATATYPES, 0);
            $isAdded = $tempQuery->getTable()->addColumn($colKey, [
                'datatype' => $colDatatype
            ]);

            if (!$isAdded) {
                $this->warning('The column was not added. Mostly, key name is invalid. Try again.');
            } else {
                $colObj = $tempQuery->getCol($colKey);
                $this->_setSize($colObj);
                $this->_isPrimaryCheck($colObj);
                $this->_addColComment($colObj);
            }
            $addMoreCols = $this->confirm('Would you like to add another column?');
        } while ($addMoreCols);
        $tempQuery->createTable();

        if ($this->confirm('Would you like to add foreign keys to the table?', false)) {
            $this->_addFks($tempQuery);
        }

        if ($this->confirm('Would you like to create an entity class that maps to the database table?', false)) {
            $entityInfo = $this->getClassInfo();
            $entityInfo['implement-jsoni'] = $this->confirm('Would you like from your class to implement the interface JsonI?', true);
            $classInfo['entity-info'] = $entityInfo;
        }

        if (strlen($classInfo['namespace']) == 0) {
            $this->warning('The query class will be added to the namespace "phMysql\query" since no namespace was provided.');
        }

        if (isset($classInfo['entity-info']) && strlen($classInfo['entity-info']['namespace']) == 0) {
            $this->warning('The entity class will be added to the namespace "phMysql\entity" since no namespace was provided.');
        }
        $writer = new QueryClassWriter($tempQuery, $classInfo);
        $writer->writeClass();
        $this->success('New class created.');

        return 0;
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
    private function _getControllerQuery() {
        $validQuery = false;
        $queryName = '';

        do {
            $queryName = $this->getInput('Enter query class name (include namespace):');
            try {
                $queryObj = new $queryName();

                if (!($queryObj instanceof MySQLQuery)) {
                    $this->error('Given object is not an instance of the class MySQLQuery.');
                } else {
                    $validQuery = true;
                }
            } catch (Error $ex) {
                $this->error($ex->getMessage());
            }
        } while (!$validQuery);

        return $queryName;
    }
    private function _getFkCols($query) {
        $moreCols = true;
        $colNumber = 1;
        $keys = $query->getTable()->colsKeys();
        $fkCols = [];

        do {
            $colKey = $this->select('Select column #'.$colNumber.':', $keys);

            if (!in_array($colKey, $fkCols)) {
                $fkCols[] = $colKey;
                $colNumber++;
            } else {
                $this->error('The column is already added.');
            }
            $moreCols = $this->confirm('Would you like to add another column to the foreign key?');
        } while ($moreCols);

        return $fkCols;
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
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _isPrimaryCheck($colObj) {
        $colObj->setIsPrimary($this->confirm('Is this column primary?', false));
        $type = $colObj->getType();

        if (!$colObj->isPrimary()) {
            if (!($type == 'bool' || $type == 'boolean')) {
                $colObj->setIsUnique($this->confirm('Is this column unique?', false));
            }
            $this->_setDefaultValue($colObj);
            $colObj->setIsNull($this->confirm('Can this column have null values?', false));
        } else {
            if ($colObj->getType() == 'int') {
                $colObj->setIsAutoInc($this->confirm('Is this column auto increment?', false));
            }
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _setDefaultValue($colObj) {
        if ($this->confirm('Would you like to include default value for the column?', false)) {
            if ($colObj->getType() == 'bool' || $colObj->getType() == 'boolean') {
                $defaultVal = trim($this->getInput('Enter default value (true or false):'));

                if ($defaultVal == 'true') {
                    $colObj->setDefault(true);
                } else {
                    if ($defaultVal == 'false') {
                        $colObj->setDefault(false);
                    }
                }
            } else {
                $defaultVal = trim($this->getInput('Enter default value:'));

                if (strlen($defaultVal) != 0) {
                    $colObj->setDefault($defaultVal);
                }
            }
        }
    }
    /**
     * 
     * @param RequestParameter $paramObj
     */
    private function _setParamName($paramObj) {
        $validName = false;

        do {
            $paramName = $this->getInput('Enter a name for the request parameter:');
            $validName = $paramObj->setName($paramName);

            if (!$validName) {
                $this->error('Given name is invalid.');
            }
        } while (!$validName);
    }
    private function _setScale($colObj) {
        $colDataType = $colObj->getType();

        if ($colDataType == 'decimal' || $colDataType == 'float' || $colDataType == 'double') {
            $validScale = false;

            do {
                $scale = $this->getInput('Enter the scale (number of numbers to the right of decimal point):');
                $validScale = $colObj->setScale($scale);

                if (!$validScale) {
                    $this->error('Invalid scale value.');
                }
            } while (!$validScale);
        }
    }
    /**
     * 
     * @param WebService $serviceObj
     */
    private function _setServiceName($serviceObj) {
        $validName = false;

        do {
            $serviceName = $this->getInput('Enter a name for the new web service:');
            $validName = $serviceObj->setName($serviceName);

            if (!$validName) {
                $this->error('Given name is invalid.');
            }
        } while (!$validName);
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _setSize($colObj) {
        $type = $colObj->getType();
        $supportSize = $type == 'int' 
                || $type == 'varchar'
                || $type == 'decimal' 
                || $type == 'float'
                || $type == 'double' 
                || $type == 'text';

        if ($supportSize) {
            $valid = false;

            do {
                $colDataType = $colObj->getType();
                $dataSize = $this->getInput('Enter column size:');

                if ($colObj->getType() == 'varchar' && $dataSize > 21845) {
                    $this->warning('The data type "varchar" has a maximum size of 21845. The '
                            .'data type of the column will be changed to "mediumtext" if you continue.');

                    if (!$this->confirm('Would you like to change data type?', false)) {
                        continue;
                    }
                }

                if ($colDataType == 'int' && $dataSize > 11) {
                    $this->warning('Size is set to 11 since this is the maximum size for "int" type.');
                }
                $valid = $colObj->setSize($dataSize);

                if (!$valid) {
                    $this->error('Invalid size is given.');
                } else {
                    $this->_setScale($colObj);
                }
            } while (!$valid);
        }
    }
    /**
     * 
     * @param MySQLQuery $tempQuery
     */
    private function _setTableComment($tempQuery) {
        $incComment = $this->confirm('Would you like to add your comment about the table?', false);

        if ($incComment) {
            $tableComment = $this->getInput('Enter your comment:');

            if (strlen($tableComment) != 0) {
                $tempQuery->getTable()->setComment($tableComment);
            }
        }
    }
    private function _setTableName($tempQuery) {
        $invalidTableName = true;

        do {
            $tableName = $this->getInput('Enter database table name:');
            $invalidTableName = !$tempQuery->getTable()->setName($tableName);

            if ($invalidTableName) {
                $this->error('The given name is invalid.');
            }
        } while ($invalidTableName);
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
