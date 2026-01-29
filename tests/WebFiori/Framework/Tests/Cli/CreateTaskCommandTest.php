<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateTaskCommand;

/**
 * Test cases for CreateTaskCommand
 *
 * @author Ibrahim
 */
class CreateTaskCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateTask00() {
        $className = 'TestTask'.time();
        
        $output = $this->executeSingleCommand(new CreateTaskCommand(), [], [
            $className,
            "\n", // Use default task name (same as class name)
            "\n", // Use default description
            'n'   // Don't add arguments
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter task class name:\n",
            "Enter task name: Enter = '$className'\n",
            "Enter task description: Enter = 'No Description'\n",
            "Add execution arguments to the task?(y/N)\n",
            "Success: Task class created at: ".APP_PATH."Tasks".DIRECTORY_SEPARATOR.$className."Task.php\n"
        ], $output);
        
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTask01() {
        $className = 'TestTask'.time();
        
        $output = $this->executeSingleCommand(new CreateTaskCommand(), [], [
            $className,
            'Email Sender Task',
            'Sends daily email reports',
            'y',    // Add arguments
            'email',
            'Recipient email address',
            'admin@example.com',
            'subject',
            'Email subject',
            "\n",   // No default for subject
            "\n"    // Empty to finish adding arguments
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTask02() {
        $className = 'TestTask'.time();
        
        $output = $this->executeSingleCommand(new CreateTaskCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            "\n",  // Use default task name
            "\n",  // Use default description
            'n'    // No arguments
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter task class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter task class name:\n",
            "Enter task name: Enter = '$className'\n",
            "Enter task description: Enter = 'No Description'\n",
            "Add execution arguments to the task?(y/N)\n",
            "Success: Task class created at: ".APP_PATH."Tasks".DIRECTORY_SEPARATOR.$className."Task.php\n"
        ], $output);
        
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTask03() {
        $className = 'TestTask'.time();
        
        $output = $this->executeSingleCommand(new CreateTaskCommand(), [
            'WebFiori',
            'create:task'
        ], [
            $className,
            'Backup Task',
            'Creates database backup',
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTaskWithArgs00() {
        $className = 'TestTask'.time();
        
        $output = $this->executeMultiCommand([
            CreateTaskCommand::class,
            '--class-name' => $className,
            '--name' => 'Cleanup Task',
            '--description' => 'Cleans up temporary files'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: Task class created at: ".APP_PATH."Tasks".DIRECTORY_SEPARATOR.$className."Task.php\n", $output);
        
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTaskWithArgs01() {
        $className = 'TestTask'.time();
        
        $output = $this->executeMultiCommand([
            CreateTaskCommand::class,
            '--class-name' => $className,
            '--name' => $className,
            '--description' => 'Test task'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTaskWithArgs02() {
        $output = $this->executeMultiCommand([
            CreateTaskCommand::class,
            '--class-name' => '',
            '--name' => 'Test',
            '--description' => 'Test'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Class name cannot be empty.\n", $output);
    }
    /**
     * @test
     */
    public function testCreateTaskWithArgs03() {
        $className = 'TestTask'.time();
        $argsJson = json_encode([
            ['name' => 'email', 'description' => 'Email address', 'default' => 'admin@example.com'],
            ['name' => 'subject', 'description' => 'Email subject']
        ]);
        
        $output = $this->executeMultiCommand([
            CreateTaskCommand::class,
            '--class-name' => $className,
            '--name' => 'Email Task',
            '--description' => 'Sends emails',
            '--args' => $argsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
    /**
     * @test
     */
    public function testCreateTaskWithArgs04() {
        $className = 'TestTask'.time();
        
        $output = $this->executeMultiCommand([
            CreateTaskCommand::class,
            '--class-name' => $className,
            '--name' => 'Simple Task',
            '--description' => 'Does something',
            '--args' => 'invalid-json'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Error: Invalid JSON format for --args parameter.\n", $output);
        $this->assertTrue(class_exists('\\App\\Tasks\\'.$className.'Task'));
        $this->removeClass('\\App\\Tasks\\'.$className.'Task');
    }
}
