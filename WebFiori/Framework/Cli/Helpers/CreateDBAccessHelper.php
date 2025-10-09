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
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Database\Table;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\DBClassWriter;

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
    /**
     * Prompt the user if he would like to have update methods for every single
     * column of the table.
     */
    public function confirnIncludeColsUpdate() {
        if ($this->getCommand()->confirm('Would you like to have update methods for every single column?', false)) {
            $this->getWriter()->includeColumnsUpdate();
        }
    }
    /**
     * Returns the table at which the database access class will be associated with.
     *
     * @return Table The table at which the database access class will be associated with.
     */
    public function getTable() : Table {
        return $this->getWriter()->getTable();
    }
    /**
     * Prompt the user for basic database class information including name and
     * the namespace at which the class will be added to.
     */
    public function readDbClassInfo() {
        $info = $this->getClassInfo(APP_DIR.'\\Database', 'DB');
        $this->getWriter()->setNamespace($info['namespace']);
        $this->getWriter()->setPath(ROOT_PATH.DS.$info['namespace']);
        $this->getWriter()->setClassName($info['name']);
        $this->getWriter()->setConnection($this->getConnection());
    }

    public function readEntityInfo() {
        $t = $this->getTable();
        $m = $t->getEntityMapper();
        $m->setEntityName($this->getCommand()->readClassName('Entity class name:', null));
        $m->setNamespace($this->getCommand()->readNamespace('Entity namespace:',  APP_DIR.'\\Entity'));
    }
    public function readTable() {
        $tableClassNameValidity = false;
        $tableClassName = $this->getCommand()->getArgValue('--table');
        $tableObj = null;

        do {
            if ($tableClassName === null || strlen($tableClassName) == 0) {
                $tableClassName = $this->getCommand()->getInput('Enter database table class name (include namespace):');
            }

            if (!class_exists($tableClassName)) {
                $this->getCommand()->error('Class not found.');
                $tableClassName = '';
                continue;
            }
            $tableObj = new $tableClassName();

            if (!$tableObj instanceof Table) {
                $this->getCommand()->error('The given class is not a child of the class "WebFiori\Database\Table".');
                $tableClassName = '';
                continue;
            }
            $tableClassNameValidity = true;
        } while (!$tableClassNameValidity);

        $this->setTable($tableObj);
    }
    /**
     * Sets the table at which the database access class will be associated with.
     *
     * @param Table $t The table at which the database access class will be associated with.
     */
    public function setTable(Table $t) {
        $this->getWriter()->setTable($t);
    }
    private function getConnection() {
        $dbConnections = array_keys(App::getConfig()->getDBConnections());

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
}
