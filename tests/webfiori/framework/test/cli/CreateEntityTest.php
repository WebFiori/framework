<?php
namespace webfiori\framework\test\cli;

use app\database\TestTable;
use webfiori\framework\App;

class CreateEntityTest extends CreateTestCase {
    /**
     * @test
     */
    public function testCreateEntity00() {
        $runner = $runner = App::getRunner();
        $runner->setInputs([
            'NeEntity',
            '',
            'y',
            'y',
            'superNewAttr',
            'y',
            'superNewAttr',
            'y',
            'invalid name',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'entity',
            '--table' => TestTable::class
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "We need from you to give us entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Enter attribute name:\n",
            "Success: Attribute successfully added.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",

            "Would you like to add another attribute?(y/N)\n",
            "Generating your entity...\n",
            "Success: Entity class created.\n"
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\entity\\NeEntity'));
        $this->removeClass('\\app\\entity\\NeEntity');
    }
    /**
     * @test
     */
    public function testCreateEntity01() {
        $runner = $runner = App::getRunner();
        $runner->setInputs([
            '1',
            'NewEntity',
            '           ',
            'y',
            'y',
            'superNewAttr',
            'y',
            'superNewAttr',
            'y',
            'invalid name',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'entiy',
            '--table' => TestTable::class
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Warning: The argument --c has invalid value.\n",
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
            "10: Quit. <--\n",
            "We need from you to give us entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Enter attribute name:\n",
            "Success: Attribute successfully added.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",

            "Would you like to add another attribute?(y/N)\n",
            "Generating your entity...\n",
            "Success: Entity class created.\n"
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\entity\\NewEntity'));
        $this->removeClass('\\app\\entity\\NewEntity');
    }
}
