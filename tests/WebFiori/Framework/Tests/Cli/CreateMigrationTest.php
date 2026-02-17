<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\Schema\AbstractMigration;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
/**
 * @author Ibrahim
 */
class CreateMigrationTest extends CLITestCase {
    
    protected function setUp(): void {
        parent::setUp();
        $migrationsDir = APP_PATH . DS . 'Database' . DS . 'Migrations';
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0755, true);
        }
    }
    
    protected function tearDown(): void {
        $migrationsDir = APP_PATH . DS . 'Database' . DS . 'Migrations';
        if (is_dir($migrationsDir)) {
            $files = glob($migrationsDir . DS . '*.php');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        parent::tearDown();
    }
    
    /**
     * @test
     */
    public function testCreateMigration01() {
        $name = 'CoolMigration';
        $clazz = '\\App\\Database\\Migrations\\'.$name;
        
        $this->assertEquals([
            "Migration namespace: Enter = 'App\Database\Migrations'\n",
            "Provide a name for the class that will have migration logic:\n",
            "Does this migration depend on other migrations?(y/N)\n",
            'Info: New class was created at "'. APP_PATH .'Database'.DS.'Migrations".'."\n",
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
        ], [
            "\n",
            $name,
            "n"
        ]));
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Database' . DS . 'Migrations' . DS . $name . '.php';
        $this->assertTrue(file_exists($filePath), "Class file was not created: $filePath");
        require_once $filePath;
        $this->assertTrue(class_exists($clazz));
        
        $instance = new $clazz();
        $this->assertInstanceOf(AbstractMigration::class, $instance);
        $this->assertEquals([], $instance->getDependencies());
        $this->assertEquals([], $instance->getEnvironments());
        
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function testCreateMigrationWithDependencies() {
        // First create a base migration
        $baseName = 'BaseMigration';
        
        $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
        ], [
            "\n",
            $baseName,
            "n",
            "n"
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        $baseFile = APP_PATH . 'Database' . DS . 'Migrations' . DS . $baseName . '.php';
        $this->assertTrue(file_exists($baseFile), "Base migration file should exist");
        
        // Manually create a dependent migration using DatabaseChangeGenerator to verify it works
        $generator = new \WebFiori\Database\Schema\DatabaseChangeGenerator();
        $generator->setNamespace('App\\Database\\Migrations');
        $generator->setPath(APP_PATH . 'Database' . DS . 'Migrations');
        $generator->createMigration('ManualDependent', [
            \WebFiori\Database\Schema\GeneratorOption::DEPENDENCIES => ['\\App\\Database\\Migrations\\BaseMigration']
        ]);
        
        $manualFile = APP_PATH . 'Database' . DS . 'Migrations' . DS . 'ManualDependent.php';
        $this->assertTrue(file_exists($manualFile));
        $content = file_get_contents($manualFile);
        $this->assertStringContainsString('getDependencies', $content);
        $this->assertStringContainsString('BaseMigration', $content);
        
        require_once $baseFile;
        require_once $manualFile;
        $instance = new \App\Database\Migrations\ManualDependent();
        $this->assertEquals(['App\\Database\\Migrations\\BaseMigration'], $instance->getDependencies());
        
        $this->removeClass('\\App\\Database\\Migrations\\ManualDependent');
        $this->removeClass('\\App\\Database\\Migrations\\'.$baseName);
    }
    
    /**
     * @test
     */
    public function testCreateMigrationWithEnvironments() {
        // Note: DatabaseChangeGenerator doesn't support environments for migrations yet
        // This test is kept for future compatibility
        $this->markTestSkipped('DatabaseChangeGenerator does not support environments for migrations yet');
        
        $name = 'EnvMigration';
        $clazz = '\\App\\Database\\Migrations\\'.$name;
        
        $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
        ], [
            "\n",
            $name,
            "n",
            "y",
            "dev",
            "y",
            "staging",
            "n"
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Database' . DS . 'Migrations' . DS . $name . '.php';
        require_once $filePath;
        $this->assertTrue(class_exists($clazz));
        
        $instance = new $clazz();
        $this->assertInstanceOf(AbstractMigration::class, $instance);
        $this->assertEquals(['dev', 'staging'], $instance->getEnvironments());
        
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function testCreateMigrationWithDefaults() {
        $name = 'DefaultMigration';
        $clazz = '\\App\\Database\\Migrations\\'.$name;
        
        $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
            '--defaults' => ''
        ], [
            $name
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Database' . DS . 'Migrations' . DS . $name . '.php';
        $this->assertTrue(file_exists($filePath));
        require_once $filePath;
        $this->assertTrue(class_exists($clazz));
        
        $this->removeClass($clazz);
    }
}
