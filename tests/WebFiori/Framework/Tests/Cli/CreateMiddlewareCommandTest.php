<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateMiddlewareCommand;

/**
 * Test cases for CreateMiddlewareCommand
 *
 * @author Ibrahim
 */
class CreateMiddlewareCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateMiddleware00() {
        $className = 'TestMd'.time();

        $output = $this->executeSingleCommand(new CreateMiddlewareCommand(), [], [
            $className,
            "\n", // Use default middleware name (same as class name)
            "\n", // Use default priority (0)
            'n'   // Don't add to groups
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter middleware class name:\n",
            "Enter middleware name: Enter = '$className'\n",
            "Enter middleware priority: Enter = '0'\n",
            "Add middleware to groups?(y/N)\n",
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));
        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddleware01() {
        $className = 'TestMd'.time();

        $output = $this->executeSingleCommand(new CreateMiddlewareCommand(), [], [
            $className,
            'My Custom Middleware',
            '100',
            'y',    // Add to groups
            'api',
            'web',
            "\n"    // Empty to finish adding groups
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter middleware class name:\n",
            "Enter middleware name: Enter = '$className'\n",
            "Enter middleware priority: Enter = '0'\n",
            "Add middleware to groups?(y/N)\n",
            "Enter group name (leave empty to finish):\n",
            "Enter group name (leave empty to finish):\n",
            "Enter group name (leave empty to finish):\n",
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));
        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddleware02() {
        $className = 'TestMd'.time();

        $output = $this->executeSingleCommand(new CreateMiddlewareCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            "\n",  // Use default middleware name
            "\n",  // Use default priority
            'n'    // No groups
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter middleware class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter middleware class name:\n",
            "Enter middleware name: Enter = '$className'\n",
            "Enter middleware priority: Enter = '0'\n",
            "Add middleware to groups?(y/N)\n",
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));
        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddleware03() {
        $className = 'TestMd'.time();

        $output = $this->executeSingleCommand(new CreateMiddlewareCommand(), [
            'WebFiori',
            'create:middleware'
        ], [
            $className,
            "\n",  // Use default middleware name (same as class name)
            '50',
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));

        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddlewareWithArgs00() {
        $className = 'TestMd'.time();

        $output = $this->executeMultiCommand([
            CreateMiddlewareCommand::class,
            '--class-name' => $className,
            '--name' => 'Auth Middleware',
            '--priority' => '100',
            '--groups' => ''
        ]);

        $this->assertEquals([
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());

        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));
        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddlewareWithArgs01() {
        $className = 'TestMd'.time();

        $output = $this->executeMultiCommand([
            CreateMiddlewareCommand::class,
            '--class-name' => $className,
            '--name' => $className,
            '--priority' => '0',
            '--groups' => 'api,web,admin'
        ]);

        $this->assertEquals([
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));
        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddlewareWithArgs02() {
        $className = 'TestMd'.time();
        $output = $this->executeMultiCommand([
            CreateMiddlewareCommand::class,
            '--class-name' => '',
        ], [
            $className,
            "\n",  // Use default middleware name (same as class name)
            '50',
            "\n"
        ]);

        $this->assertEquals([
            "Error: --class-name cannot be empty string.\n",
            "Enter middleware class name:\n",
            "Enter middleware name: Enter = '$className'\n",
            "Enter middleware priority: Enter = '0'\n",
            "Add middleware to groups?(y/N)\n",
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));

        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
    /**
     * @test
     */
    public function testCreateMiddlewareWithArgs03() {
        $className = 'TestMd'.time();

        $output = $this->executeMultiCommand([
            CreateMiddlewareCommand::class,
            '--class-name' => $className,
            '--name' => $className,
            '--priority' => 'invalid',
            '--groups' => ''
        ]);

        $this->assertEquals([
            "Error: Priority must be a number.\n"
        ], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCreateMiddlewareWithArgs04() {
        $className = 'TestMd'.time();

        $output = $this->executeMultiCommand([
            CreateMiddlewareCommand::class,
            '--class-name' => $className,
            '--name' => 'My Middleware',
            '--priority' => '50',
            '--groups' => 'api,web'
        ]);

        $this->assertEquals([
            "Success: Middleware class created at: ".APP_PATH."Middleware".DIRECTORY_SEPARATOR.$className."Middleware.php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Middleware\\'.$className.'Middleware'));
        $this->removeClass('\\App\\Middleware\\'.$className.'Middleware');
    }
}
