<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\VersionCommand;

/**
 * Description of VersionCommandTest
 *
 * @author Ibrahim
 */
class VersionCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $output = $this->executeSingleCommand(new VersionCommand(), [], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            'Framework Version: '.WF_VERSION."\n",
            'Release Date: '.WF_RELEASE_DATE."\n",
            'Version Type: '.WF_VERSION_TYPE."\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function test01() {
        $output = $this->executeSingleCommand(new VersionCommand(), [
            'v',
            '--ansi'
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "\e[1;94mFramework Version: \e[0m".WF_VERSION."\n",
            "\e[1;94mRelease Date: \e[0m".WF_RELEASE_DATE."\n",
            "\e[1;94mVersion Type: \e[0m".WF_VERSION_TYPE."\n",
        ], $output);
    }
}
