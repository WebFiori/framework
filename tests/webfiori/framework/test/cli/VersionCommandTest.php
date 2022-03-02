<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\VersionCommand;
/**
 * Description of VersionCommandTest
 *
 * @author Ibrahim
 */
class VersionCommandTest extends TestCase {

    public function test00() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        $commandRunner->runCommand(new VersionCommand());
        //$this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'Framework Version: '.WF_VERSION,
            'Release Date: '.WF_RELEASE_DATE,
            'Version Type: '.WF_VERSION_TYPE,
            ""
        ], $this));
    }

}
