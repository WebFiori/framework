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
        $commandRunner = new CommandRunner();
        $commandRunner->runCommand(new VersionCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'Framework Version: '.WF_VERSION."\n",
            'Release Date: '.WF_RELEASE_DATE."\n",
            'Version Type: '.WF_VERSION_TYPE."\n",
        ], $this));
    }

}
