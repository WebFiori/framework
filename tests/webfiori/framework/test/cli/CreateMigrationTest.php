<?php
namespace webfiori\framework\test\cli;

use WebFiori\Database\Schema\SchemaRunner;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\CreateCommand;
/**
 * @author Ibrahim
 */
class CreateMigrationTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateMigration00() {
        $name = $this->getMName();
        $clazz = '\\app\\database\\migrations\\'.$name;
        $order = $this->getOrder();
        
        $this->assertEquals([
            'Info: New class was created at "'. APP_PATH .'database'.DS.'migrations".'."\n",
            "Info: Migration Name: $name\n",
            "Info: Migration Order: $order\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
            '--defaults'
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateMigration01() {
        $name = 'CoolMigration';
        $defaultName = $this->getMName();
        $clazz = '\\app\\database\\migrations\\'.$name;
        $order = $this->getOrder();
        
        $this->assertEquals([
            "Migration namespace: Enter = 'app\database\migrations'\n",
            "Provide an optional name for the class that will have migration logic:\n",
            "Enter an optional name for the migration: Enter = '$defaultName'\n",
            "Enter an optional execution order for the migration: Enter = '$order'\n",
            'Info: New class was created at "'. APP_PATH .'database'.DS.'migrations".'."\n",
            "Info: Migration Name: Great One\n",
            "Info: Migration Order: 11\n"
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
    private function getOrder() {
        $runner = new SchemaRunner(null);
        return count($runner->getChanges());
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