<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommandCommand;

/**
 * Test cases for CreateCommandCommand
 *
 * @author Ibrahim
 */
class CreateCommandCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateCommand00() {
        $className = 'TestCmd'.time();

        $output = $this->executeSingleCommand(new CreateCommandCommand(), [], [
            $className,
            "\n", // Use default command name
            "\n", // Use default description
            'n'   // Don't add arguments
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter command class name:\n",
            "Enter command name: Enter = '".strtolower($className)."'\n",
            "Enter command description: Enter = ''\n",
            "Add arguments to the command?(y/N)\n",
            "Success: Command class created at: ".APP_PATH."Commands".DIRECTORY_SEPARATOR.$className."Command.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Commands\\'.$className.'Command'));
        $this->removeClass('\\App\\Commands\\'.$className.'Command');
    }
    /**
     * @test
     */
    public function testCreateCommand01() {
        $className = 'TestCmd'.time();

        $output = $this->executeSingleCommand(new CreateCommandCommand(), [], [
            $className,
            'test-command',
            'A test command',
            'y',    // Add arguments
            'name',
            'User name',
            'n',    // Not optional
            'n',    // No allowed values
            'email',
            'User email',
            'y',    // Optional
            'n',    // No allowed values
            "\n"    // Empty to finish adding arguments
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Commands\\'.$className.'Command'));
        $this->removeClass('\\App\\Commands\\'.$className.'Command');
    }
    /**
     * @test
     */
    public function testCreateCommand02() {
        $className = 'TestCmd'.time();

        $output = $this->executeSingleCommand(new CreateCommandCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            "\n",  // Use default command name
            "\n",  // Use default description
            'n'    // No arguments
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter command class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter command class name:\n",
            "Enter command name: Enter = '".strtolower($className)."'\n",
            "Enter command description: Enter = ''\n",
            "Add arguments to the command?(y/N)\n",
            "Success: Command class created at: ".APP_PATH."Commands".DIRECTORY_SEPARATOR.$className."Command.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Commands\\'.$className.'Command'));
        $this->removeClass('\\App\\Commands\\'.$className.'Command');
    }
    /**
     * @test
     */
    public function testCreateCommandWithArgs00() {
        $className = 'TestCmd'.time();

        $output = $this->executeMultiCommand([
            CreateCommandCommand::class,
            '--class-name' => $className,
            '--name' => 'my-command',
            '--description' => 'My custom command'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: Command class created at: ".APP_PATH."Commands".DIRECTORY_SEPARATOR.$className."Command.php\n", $output);

        $this->assertTrue(class_exists('\\App\\Commands\\'.$className.'Command'));
        $this->removeClass('\\App\\Commands\\'.$className.'Command');
    }
    /**
     * @test
     */
    public function testCreateCommandWithArgs01() {
        $className = 'TestCmd'.time();
        $argsJson = json_encode([
            ['name' => '--name', 'description' => 'User name', 'optional' => false],
            ['name' => '--type', 'description' => 'User type', 'optional' => true, 'values' => ['admin', 'user']]
        ]);

        $output = $this->executeMultiCommand([
            CreateCommandCommand::class,
            '--class-name' => $className,
            '--name' => 'user-command',
            '--description' => 'Manages users',
            '--args' => $argsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Commands\\'.$className.'Command'));
        $this->removeClass('\\App\\Commands\\'.$className.'Command');
    }
    /**
     * @test
     */
    public function testCreateCommandWithArgs02() {
        $output = $this->executeMultiCommand([
            CreateCommandCommand::class,
            '--class-name' => '',
            '--name' => 'test',
            '--description' => 'Test'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Class name cannot be empty.\n", $output);
    }
    /**
     * @test
     */
    public function testCreateCommandWithArgs03() {
        $className = 'TestCmd'.time();

        $output = $this->executeMultiCommand([
            CreateCommandCommand::class,
            '--class-name' => $className,
            '--name' => 'test command',
            '--description' => 'Test'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Command name cannot be empty or contain spaces.\n", $output);
    }
}
