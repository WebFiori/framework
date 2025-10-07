<?php
namespace webfiori\framework\test\cli;

use app\database\TestTable;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\CreateCommand;

class CreateEntityTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateEntity00() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create',
            '--c' => 'entity',
            '--table' => TestTable::class
        ], [
            'NeEntity',
            "\n", // Hit Enter to pick default value (app\entity)
            'y',
            'y',
            'superNewAttr',
            'y',
            'superNewAttr', // Duplicate attribute name
            'y',
            'invalid name', // Invalid attribute name
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
        $this->assertTrue(class_exists('\\app\\entity\\NeEntity'));
        $this->removeClass('\\app\\entity\\NeEntity');
    }
    
    /**
     * @test
     */
    public function testCreateEntity01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create',
            '--c' => 'entiy', // Invalid command value
            '--table' => TestTable::class
        ], [
            '1',
            'NewEntity',
            '           ', // Invalid namespace (spaces only)
            'y',
            'y',
            'superNewAttr',
            'y',
            'superNewAttr', // Duplicate attribute name
            'y',
            'invalid name', // Invalid attribute name
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
            "10: Database migration.\n",
            "11: Quit. <--\n",
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
        ], $output);
        $this->assertTrue(class_exists('\\app\\entity\\NewEntity'));
        $this->removeClass('\\app\\entity\\NewEntity');
    }
}
