<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateEntityCommand;

/**
 * Test cases for CreateEntityCommand
 *
 * @author Ibrahim
 */
class CreateEntityCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateEntity00() {
        $className = 'TestEntity'.time();

        $output = $this->executeSingleCommand(new CreateEntityCommand(), [], [
            $className,
            'n'   // Don't add properties
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter entity class name:\n",
            "Add properties to the entity?(y/N)\n",
            "Success: Entity class created at: ".APP_PATH."Domain".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateEntity01() {
        $className = 'TestEntity'.time();

        $output = $this->executeSingleCommand(new CreateEntityCommand(), [], [
            $className,
            'y',    // Add properties
            'id',
            'int',
            'n',    // Not nullable
            'name',
            'string',
            'n',    // Not nullable
            'email',
            'string',
            'y',    // Nullable
            "\n"    // Empty to finish adding properties
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateEntity02() {
        $className = 'TestEntity'.time();

        $output = $this->executeSingleCommand(new CreateEntityCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            'n'    // No properties
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter entity class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter entity class name:\n",
            "Add properties to the entity?(y/N)\n",
            "Success: Entity class created at: ".APP_PATH."Domain".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateEntityWithArgs00() {
        $className = 'TestEntity'.time();

        $output = $this->executeMultiCommand([
            CreateEntityCommand::class,
            '--class-name' => $className
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: Entity class created at: ".APP_PATH."Domain".DIRECTORY_SEPARATOR.$className.".php\n", $output);

        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateEntityWithArgs01() {
        $className = 'TestEntity'.time();
        $propsJson = json_encode([
            ['name' => 'id', 'type' => 'int', 'nullable' => false],
            ['name' => 'name', 'type' => 'string', 'nullable' => false],
            ['name' => 'email', 'type' => 'string', 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateEntityCommand::class,
            '--class-name' => $className,
            '--properties' => $propsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateEntityWithArgs02() {
        $output = $this->executeMultiCommand([
            CreateEntityCommand::class,
            '--class-name' => ''
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Class name cannot be empty.\n", $output);
    }
    /**
     * @test
     */
    public function testCreateEntityWithArgs03() {
        $className = 'TestEntity'.time();

        $output = $this->executeMultiCommand([
            CreateEntityCommand::class,
            '--class-name' => $className,
            '--properties' => 'invalid-json'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Error: Invalid JSON format for --properties parameter.\n", $output);
        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);
    }
}
