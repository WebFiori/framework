<?php

namespace webfiori\tests\entity\cli;

use webfiori\framework\cli\CLICommand;

class TestCommand extends CLICommand {
    public function __construct($commandName, $args = array(), $description = '') {
        parent::__construct($commandName, $args, $description);
    }
    public function exec() {
        return 0;
    }

}
