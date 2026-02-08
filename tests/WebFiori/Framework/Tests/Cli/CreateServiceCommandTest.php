<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateServiceCommand;

/**
 * Test cases for CreateServiceCommand
 *
 * @author Ibrahim
 */
class CreateServiceCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateService00() {
        $className = 'TestService'.time();

        $output = $this->executeSingleCommand(new CreateServiceCommand(), [], [
            $className,
            "\n", // Use default description
            'n'   // Don't add methods
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter service class name:\n",
            "Enter service description: Enter = 'REST API Service'\n",
            "Add methods to the service?(y/N)\n",
            "Success: Service class created at: ".APP_PATH."Apis".DIRECTORY_SEPARATOR.$className."Service.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Apis\\'.$className.'Service'));
        $this->removeClass('\\App\\Apis\\'.$className.'Service');
    }
    /**
     * @test
     */
    public function testCreateService01() {
        $className = 'TestService'.time();

        $output = $this->executeSingleCommand(new CreateServiceCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            "\n",  // Use default description
            'n'    // No methods
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter service class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter service class name:\n",
            "Enter service description: Enter = 'REST API Service'\n",
            "Add methods to the service?(y/N)\n",
            "Success: Service class created at: ".APP_PATH."Apis".DIRECTORY_SEPARATOR.$className."Service.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Apis\\'.$className.'Service'));
        $this->removeClass('\\App\\Apis\\'.$className.'Service');
    }
    /**
     * @test
     */
    public function testCreateServiceWithArgs00() {
        $className = 'TestService'.time();

        $output = $this->executeMultiCommand([
            CreateServiceCommand::class,
            '--class-name' => $className,
            '--description' => 'User management service'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: Service class created at: ".APP_PATH."Apis".DIRECTORY_SEPARATOR.$className."Service.php\n", $output);

        $this->assertTrue(class_exists('\\App\\Apis\\'.$className.'Service'));
        $this->removeClass('\\App\\Apis\\'.$className.'Service');
    }
    /**
     * @test
     */
    public function testCreateServiceWithArgs01() {
        $className = 'TestService'.time();
        $methodsJson = json_encode([
            [
                'http' => 'GET',
                'name' => 'getUser',
                'params' => [
                    ['name' => 'id', 'type' => 'INT', 'description' => 'User ID', 'min' => 1]
                ],
                'return' => 'array'
            ],
            [
                'http' => 'POST',
                'name' => 'createUser',
                'params' => [
                    ['name' => 'name', 'type' => 'STRING', 'description' => 'User name'],
                    ['name' => 'email', 'type' => 'EMAIL', 'description' => 'User email']
                ],
                'return' => 'array'
            ]
        ]);

        $output = $this->executeMultiCommand([
            CreateServiceCommand::class,
            '--class-name' => $className,
            '--description' => 'User API',
            '--methods' => $methodsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Apis\\'.$className.'Service'));
        $this->removeClass('\\App\\Apis\\'.$className.'Service');
    }
    /**
     * @test
     */
    public function testCreateServiceWithArgs02() {
        $output = $this->executeMultiCommand([
            CreateServiceCommand::class,
            '--class-name' => '',
            '--description' => 'Test'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Class name cannot be empty.\n", $output);
    }
    /**
     * @test
     */
    public function testCreateServiceWithArgs03() {
        $className = 'TestService'.time();

        $output = $this->executeMultiCommand([
            CreateServiceCommand::class,
            '--class-name' => $className,
            '--description' => 'Test service',
            '--methods' => 'invalid-json'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Error: Invalid JSON format for --methods parameter.\n", $output);
        $this->assertTrue(class_exists('\\App\\Apis\\'.$className.'Service'));
        $this->removeClass('\\App\\Apis\\'.$className.'Service');
    }
}
