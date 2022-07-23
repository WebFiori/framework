<?php

namespace webfiori\framework\test\cli;
use webfiori\framework\WebFioriApp;
use webfiori\file\File;
use webfiori\framework\cli\commands\CreateCommand;
use PHPUnit\Framework\TestCase;
/**
 * Description of CronCommandTest
 *
 * @author Ibrahim
 */
class CronCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput();
        $runner->setArgsVector([
            'webfiori',
            'cron'
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Info: At least one of the options '--check', '--force' or '--show-job-args' must be provided.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test01() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput();
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--check',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Total number of jobs: 0\n",
            "Executed Jobs: 0\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
            "    <NONE>\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test02() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput();
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--check',
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: The argument 'p' is missing. It must be provided if cron password is set.\n",
        ], $runner->getOutput());
    }
}
