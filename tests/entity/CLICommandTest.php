<?php

namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\cli\CLICommand;

class CLICommandTest  extends TestCase{
    public function functionName() {
        $format = CLICommand::formatOutput('Hello', []);
        $this->assertEquals('Hello', $format);
    }
}
