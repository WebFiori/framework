<?php

namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\DownCommand;
use WebFiori\Framework\Cli\Commands\UpCommand;
use WebFiori\Framework\Cli\Commands\QueueStatusCommand;
use WebFiori\Framework\Cli\Commands\QueueRetryCommand;

class MaintenanceCommandsTest extends CLITestCase {
    private string $maintenanceFile;

    protected function setUp(): void {
        parent::setUp();
        $this->maintenanceFile = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'.maintenance';

        if (file_exists($this->maintenanceFile)) {
            unlink($this->maintenanceFile);
        }
    }

    protected function tearDown(): void {
        if (file_exists($this->maintenanceFile)) {
            unlink($this->maintenanceFile);
        }
    }
    /** @test */
    public function testDownCommand() {
        $this->executeSingleCommand(new DownCommand(), []);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(file_exists($this->maintenanceFile));
        $output = $this->getOutput();
        $this->assertStringContainsString('maintenance mode', implode('', $output));
    }
    /** @test */
    public function testDownCommandWithOptions() {
        $this->executeSingleCommand(new DownCommand(), [
            '--allow' => '192.168.1.1,10.0.0.1',
            '--retry' => '300',
            '--message' => 'Back soon',
        ]);
        $this->assertEquals(0, $this->getExitCode());
        $data = json_decode(file_get_contents($this->maintenanceFile), true);
        $this->assertEquals(['192.168.1.1', '10.0.0.1'], $data['allowed']);
        $this->assertEquals(300, $data['retry_after']);
        $this->assertEquals('Back soon', $data['message']);
    }
    /** @test */
    public function testUpCommandWhenDown() {
        file_put_contents($this->maintenanceFile, json_encode(['message' => 'test']));
        $this->executeSingleCommand(new UpCommand(), []);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertFalse(file_exists($this->maintenanceFile));
        $output = $this->getOutput();
        $this->assertStringContainsString('live', implode('', $output));
    }
    /** @test */
    public function testUpCommandWhenAlreadyUp() {
        $this->executeSingleCommand(new UpCommand(), []);
        $this->assertEquals(0, $this->getExitCode());
        $output = $this->getOutput();
        $this->assertStringContainsString('not in maintenance', implode('', $output));
    }
    /** @test */
    public function testQueueStatusCommand() {
        $this->executeSingleCommand(new QueueStatusCommand(), []);
        $this->assertEquals(0, $this->getExitCode());
        $output = implode('', $this->getOutput());
        $this->assertStringContainsString('Pending', $output);
        $this->assertStringContainsString('Failed', $output);
    }
    /** @test */
    public function testQueueRetryNoArgs() {
        $this->executeSingleCommand(new QueueRetryCommand(), []);
        $this->assertEquals(1, $this->getExitCode());
        $output = implode('', $this->getOutput());
        $this->assertStringContainsString('--id', $output);
    }
    /** @test */
    public function testQueueRetryFlush() {
        $this->executeSingleCommand(new QueueRetryCommand(), ['--flush' => '']);
        $this->assertEquals(0, $this->getExitCode());
        $output = implode('', $this->getOutput());
        $this->assertStringContainsString('removed', $output);
    }
}
