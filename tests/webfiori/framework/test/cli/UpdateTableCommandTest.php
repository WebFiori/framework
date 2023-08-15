<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\database\Table;
use webfiori\file\File;
use webfiori\framework\App;

class UpdateTableCommandTest extends TestCase {
    public function test00() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInputs([
            '   ',
            'ok\\y\\Super',
            'app\\database\\TestTable',
            '0',
            'new-col',
            '1',
            '9',
            'n',
            'n',
            '',
            'n',
            'Cool new column.',
            'y',
            'ModifiedO',
            ''
        ]);


        $this->assertEquals(0, $runner->start());
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
        ], $runner->getOutput());

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
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInputs([
            'app\\database\\TestTable',
            '2',
            'id',
            'user-id',
            'int',
            '10',
            'n',
            'n',
            '',
            'n',
            'Cool modifiyed column.',
            'y',
            'Modified',
            ''
        ]);


        $this->assertEquals(0, $runner->start());
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
        ], $runner->getOutput());
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
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInputs([
            'app\\database\\Test2Table',
            '4',
            '0',
            'y',
            'Modified2',
            ''
        ]);


        $this->assertEquals(0, $runner->start());
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
        ], $runner->getOutput());
        $clazz = '\\app\\database\\Modified2Table';
        $this->assertTrue(class_exists($clazz));
        $file = new File(ROOT_PATH.$clazz.'.php');
        $file->remove();
        $obj = new $clazz();
        $this->assertTrue($obj instanceof Table);
    }

    public function test03() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInputs([
            'app\\database\\TestTable',
            '4',
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Info: Selected table has no foreign keys.\n",
        ], $runner->getOutput());
    }

    public function test04() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInputs([
            'app\\database\\TestTable',
            '1',
            'app\\database\\Test2Table',
            'new_fk',
            '0',
            'n',
            '0',
            '0',
            '0',
            'y',
            'Modified3',
            ''
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Enter the name of the referenced table class (with namespace):\n",
            "Enter a name for the foreign key:\n",
            "Select column #1:\n",
            "0: id\n",
            "Would you like to add another column to the foreign key?(y/N)\n",
            "Select the column that will be referenced by the column 'id':\n",
            "0: user-id\n",
            "Choose on update condition:\n",
            "0: cascade\n",
            "1: restrict <--\n",
            "2: set null\n",
            "3: set default\n",
            "4: no action\n",
            "Choose on delete condition:\n",
            "0: cascade\n",
            "1: restrict <--\n",
            "2: set null\n",
            "3: set default\n",
            "4: no action\n",
            "Would you like to update same class or create a copy with the update?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Success: Foreign key added.\n"
        ], $runner->getOutput());
        $clazz = '\\app\\database\\Modified3Table';
        $this->assertTrue(class_exists($clazz));
        $file = new File(ROOT_PATH.$clazz.'.php');
        $file->remove();
        $obj = new $clazz();
        $this->assertTrue($obj instanceof Table);
        $fk = $obj->getForeignKey('new_fk');
        $this->assertTrue($fk instanceof \webfiori\database\ForeignKey);
        $this->assertTrue($fk->getSource() instanceof \app\database\Test2Table);
        $col1 = $fk->getOwnerCols()['id'];
        $this->assertEquals('`id`', $col1->getName());
        $col2 = $fk->getSourceCols()['user-id'];
        $this->assertEquals('`user_id`', $col2->getName());
    }
    /**
     * @test
     * @depends test01
     */
    public function test05() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInputs([
            'app\\database\\TestTable',
            '3',
            '0',
            'y',
            'ModifiedX',
            ''
        ]);


        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Which column would you like to drop?\n",
            "0: id\n",
            "Would you like to update same class or create a copy with the update?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Success: Column dropped.\n",
        ], $runner->getOutput());
        $clazz = '\\app\\database\\ModifiedXTable';
        $this->assertTrue(class_exists($clazz));
        $file = new File(ROOT_PATH.$clazz.'.php');
        $file->remove();
        $obj = new $clazz();
        $this->assertTrue($obj instanceof Table);
        $this->assertFalse($obj->hasColumn('user-id'));
    }
}
