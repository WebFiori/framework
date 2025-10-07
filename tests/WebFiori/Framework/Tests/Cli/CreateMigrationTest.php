<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
/**
 * @author Ibrahim
 */
class CreateMigrationTest extends CLITestCase {
    
    protected function tearDown(): void {
        // Clean up only specific migration files created by this test
        $migrationsDir = APP_PATH . DS . 'Database' . DS . 'migrations';
        if (is_dir($migrationsDir)) {
            // Only remove Migration files directly in the migrations directory
            $files = glob($migrationsDir . DS . 'Migration[0-9][0-9][0-9].php');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            // Remove Cool* files created by this test
            $files = glob($migrationsDir . DS . 'Cool*.php');
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
        
        $clazz = '\\App\\Database\\migrations\\'.$name;
        
        $this->assertEquals([
            "Migration namespace: Enter = 'app\database\migrations'\n",
            "Provide a name for the class that will have migration logic:\n",
            'Info: New class was created at "'. APP_PATH .'Database'.DS.'migrations".'."\n",
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
        ], [
            "\n",
            $name,
            "Great One",
            "11"
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    private function getMName() {
        $runner = new SchemaRunner(null);
        $count = count($runner->getChanges());
        if ($count < 10) {
            return 'Migration00'.$count;
        } else if ($count < 100) {
            return 'Migration0'.$count;
        }
        return 'Migration'.$count;
    }
}