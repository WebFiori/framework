<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\database\ConnectionInfo;
use webfiori\database\migration\MigrationsRunner;
use webfiori\framework\writers\DatabaseMigrationWriter;

/**
 * @author Ibrahim
 */
class DatabaseMigrationWriterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = new MigrationsRunner(APP_PATH.DS.'database'.DS.'migrations', '\\app\\database\\migrations', null);
        $writter = new DatabaseMigrationWriter($runner);
        $this->assertEquals('Migration000', $writter->getName());
        $this->assertEquals('app\\database\\migrations', $writter->getNamespace());
        $this->assertEquals('', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\database\Database",
            "webfiori\database\migration\AbstractMigration",
        ], $writter->getUseStatements());
    }
    
}
