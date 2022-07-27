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
            "Total number of jobs: 5\n",
            "Executed Jobs: 5\n",
            "Successfully finished jobs:\n",
            "    Success Every Minute\n",
            "    Success 1\n",
            "Failed jobs:\n",
            "    Fail 1\n",
            "    Fail 2\n",
            "    Fail 3\n",
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
    /**
     * @test
     */
    public function test03() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput([
            '0'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--force',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
            "    Fail 1\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test04() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput([
            '0'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--force',
            '--show-log',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Running job(s) check...\n",
            "Forceing job 'Fail 1' to execute...\n",
            "Active job: \"Fail 1\" ...\n",
            "Calling the method app\jobs\Fail1TestJob::execute()\n",
            "Calling the method app\jobs\Fail1TestJob::onFail()\n",
            "Calling the method app\jobs\Fail1TestJob::afterExec()\n",
            "Check finished.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
            "    Fail 1\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test05() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput([
            '1'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--force',
            '--show-log',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Running job(s) check...\n",
            "Forceing job 'Fail 2' to execute...\n",
            "Active job: \"Fail 2\" ...\n",
            "Calling the method app\jobs\Fail2TestJob::execute()\n",
            "WARNING: An exception was thrown while performing the operation app\jobs\Fail2TestJob::execute. The output of the job might be not as expected.\n",
            "Exception class: Error\n",
            "Exception message: Call to undefined method app\jobs\Fail2TestJob::undefined()\n",
            "Thrown in: Fail2TestJob\n",
            "Line: 36\n",
            "Calling the method app\jobs\Fail2TestJob::onFail()\n",
            "Calling the method app\jobs\Fail2TestJob::afterExec()\n",
            "Check finished.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
            "    Fail 2\n"
        ], $runner->getOutput());
    }
}
