<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\commands\VersionCommand;
use webfiori\cli\Runner;
/**
 * Description of VersionCommandTest
 *
 * @author Ibrahim
 */
class VersionCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = new Runner();
        $runner->setInput();
        $this->assertEquals(0, $runner->runCommand(new VersionCommand()));
        $this->assertEquals([
            'Framework Version: '.WF_VERSION."\n",
            'Release Date: '.WF_RELEASE_DATE."\n",
            'Version Type: '.WF_VERSION_TYPE."\n",
        ], $runner->getOutput());
    }
}
