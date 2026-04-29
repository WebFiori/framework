<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\SchedulerDaemonCommand;
use WebFiori\Framework\Scheduler\TasksManager;

class SchedulerDaemonCommandTest extends CLITestCase {

    public function setUp(): void {
        parent::setUp();
        TasksManager::reset();
        TasksManager::setPassword('123456');
        TasksManager::registerTasks();
    }

    /**
     * @test
     * Covers: exec() — no tasks registered, returns -1
     */
    public function testNoTasks() {
        TasksManager::reset();

        $output = $this->executeSingleCommand(new SchedulerDaemonCommand(), ['p' => '123456']);

        $this->assertEquals(["Info: There are no scheduled tasks.\n"], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: exec() — --max-minutes=0 is rejected
     */
    public function testInvalidMaxMinutesZero() {
        $output = $this->executeSingleCommand(new SchedulerDaemonCommand(), [
            'p' => '123456',
            '--max-minutes' => '0'
        ]);

        $this->assertEquals(["Error: --max-minutes must be a positive integer.\n"], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: exec() — negative --max-minutes is rejected
     */
    public function testInvalidMaxMinutesNegative() {
        $output = $this->executeSingleCommand(new SchedulerDaemonCommand(), [
            'p' => '123456',
            '--max-minutes' => '-5'
        ]);

        $this->assertEquals(["Error: --max-minutes must be a positive integer.\n"], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: exec() — wrong password returns -1
     */
    public function testWrongPassword() {
        $output = $this->executeSingleCommand(new SchedulerDaemonCommand(), [
            'p' => 'wrong',
            '--max-minutes' => '1'
        ]);

        $this->assertContains("Error: Provided password is incorrect.\n", $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: __construct() — command metadata
     */
    public function testCommandInfo() {
        $cmd = new SchedulerDaemonCommand();
        $this->assertEquals('scheduler:daemon', $cmd->getName());
        $this->assertEquals('Run the scheduler in a loop for a limited duration.', $cmd->getDescription());
    }
}
