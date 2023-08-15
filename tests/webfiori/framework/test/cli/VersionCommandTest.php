<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\cli\Runner;
use webfiori\framework\cli\commands\VersionCommand;
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
        $runner->setInputs();
        $this->assertEquals(0, $runner->runCommand(new VersionCommand()));
        $this->assertEquals([
            'Framework Version: '.WF_VERSION."\n",
            'Release Date: '.WF_RELEASE_DATE."\n",
            'Version Type: '.WF_VERSION_TYPE."\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test01() {
        $runner = new Runner();
        $runner->setInputs();
        $runner->register(new VersionCommand());
        $this->assertEquals(0, $runner->runCommand(null, [
            'v',
            '--ansi'
        ]));
        $this->assertEquals([
            "\e[1;94mFramework Version: \e[0m".WF_VERSION."\n",
            "\e[1;94mRelease Date: \e[0m".WF_RELEASE_DATE."\n",
            "\e[1;94mVersion Type: \e[0m".WF_VERSION_TYPE."\n",
        ], $runner->getOutput());
    }
}
