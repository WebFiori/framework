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


    /**
     * @test
     * Covers: getEnvironments() interactive yes-branch
     */
    public function testCreateMigrationInteractiveWithEnvironments() {
        $className = 'EnvMigration'.time();

        $output = $this->executeSingleCommand(new CreateMigrationCommand(), [], [
            $className,
            'Some description',
            'y',
            'dev',
            'test',
            '',
            'n',
        ]);

        $this->assertContains("Enter environment name (leave empty to finish):\n", $output);
        $this->assertContains("Success: Migration class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Migrations".DIRECTORY_SEPARATOR.$className.".php\n", $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));
        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
    }

    /**
     * @test
     * Covers: getEnvironments() via --environments arg
     */
    public function testCreateMigrationWithEnvironmentsArg() {
        $className = 'EnvArgMigration'.time();

        $output = $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $className,
            '--environments' => 'dev,prod',
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
     * Covers: selectDependenciesInteractive() - valid, invalid selection, finish
     */
    public function testCreateMigrationInteractiveWithDependencies() {
        $depName = 'DepMigration'.time();
        $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $depName,
        ]);

        $className = 'WithDepMigration'.time();

        $output = $this->executeSingleCommand(new CreateMigrationCommand(), [], [
            $className,
            'Some description',
            'n',
            'y',
            '99',
            '1',
            '',
        ]);

        $this->assertContains("Available database changes:\n", $output);
        $this->assertContains("Error: Invalid selection.\n", $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));

        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
        $this->removeClass('\\App\\Database\\Migrations\\'.$depName);
    }

    /**
     * @test
     * Covers: selectDependenciesInteractive() when no existing changes found
     */
    public function testCreateMigrationInteractiveDependenciesNoneAvailable() {
        $className = 'NoDepsAvailMigration'.time();

        $migrationsPath = APP_PATH.'Database'.DS.'Migrations';
        $tempPath = APP_PATH.'Database'.DS.'Migrations_bak_'.time();
        rename($migrationsPath, $tempPath);
        mkdir($migrationsPath);

        $seedersPath = APP_PATH.'Database'.DS.'Seeders';
        $tempSeedersPath = APP_PATH.'Database'.DS.'Seeders_bak_'.time();
        rename($seedersPath, $tempSeedersPath);
        mkdir($seedersPath);

        try {
            $output = $this->executeSingleCommand(new CreateMigrationCommand(), [], [
                $className,
                'Some description',
                'n',
                'y',
            ]);

            $this->assertContains("Info: No existing database changes found.\n", $output);
            $this->assertEquals(0, $this->getExitCode());
        } finally {
            $createdFile = $migrationsPath.DS.$className.'.php';
            if (file_exists($createdFile)) {
                unlink($createdFile);
            }
            rmdir($migrationsPath);
            rename($tempPath, $migrationsPath);
            rmdir($seedersPath);
            rename($tempSeedersPath, $seedersPath);
        }
    }

    /**
     * @test
     * Covers: resolveDependencies() via --depends-on - found and not-found (warning) branches
     */
    public function testCreateMigrationWithDependsOnArg() {
        $depName = 'ResolvableDepMigration'.time();
        $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $depName,
        ]);

        $className = 'DependsOnArgMigration'.time();

        $output = $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $className,
            '--depends-on' => $depName.',NonExistentClass',
        ]);

        $this->assertContains("Warning: Dependency 'NonExistentClass' not found, skipping.\n", $output);
        $this->assertContains("Success: Migration class created at: ".APP_PATH."Database".DIRECTORY_SEPARATOR."Migrations".DIRECTORY_SEPARATOR.$className.".php\n", $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Migrations\\'.$className));

        $this->removeClass('\\App\\Database\\Migrations\\'.$className);
        $this->removeClass('\\App\\Database\\Migrations\\'.$depName);
    }
}
