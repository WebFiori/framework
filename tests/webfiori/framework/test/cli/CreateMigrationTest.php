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
    public function testCreateMigration01() {
        $name = 'CoolMigration';
        
        $clazz = '\\app\\database\\migrations\\'.$name;
        
        $this->assertEquals([
            "Migration namespace: Enter = 'app\database\migrations'\n",
            "Provide a name for the class that will have migration logic:\n",
            'Info: New class was created at "'. APP_PATH .'database'.DS.'migrations".'."\n",
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