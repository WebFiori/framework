<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\CreateCommand;

/**
 * Description of CreateCommandTest
 *
 * @author Ibrahim
 */
class CreateCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreate00() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create'
        ], [
            '11',
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Database migration.\n",
            "11: Quit. <--\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testCreate01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create'
        ], [
            "\n", // Hit Enter to pick default value (quit)
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Database migration.\n",
            "11: Quit. <--\n",
        ], $output);
    }
}
