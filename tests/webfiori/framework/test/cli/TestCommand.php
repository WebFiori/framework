<?php

namespace webfiori\framework\test\cli;

use webfiori\framework\cli\CLICommand;

class TestCommand extends CLICommand {
    public function __construct($commandName, $args = array(), $description = '') {
        parent::__construct($commandName, $args, $description);
    }
    public function exec() : int {
        $name = $this->getArgValue('name');
        $this->println('Hello '.$name.'!', [
            'color' => 'red',
        ]);
        $this->println('Ok');
        return 0;
    }

}
