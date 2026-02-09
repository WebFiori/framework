<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateSeederCommand;

/**
 * Test cases for CreateSeederCommand.
 *
 * @author Ibrahim
 */
class CreateSeederCommandTest extends CLITestCase {
    
    /**
     * @test
     */
    public function testCreateSeederWithArgs() {
        $className = 'TestSeeder'.time();
        
        $output = $this->executeMultiCommand([
            CreateSeederCommand::class,
            '--class-name' => $className,
            '--description' => 'Test seeder description'
        ]);
        
        $this->assertEquals([
            "Success: Seeder class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Seeders".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Seeders\\'.$className));
        $this->removeClass('\\App\\Database\\Seeders\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateSeederInteractive() {
        $className = 'InteractiveSeeder'.time();
        
        $output = $this->executeSingleCommand(new CreateSeederCommand(), [], [
            $className,
            'Interactive seeder description'
        ]);
        
        $this->assertEquals([
            "Enter seeder class name:\n",
            "Enter seeder description: Enter = 'No description'\n",
            "Success: Seeder class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Seeders".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Seeders\\'.$className));
        $this->removeClass('\\App\\Database\\Seeders\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateSeederWithEmptyClassName() {
        $className = 'EmptyTestSeeder'.time();
        
        $output = $this->executeMultiCommand([
            CreateSeederCommand::class,
            '--class-name' => ''
        ], [
            $className
        ]);
        
        $this->assertEquals([
            "Error: --class-name cannot be empty string.\n",
            "Enter seeder class name:\n",
            "Success: Seeder class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Seeders".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Seeders\\'.$className));
        $this->removeClass('\\App\\Database\\Seeders\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateSeederWithDefaultDescription() {
        $className = 'DefaultDescSeeder'.time();
        
        $output = $this->executeMultiCommand([
            CreateSeederCommand::class,
            '--class-name' => $className
        ]);
        
        $this->assertEquals([
            "Success: Seeder class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Seeders".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Seeders\\'.$className));
        $this->removeClass('\\App\\Database\\Seeders\\'.$className);
    }
}
