<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\SchedulerRunCommand;
use WebFiori\Framework\Scheduler\TasksManager;

class SchedulerRunCommandTest extends CLITestCase {

    public function setUp(): void {
        parent::setUp();
        TasksManager::reset();
        TasksManager::setPassword('123456');
        TasksManager::registerTasks();
    }

    /**
     * @test
     * Covers: exec() — no tasks registered
     */
    public function testNoTasks() {
        TasksManager::reset();

        $output = $this->executeSingleCommand(new SchedulerRunCommand(), ['p' => '123456']);

        $this->assertEquals(["Info: There are no scheduled tasks.\n"], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Covers: exec() — correct password, tasks run, results printed
     */
    public function testRunWithCorrectPassword() {
        $output = $this->executeSingleCommand(new SchedulerRunCommand(), ['p' => '123456']);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Total number of tasks: 5\n",
            "Executed Tasks: 4\n",
            "Successfully finished tasks:\n",
            "    Success Every Minute\n",
            "Failed tasks:\n",
            "    Fail 1\n",
            "    Fail 2\n",
            "    Fail 3\n",
        ], $output);
    }

    /**
     * @test
     * Covers: exec() — wrong password → INV_PASS → error, returns -1
     */
    public function testRunWithWrongPassword() {
        $output = $this->executeSingleCommand(new SchedulerRunCommand(), ['p' => 'wrong']);

        $this->assertEquals(["Error: Provided password is incorrect.\n"], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: CLIUtils::resolvePassword() env: prefix — password read from environment variable
     */
    public function testRunWithEnvPassword() {
        putenv('SCHEDULER_PASS=123456');

        $output = $this->executeSingleCommand(new SchedulerRunCommand(), ['p' => 'env:SCHEDULER_PASS']);

        putenv('SCHEDULER_PASS');  // unset

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Total number of tasks: 5\n", $output);
    }
    public function testRunAllSucceed() {
        TasksManager::reset();
        TasksManager::setPassword('123456');

        $task = new class('All Success') extends \WebFiori\Framework\Scheduler\AbstractTask {
            public function execute() { return true; }
            public function afterExec() {}
            public function onFail() {}
            public function onSuccess() {}
        };
        $task->cron('* * * * *');
        TasksManager::scheduleTask($task);

        $output = $this->executeSingleCommand(new SchedulerRunCommand(), ['p' => '123456']);

        $this->assertEquals(0, $this->getExitCode());
        $outputStr = implode('', $output);
        $this->assertStringContainsString('Successfully finished tasks:', $outputStr);
        $this->assertStringContainsString('    All Success', $outputStr);
        $this->assertStringContainsString('Failed tasks:', $outputStr);
        $this->assertStringContainsString('    <NONE>', $outputStr);
    }
}
