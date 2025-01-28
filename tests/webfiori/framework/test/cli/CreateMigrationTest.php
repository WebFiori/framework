<?php
namespace webfiori\framework\test\cli;

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
        $this->assertEquals([
            "\n",
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'migration',
            '--defaults'
        ]));
        $this->assertEquals(-1, $this->getExitCode());
    }
}