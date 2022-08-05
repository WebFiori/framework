<?php

namespace webfiori\framework\test\cli;

use webfiori\framework\WebFioriApp;

/**
 * Description of CreateRESTTest
 *
 * @author Ibrahim
 */
class CreateRESTTest extends CreateTestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'rest'
        ]);
        $runner->setInput([
            '0',
            'SuperUser',
            'app\\entity',
            'y',
            'n',
            "app\\database",
            "super_users",
            "A table to hold super users information.",
            "id",
            "int",
            "11",
            "y",
            "The unique ID of the super user.",
            "n",
            "n"
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'app\database'\n",
            "Enter database table name:\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            "Enter a name for column key:\n",
            "Column data type:",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Done.\n"
        ], $runner->getOutput());
    }
}
