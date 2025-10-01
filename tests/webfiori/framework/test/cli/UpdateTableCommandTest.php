<?php
namespace webfiori\framework\test\cli;

use WebFiori\Database\Table;
use WebFiori\File\File;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\UpdateTableCommand;

class UpdateTableCommandTest extends CLITestCase {
    public function test00() {
        $output = $this->executeSingleCommand(new UpdateTableCommand(), [
            'webfiori',
            'update-table',
        ], [
            '   ', // Invalid class name
            'ok\\y\\Super', // Invalid class name
            'app\\database\\TestTable',
            '0',
            'new-col',
            '1',
            '9',
            'n',
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            'Cool new column.',
            'y',
            'ModifiedO',
            "\n" // Hit Enter to pick default value (app\database)
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "Error: Class not found.\n",
            "Enter database table class name (include namespace):\n",
            "Error: Class not found.\n",
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: mixed <--\n",
            "1: int\n",
            "2: char\n",
            "3: varchar\n",
            "4: timestamp\n",
            "5: tinyblob\n",
            "6: blob\n",
            "7: mediumblob\n",
            "8: longblob\n",
            "9: datetime\n",
            "10: text\n",
            "11: mediumtext\n",
            "12: decimal\n",
            "13: double\n",
            "14: float\n",
            "15: boolean\n",
            "16: bool\n",
            "17: bit\n",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Would you like to update same class or create a copy with the update?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Success: Column added.\n",
        ], $output);

        $clazz = '\\app\\database\\ModifiedOTable';
        $this->assertTrue(class_exists($clazz));
        $file = new File(ROOT_PATH.$clazz.'.php');
        $file->remove();
        $obj = new $clazz();
        $this->assertTrue($obj instanceof Table);
        $col = $obj->getColByKey('new-col');
        $this->assertEquals('int', $col->getDatatype());
        $this->assertEquals(9, $col->getSize());
        $this->assertEquals('Cool new column.', $col->getComment());
    }
    
    /**
     * @test
     */
    public function test01() {
        $output = $this->executeSingleCommand(new UpdateTableCommand(), [
            'webfiori',
            'update-table',
        ], [
            'app\\database\\TestTable',
            '2',
            'id',
            'user-id',
            'int',
            '10',
            'n',
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            'Cool modifiyed column.',
            'y',
            'Modified',
            "\n" // Hit Enter to pick default value (app\database)
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Which column would you like to update?\n",
            "0: id\n",
            "Enter a new name for column key: Enter = 'id'\n",
            "Select column data type:\n",
            "0: mixed <--\n",
            "1: int\n",
            "2: char\n",
            "3: varchar\n",
            "4: timestamp\n",
            "5: tinyblob\n",
            "6: blob\n",
            "7: mediumblob\n",
            "8: longblob\n",
            "9: datetime\n",
            "10: text\n",
            "11: mediumtext\n",
            "12: decimal\n",
            "13: double\n",
            "14: float\n",
            "15: boolean\n",
            "16: bool\n",
            "17: bit\n",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Would you like to update same class or create a copy with the update?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Success: Column updated.\n",
        ], $output);
        
        $clazz = '\\app\\database\\ModifiedTable';
        $this->assertTrue(class_exists($clazz));
        $file = new File(ROOT_PATH.$clazz.'.php');
        $file->remove();
        $obj = new $clazz();
        $this->assertTrue($obj instanceof Table);
        $col = $obj->getColByKey('user-id');
        $this->assertEquals('int', $col->getDatatype());
        $this->assertEquals(10, $col->getSize());
        $this->assertEquals('Cool modifiyed column.', $col->getComment());
    }

    public function test02() {
        $output = $this->executeSingleCommand(new UpdateTableCommand(), [
            'webfiori',
            'update-table',
        ], [
            'app\\database\\Test2Table',
            '4',
            '0',
            'y',
            'Modified2',
            "\n" // Hit Enter to pick default value (app\database)
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Select the key that you would like to remove:\n",
            "0: user_id_fk\n",
            "Would you like to update same class or create a copy with the update?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Success: Table updated.\n",
        ], $output);
        
        $clazz = '\\app\\database\\Modified2Table';
        $this->assertTrue(class_exists($clazz));
        $file = new File(ROOT_PATH.$clazz.'.php');
        $file->remove();
        $obj = new $clazz();
        $this->assertTrue($obj instanceof Table);
    }
}
