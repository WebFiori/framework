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
namespace webfiori\framework\cli\helpers;

use WebFiori\Database\Schema\SchemaRunner;
use webfiori\framework\cli\CLIUtils;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\DatabaseMigrationWriter;
/**
 * A helper class which is used to help in creating scheduler tasks classes using CLI.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class CreateMigration extends CreateClassHelper {
    /**
     * @var DatabaseMigrationWriter
     */
    private $writer;
    private $isConfigured;
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $ns = APP_DIR.'\\database\\migrations';
        $this->isConfigured = false;
        if (!$command->isArgProvided('--defaults')) {
            $ns = CLIUtils::readNamespace($command, $ns , 'Migration namespace:');
        }
        
        $runner = $this->initRunner($ns, $command);
        if ($runner === null) {
            $command->error("Unable to set migrations path.");
        } else {
            parent::__construct($command, new DatabaseMigrationWriter($runner));
            $this->writer = $this->getWriter();
            $this->setNamespace($ns);
            
            $this->isConfigured = true;
            if (!$command->isArgProvided('--defaults')) {
                $this->setClassName($command->readClassName('Provide an optional name for the class that will have migration logic:', null));
                $this->readClassInfo();
                
            }
        }
    }
    public function isConfigured() : bool {
        return $this->isConfigured;
    }
    private function initRunner($ns, $command) {
        $path = ROOT_PATH.DS. str_replace('\\', DS, $ns);
        if (!is_dir($path)) {
            $command->warning("The path '$path' does not exist.");
            $create = $command->confirm("Would you like to create it?", true);
            
            if ($create) {
                if (!mkdir($path)) {
                    $command->error("Unable to create directory.");
                    return null;
                }
            } else {
                return null;
            }
        }
        return new MigrationsRunner($path, $ns, null);
    }

    private function readClassInfo() {
        
        $name = $this->getInput('Enter an optional name for the migration:', $this->writer->getMigrationName());
        $order = $this->getCommand()->readInteger('Enter an optional execution order for the migration:', $this->writer->getMigrationOrder());
        

        $this->writer->setMigrationName($name);
        $this->writer->setMigrationOrder($order);
    }
}
