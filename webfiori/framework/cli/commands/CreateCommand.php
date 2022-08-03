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
namespace webfiori\framework\cli\commands;

use webfiori\cli\CLICommand;
use webfiori\database\Table;
use webfiori\framework\cli\helpers\CreateCLIClassHelper;
use webfiori\framework\cli\helpers\CreateCronJob;
use webfiori\framework\cli\helpers\CreateDBAccessHelper;
use webfiori\framework\cli\helpers\CreateMiddleware;
use webfiori\framework\cli\helpers\CreateTable;
use webfiori\framework\cli\helpers\CreateTableObj;
use webfiori\framework\cli\helpers\CreateThemeHelper;
use webfiori\framework\cli\helpers\CreateWebService;
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
            '--c' => [
                'optional' => true,
                'description' => 'What will be created. Possible values: table, entity, web-service, job, middleware, command, theme.'
            ]
        ], 'Creates a system entity (middleware, web service, background process ...).');
    }
    private function getWhat() {
        $options = [
           'table' => 'Database table class.',
           'entity' => 'Entity class from table.',
           'web-service' => 'Web service.',
           'job' => 'Background job.',
           'middleware' => 'Middleware.',
           'command' => 'CLI Command.',
           'theme' => 'Theme.',
           'db' => 'Database access class based on table.',
           'Quit.'
        ];
        $what = $this->getArgValue('--c');
        $answer = null;
        if ($what !== null) {
            $answer = isset($options[$what]) ? $options[$what] : null;
            
            if ($answer === null) {
                $this->warning('The argument --c has invalid value.');
            }
        }
        if ($answer === null) {
            $answer = $this->select('What would you like to create?', $options, count($options) - 1);
        }
        return $answer;
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
        $classInfo = $this->getClassInfo(APP_DIR_NAME.'\\entity');
        $implJsonI = $this->confirm('Would you like from your class to implement the interface JsonI?', true);
        
        if (strlen($classInfo['namespace']) == 0) {
            $this->warning('The entity class will be added to the namespace "'.APP_DIR_NAME.'\database".');
            $classInfo['namespace'] = APP_DIR_NAME.'\\database';
        }
        $mapper = $tableObj->getEntityMapper();
        if ($this->confirm('Would you like to add extra attributes to the entity?', false)) {
            $addExtra = true;
            
            while ($addExtra) {
                if ($mapper->addAttribute($this->getInput('Enter attribute name:'))) {
                    $this->success('Attribute successfully added.');
                } else {
                    $this->warning('Unable to add attribute.');
                }
                $addExtra = $this->confirm('Would you like to add another attribute?', false);
            }
        }
        $this->println('Generating your entity...');
        $mapper->setPath($classInfo['path']);
        $mapper->setNamespace($classInfo['namespace']);
        $mapper->setEntityName($classInfo['name']);
        $mapper->setUseJsonI($implJsonI);
        $mapper->create();
        $this->success('Entity class created.');

        return 0;
    }
    public function exec() : int {
        
        $answer = $this->getWhat();
        
        if ($answer == 'Quit.') {
        } else if ($answer == 'Database table class.') {
            $create = new CreateTableObj($this);
        } else if ($answer == 'Entity class from table.') {
            $this->_createEntityFromQuery();
        } else if ($answer == 'Web service.') {
            $create = new CreateWebService($this);
        } else if ($answer == 'Database table from class.') {
            $create = new CreateTable($this);
        } else if ($answer == 'Middleware.') {
            $create = new CreateMiddleware($this);
        } else if ($answer == 'CLI Command.') {
            $create = new CreateCLIClassHelper($this);
        } else if ($answer == 'Background job.') {
            $create = new CreateCronJob($this);
        } else if ($answer == 'Theme.') {
            $create = new CreateThemeHelper($this);
        } else if ($answer == 'Database access class based on table.') {
            $create = new CreateDBAccessHelper($this);
        }
        
        return 0;
    }
}
