<?php
namespace webfiori\entity\cli;
use webfiori\entity\Util;
use webfiori\entity\AutoLoader;
use phMysql\MySQLQuery;
use phMysql\MySQLColumn;
use Exception;
use Error;
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
    public function exec() {
        $options = [
            'Query class.',
            'Entity class from query.',
            'Quit.'
        ];
        $answer = $this->select('What would you like to create?', $options, count($options) - 1);
        if ($answer == 'Quit.') {
            return 0;
        } else if ($answer == 'Query class.') {
            return $this->_createQueryClass();
        } else if ($answer == 'Entity class from query.') {
            return $this->_createEntityFromQuery();
        }
        
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
        if (strlen($classInfo['namespace']) == 0){
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
            
            $addMoreFks = $this->confirm ('Would you like to add another foreign key?');
        } while ($addMoreFks);
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
        
        $this->println($tempQuery->getQuery());
        if ($this->confirm('Would you like to add foreign keys to the table?', false)) {
            $this->_addFks($tempQuery);
        }
        if ($this->confirm('Would you like to create an entity class that maps to the database table?')) {
            $entityInfo = $this->getClassInfo();
            $entityInfo['implement-jsoni'] = $this->confirm('Would you like from your class to implement the interface JsonI?', true);
            $classInfo['entity-info'] = $entityInfo;
        }
        if (strlen($classInfo['namespace']) == 0){
            $this->warning('The query class will be added to the namespace "phMysql\query" since no namespace was provided.');
        }
        if (isset($classInfo['entity-info'])  && strlen($classInfo['entity-info']['namespace']) == 0){
            $this->warning('The entity class will be added to the namespace "phMysql\entity" since no namespace was provided.');
        }
        $writer = new QueryClassCreator($tempQuery, $classInfo);
        $writer->writeClass();
        $this->success('New class created.');
        
        return 0;
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _isPrimaryCheck($colObj) {
        $colObj->setIsPrimary($this->confirm('Is this column primary?', false));
                
        if (!$colObj->isPrimary()) {
            $colObj->setIsUnique($this->confirm('Is this column unique?', false));
        } else if ($colObj->getType() == 'int') {
            $colObj->setIsAutoInc($this->confirm('Is this column auto increment?', false));
            $colObj->setIsNull($this->confirm('Can this column have null values?', false));
            $this->_setDefaultValue($colObj);
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
                } else if ($defaultVal == 'false') {
                    $colObj->setDefault(false);
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
                            . 'data type of the column will be changed to "mediumtext" if you continue.');
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
