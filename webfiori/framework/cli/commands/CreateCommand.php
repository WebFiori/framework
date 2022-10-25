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
use webfiori\framework\cli\CLIUtils;
use webfiori\framework\cli\helpers\ClassInfoReader;
use webfiori\framework\cli\helpers\CreateCLIClassHelper;
use webfiori\framework\cli\helpers\CreateCronJob;
use webfiori\framework\cli\helpers\CreateDBAccessHelper;
use webfiori\framework\cli\helpers\CreateFullRESTHelper;
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
           'rest' => 'Complete REST backend (Database table, entity, database access and web services).',
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
        $tableObj = CLIUtils::readTable($this);
        $defaultNs = APP_DIR_NAME.'\\entity';
        $this->println('We need from you to give us entity class information.');
        $infoReader = new ClassInfoReader($this);
        $classInfo = $infoReader->readClassInfo($defaultNs);
        $implJsonI = $this->confirm('Would you like from your class to implement the interface JsonI?', true);
        
        if (strlen($classInfo['namespace']) == 0) {
            $this->warning('The entity class will be added to the namespace "'.'".');
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
            $create->readClassInfo();
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
            $create->setTable(CLIUtils::readTable($this));
            $this->println('We need from you to give us class information.');
            $create->readDbClassInfo();
            $this->println('We need from you to give us entity class information.');
            $create->readEntityInfo();
            $create->confirnIncludeColsUpdate();
            $create->writeClass();
        } else if ($answer == 'Complete REST backend (Database table, entity, database access and web services).') {
            $create = new CreateFullRESTHelper($this);
        }
        
        return 0;
    }
}
