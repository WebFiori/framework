<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
/**
 * Description of TestCreateCommand
 *
 * @author Ibrahim
 */
class CreateCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreate00() {
        $runner = App::getRunner();
        $runner->setInputs([
            '11',
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $this->assertEquals(0, $runner->start());
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
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testCreate01() {
        $runner = $runner = App::getRunner();
        $runner->setInputs([
            '',
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $this->assertEquals(0, $runner->start());
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
        ], $runner->getOutput());
    }
}
