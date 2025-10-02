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
    private $isConfigured;
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
        
        $runner = new SchemaRunner(null);

        parent::__construct($command, new DatabaseMigrationWriter($runner));
        $this->setNamespace($ns);
        $this->setClassName($command->readClassName('Provide a name for the class that will have migration logic:', null));
    }
    public function isConfigured() : bool {
        return $this->isConfigured;
    }
}
