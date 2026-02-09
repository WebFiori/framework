<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateMigrationCommand;

/**
 * Test cases for CreateMigrationCommand.
 *
 * @author Ibrahim
 */
class CreateMigrationCommandTest extends CLITestCase {
    
    /**
     * @test
     */
    public function testCreateMigrationWithArgs() {
        $className = 'TestMigration'.time();
        
        $output = $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $className,
            '--description' => 'Test migration description'
        ]);
        
        $this->assertEquals([
            "Success: Migration class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Migrations".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));
        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateMigrationInteractive() {
        $className = 'InteractiveMigration'.time();
        
        $output = $this->executeSingleCommand(new CreateMigrationCommand(), [], [
            $className,
            'Interactive migration description',
            'n', // No environments
            'n'  // No dependencies
        ]);
        
        $this->assertEquals([
            "Enter migration class name:\n",
            "Enter migration description: Enter = 'No description'\n",
            "Restrict to specific environments?(y/N)\n",
            "Add dependencies?(y/N)\n",
            "Success: Migration class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Migrations".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));
        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateMigrationWithEmptyClassName() {
        $className = 'EmptyTestMigration'.time();
        
        $output = $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => ''
        ], [
            $className
        ]);
        
        $this->assertEquals([
            "Error: --class-name cannot be empty string.\n",
            "Enter migration class name:\n",
            "Success: Migration class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Migrations".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));
        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateMigrationWithDefaultDescription() {
        $className = 'DefaultDescMigration'.time();
        
        $output = $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $className
        ]);
        
        $this->assertEquals([
            "Success: Migration class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Migrations".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));
        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
    }
}
