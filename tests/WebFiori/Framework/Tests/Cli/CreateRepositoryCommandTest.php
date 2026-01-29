<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateRepositoryCommand;

/**
 * Test cases for CreateRepositoryCommand
 *
 * @author Ibrahim
 */
class CreateRepositoryCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateRepository00() {
        $className = 'TestRepo'.time();
        
        $output = $this->executeSingleCommand(new CreateRepositoryCommand(), [], [
            $className,
            'App\\Domain\\User',
            'users',
            "\n", // Use default id field
            'n'   // Don't add properties
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter repository class name:\n",
            "Enter entity class (e.g., App\\Domain\\User):\n",
            "Enter table name:\n",
            "Enter ID field name: Enter = 'id'\n",
            "Add properties to the repository?(y/N)\n",
            "Success: Repository class created at: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Repository".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Repository\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateRepository01() {
        $className = 'TestRepo'.time();
        
        $output = $this->executeSingleCommand(new CreateRepositoryCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            'App\\Domain\\User',
            'users',
            "\n",  // Use default id field
            'n'    // No properties
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter repository class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter repository class name:\n",
            "Enter entity class (e.g., App\\Domain\\User):\n",
            "Enter table name:\n",
            "Enter ID field name: Enter = 'id'\n",
            "Add properties to the repository?(y/N)\n",
            "Success: Repository class created at: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Repository".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Repository\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateRepositoryWithArgs00() {
        $className = 'TestRepo'.time();
        
        $output = $this->executeMultiCommand([
            CreateRepositoryCommand::class,
            '--class-name' => $className,
            '--entity' => 'App\\Domain\\User',
            '--table' => 'users',
            '--id-field' => 'id'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: Repository class created at: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Repository".DIRECTORY_SEPARATOR.$className.".php\n", $output);
        
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Repository\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateRepositoryWithArgs01() {
        $className = 'TestRepo'.time();
        $propsJson = json_encode([
            ['name' => 'id', 'type' => 'int'],
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'email', 'type' => 'string']
        ]);
        
        $output = $this->executeMultiCommand([
            CreateRepositoryCommand::class,
            '--class-name' => $className,
            '--entity' => 'App\\Domain\\User',
            '--table' => 'users',
            '--id-field' => 'id',
            '--properties' => $propsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Repository\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateRepositoryWithArgs02() {
        $output = $this->executeMultiCommand([
            CreateRepositoryCommand::class,
            '--class-name' => '',
            '--entity' => 'App\\Domain\\User',
            '--table' => 'users'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Class name cannot be empty.\n", $output);
    }
    /**
     * @test
     */
    public function testCreateRepositoryWithArgs03() {
        $className = 'TestRepo'.time();
        
        $output = $this->executeMultiCommand([
            CreateRepositoryCommand::class,
            '--class-name' => $className,
            '--entity' => 'App\\Domain\\User',
            '--table' => 'users',
            '--id-field' => 'id',
            '--properties' => 'invalid-json'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Error: Invalid JSON format for --properties parameter.\n", $output);
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Repository\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$className);
    }
}
