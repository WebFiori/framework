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
namespace webfiori\framework\cli\commands;

use webfiori\database\Table;
use webfiori\framework\cli\CLICommand;
use webfiori\framework\cli\helpers\CreateCLIClassHelper;
use webfiori\framework\cli\helpers\CreateCronJob;
use webfiori\framework\cli\helpers\CreateMiddleware;
use webfiori\framework\cli\helpers\CreateTable;
use webfiori\framework\cli\helpers\CreateTableObj;
use webfiori\framework\cli\helpers\CreateThemeHelper;
use webfiori\framework\cli\helpers\CreateWebService;
use webfiori\framework\Util;
use webfiori\framework\cli\helpers\ClassInfoReader;
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
    public function exec() {
        $what = $this->getArgValue('--what');

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
                'CLI Command.',
                'Theme.',
                'Quit.'
            ];
            $answer = $this->select('What would you like to create?', $options, count($options) - 1);
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
            } else {
                $this->info('Not implemented yet.');
            }
        }
        return 0;
    }
}
