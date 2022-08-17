<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2021 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\helpers;

use webfiori\database\Table;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\WebFioriApp;
use webfiori\framework\writers\DBClassWriter;

/**
 * Description of CreateDBAccessHelper
 *
 * @author Ibrahim
 */
class CreateDBAccessHelper extends CreateClassHelper {

    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new DBClassWriter());
    }
    public function setTable(Table $t) {
        $this->getWriter()->setTable($t);
    }
    public function confirnIncludeColsUpdate() {
        if ($this->getCommand()->confirm('Would you like to have update methods for every single column?', false)) {
            $this->getWriter()->includeColumnsUpdate();
        }
    }
    public function readDbClassInfo() {  
        $info = $this->getClassInfo(APP_DIR_NAME.'\\database', 'DB');
        $this->getWriter()->setNamespace($info['namespace']);
        $this->getWriter()->setPath($info['namespace']);
        $this->getWriter()->setClassName($info['name']);
        $this->getWriter()->setConnection($this->getConnection());
    }
    private function getConnection() {
        $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());
        
        if (count($dbConnections) != 0) {
            $dbConnections[] = 'None';
            $conn = $this->select('Select database connecion to use with the class:', $dbConnections, count($dbConnections) - 1);
            
            if ($conn != 'None') {
                return $conn;
            }
        } else {
            $this->warning('No database connections were found. Make sure to specify connection later inside the class.');
        }
        return '';
    }
    public function readEntityInfo() {
        $t = $this->getTable();
        $m = $t->getEntityMapper();
        $m->setEntityName(ClassInfoReader::readName($this->getCommand(), null, 'Entity class name:'));
        $m->setNamespace(ClassInfoReader::readNamespace($this->getCommand(), APP_DIR_NAME.'\\entity', 'Entity namespace:'));
    }
    public function getTable() : Table {
        return $this->getWriter()->getTable();
    }
    public function readTable() {
        $tableClassNameValidity = false;
        $tableClassName = $this->getCommand()->getArgValue('--table');
        $tableObj = null;
        
        do {
            if ($tableClassName !== null && strlen($tableClassName) == 0) {
                $tableClassName = $this->getCommand()->getInput('Enter database table class name (include namespace):');
            }

            if (!class_exists($tableClassName)) {
                $this->getCommand()->error('Class not found.');
                $tableClassName = '';
                continue;
            }
            $tableObj = new $tableClassName();

            if (!$tableObj instanceof Table) {
                $this->getCommand()->error('The given class is not a child of the class "webfiori\database\Table".');
                $tableClassName = '';
                continue;
            }
            $tableClassNameValidity = true;
        } while (!$tableClassNameValidity);
        
        $this->setTable($tableObj);
    }
}
