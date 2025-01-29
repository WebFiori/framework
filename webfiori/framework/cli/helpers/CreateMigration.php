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

use webfiori\database\migration\MigrationsRunner;
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
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $ns = APP_DIR.'\\database\\migrations';

        if (!$command->isArgProvided('--defaults')) {
            $ns = CLIUtils::readNamespace($command, $ns , 'Migration namespace:');
        }
        
        $runner = new MigrationsRunner(ROOT_PATH.DS. str_replace('\\', DS, $ns), $ns, null);
        parent::__construct($command, new DatabaseMigrationWriter($runner));
        $this->writer = $this->getWriter();
        $this->setNamespace($ns);
        
        if (!$command->isArgProvided('--defaults')) {
            $this->setClassName($command->readClassName('Provide an optional name for the class that will have migration logic:', null));
            $this->readClassInfo();
        }
    }

    private function readClassInfo() {
        
        $name = $this->getInput('Enter an optional name for the migration:', $this->writer->getMigrationName());
        $order = $this->getCommand()->readInteger('Enter an optional execution order for the migration:', $this->writer->getMigrationOrder());
        

        $this->writer->setMigrationName($name);
        $this->writer->setMigrationOrder($order);
    }
}
