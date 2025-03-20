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

use webfiori\cli\Argument;
use webfiori\cli\CLICommand;
use webfiori\framework\cli\CLIUtils;
use webfiori\framework\cli\helpers\ClassInfoReader;
use webfiori\framework\cli\helpers\CreateAPITestCase;
use webfiori\framework\cli\helpers\CreateBackgroundTask;
use webfiori\framework\cli\helpers\CreateCLIClassHelper;
use webfiori\framework\cli\helpers\CreateDBAccessHelper;
use webfiori\framework\cli\helpers\CreateFullRESTHelper;
use webfiori\framework\cli\helpers\CreateMiddleware;
use webfiori\framework\cli\helpers\CreateMigration;
use webfiori\framework\cli\helpers\CreateTableObj;
use webfiori\framework\cli\helpers\CreateThemeHelper;
use webfiori\framework\cli\helpers\CreateWebService;
/**
 * A command which is used to automate some common tasks such as
 * creating table classes or controllers.
 * Note that this feature is Experimental and might have issues. Also, it
 * might be removed in the future.
 * @author Ibrahim
 * @version 1.0
 */
class CreateCommand extends CLICommand {
    public function __construct() {
        parent::__construct('create', [
            new Argument('--c', 'What will be created. Possible values: table, entity, web-service, job, middleware, command, theme.', true),
            new Argument('--table', '', true),
            new Argument('--manager', 'Web services manager class.', true),
            new Argument('--service', 'The name of web service that is registered by web services manager.', true),
            new Argument('--defaults', 'An option which is used to indicate that default values should be used for non-provided options.', true)
        ], 'Creates a system entity (middleware, web service, background process ...).');
    }
    public function createEntityFromQuery(): int {
        $tableObj = CLIUtils::readTable($this);
        $defaultNs = APP_DIR.'\\entity';
        $this->println('We need from you to give us entity class information.');
        $infoReader = new ClassInfoReader($this);
        $classInfo = $infoReader->readClassInfo($defaultNs);
        $implJsonI = $this->confirm('Would you like from your class to implement the interface JsonI?', true);

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
            return 0;
        } else if ($answer == 'Database table class.') {
            $create = new CreateTableObj($this);
            $create->readClassInfo();
        } else if ($answer == 'Entity class from table.') {
            $this->createEntityFromQuery();
        } else if ($answer == 'Web service.') {
            $create = new CreateWebService($this);
            $create->readClassInfo();
        } else if ($answer == 'Middleware.') {
            $create = new CreateMiddleware($this);
            $create->readClassInfo();
        } else if ($answer == 'CLI Command.') {
            $create = new CreateCLIClassHelper($this);
            $create->readClassInfo();
        } else if ($answer == 'Background Task.') {
            $create = new CreateBackgroundTask($this);
            $create->readClassInfo();
        } else if ($answer == 'Theme.') {
            $create = new CreateThemeHelper($this);
            $create->readClassInfo();
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
            $create->readInfo();
        } else if ($answer == 'Web service test case.') {
            $create = new CreateAPITestCase($this);
            if (!$create->readClassInfo()) {
                return -1;
            }
        }  else if ($answer == 'Database migration.') {
            $create = new CreateMigration($this);
            if ($create->isConfigured()) {
                $create->writeClass();
                $writer = $create->getWriter();
                $this->info("Migration Name: ".$writer->getMigrationName());
                $this->info("Migration Order: ".$writer->getMigrationOrder());
                return 0;
            } else {
                return -1;
            }
        }

        return 0;
    }
    private function getWhat() {
        $options = [];
        $options['table'] = 'Database table class.';
        $options['entity'] = 'Entity class from table.';
        $options['web-service'] = 'Web service.';
        $options['task'] = 'Background Task.';
        $options['middleware'] = 'Middleware.';
        $options['command'] = 'CLI Command.';
        $options['theme'] = 'Theme.';
        $options['db'] = 'Database access class based on table.';
        $options['rest'] = 'Complete REST backend (Database table, entity, database access and web services).';
        $options['api-test'] = 'Web service test case.';
        $options['migration'] = 'Database migration.';
        $options['q'] = 'Quit.';
        $what = $this->getArgValue('--c');
        $answer = null;

        if ($what !== null) {
            $answer = $options[$what] ?? null;

            if ($answer === null) {
                $this->warning('The argument --c has invalid value.');
            }
        }

        if ($answer === null) {
            $answer = $this->select('What would you like to create?', $options, count($options) - 1);
        }

        return $answer;
    }
}
