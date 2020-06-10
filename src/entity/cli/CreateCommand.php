<?php
namespace webfiori\entity\cli;
use webfiori\entity\Util;
use webfiori\entity\AutoLoader;
use phMysql\MySQLQuery;
use phMysql\MySQLColumn;
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
            'Entity class from query.',
            'Quit.'
        ];
        $answer = $this->select('What would you like to create?', $options, count($options) - 1);
        if ($answer == 'Nothing.') {
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
    public function getClassInfo($options = []) {
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
        $tempQuery = new MySQLQuery();
        $addMoreCols = true;
        $invalidTableName = true;
        do {
            $tableName = $this->getInput('Enter database table name:');
            $invalidTableName = !$tempQuery->getTable()->setName($tableName);
            if ($invalidTableName) {
                $this->error('The given name is invalid.');
            }
        } while ($invalidTableName);
        $addDefaultCols = $this->confirm('Would you like to include default columns? Default columns include "id", "created-on" and "last-updated".');
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
                $dataSize = $this->getInput('Enter column size:');
                $isPrimary = $this->confirm('Is this column primary?', false);
                $isUnique = $this->confirm('Is this column unique?', false);
                $isNull = $this->confirm('Can this column have null values?', false);
                if ($this->confirm('Would you like to include default value for the column?', false)) {
                    $defaultVal = $this->getInput('Enter default value:');
                }
                if ($this->confirm('Would you like to add your own comment about the column?', false)) {
                    $defaultVal = $this->getInput('Enter your comment:');
                }
                $this->success('Column added.');
            }
            $addMoreCols = $this->confirm('Would you like to add another column?');
        } while ($addMoreCols);
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
