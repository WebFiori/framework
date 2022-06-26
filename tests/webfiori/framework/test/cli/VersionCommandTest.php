<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\commands\VersionCommand;
use webfiori\framework\cli\Runner;
use webfiori\framework\cli\ArrayInputStream;
use webfiori\framework\cli\ArrayOutputStream;
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
        Runner::setInput([]);
        
        $this->assertEquals(0, Runner::runCommand(new VersionCommand()));
        $this->assertEquals([
            'Framework Version: '.WF_VERSION."\n",
            'Release Date: '.WF_RELEASE_DATE."\n",
            'Version Type: '.WF_VERSION_TYPE."\n",
        ], Runner::getOutput());
    }
    /**
     * @TEST
     */
    public function test01() {
        Runner::setInput([]);
        $this->assertEquals(0, Runner::runCommand(new VersionCommand()));
        $this->assertEquals([
            'Framework Version: '.WF_VERSION."\n",
            'Release Date: '.WF_RELEASE_DATE."\n",
            'Version Type: '.WF_VERSION_TYPE."\n",
        ], Runner::getOutput());
    }
}
