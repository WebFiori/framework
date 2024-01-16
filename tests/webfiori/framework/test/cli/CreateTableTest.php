<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\database\DataType;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\Table;
use webfiori\file\File;
use webfiori\framework\App;
/**
 * Description of CreateTableTest
 *
 * @author Ibrahim
 */
class CreateTableTest extends TestCase {
    const MSSQL_COLS_TYPES = [
        "Enter a name for column key:\n",
        "Column data type:\n",
        "0: mixed <--\n",
        "1: int\n",
        "2: bigint\n",
        "3: varchar\n",
        "4: nvarchar\n",
        "5: char\n",
        "6: nchar\n",
        "7: binary\n",
        "8: varbinary\n",
        "9: date\n",
        "10: datetime2\n",
        "11: time\n",
        "12: money\n",
        "13: bit\n",
        "14: decimal\n",
        "15: float\n",
        "16: boolean\n",
        "17: bool\n",
    ];
    const MYSQL_COLS_TYPES = [
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
    ];
    /**
     * @test
     */
    public function testCreateTable00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mysql',
            'Cool00Table',
            '',
            'cool_table_00',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertTrue(class_exists('\\app\\database\\Cool00Table'));
        $clazz = '\\app\\database\\Cool00Table';
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MySQLTable);
        $this->assertEquals('`cool_table_00`', $testObj->getName());
        $this->assertEquals('This is the first cool table that was created using CLI.', $testObj->getComment());
        $this->assertEquals(1, $testObj->getColsCount());
        $this->assertEquals([
            'id'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '`id`'
        ], $testObj->getColsNames());

        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_00_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
        ]), $runner->getOutput());
    }
    /**
     * @test
     */
    public function testCreateTable01() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mssql',
            'Cool01Table',
            '',
            'cool_table_01',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            'n',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $clazz = '\\app\\database\\Cool01Table';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MSSQLTable);
        $this->assertEquals('[cool_table_01]', $testObj->getName());
        $this->assertEquals('This is the first cool table that was created using CLI.', $testObj->getComment());
        $this->assertEquals(1, $testObj->getColsCount());
        $this->assertEquals([
            'id'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '[id]'
        ], $testObj->getColsNames());

        $col = $testObj->getColByKey('id');
        $this->assertFalse($col->isIdentity());

        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_01_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MSSQL_COLS_TYPES, [
            "Is this column an identity column?(y/N)\n",
            "Is this column primary?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
        ]), $runner->getOutput());
    }
    /**
     * @test
     */
    public function testCreateTable02() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mysql',
            'Cool02Table',
            '',
            'cool_table_02',
            '',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'y',

            'id',
            'y',

            'name',
            '3',//type
            '400',//size
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'The name of the user',//optional comment
            'n',

            'n',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $output = $runner->getOutput();
        $this->assertTrue(class_exists('\\app\\database\\Cool02Table'));
        $clazz = '\\app\\database\\Cool02Table';
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MySQLTable);
        $this->assertEquals('`cool_table_02`', $testObj->getName());
        $this->assertNull($testObj->getComment());
        $this->assertEquals(2, $testObj->getColsCount());
        $this->assertEquals([
            'id',
            'name'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '`id`',
            '`name`'
        ], $testObj->getColsNames());

        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_02_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Enter a name for column key:\n",
            "Warning: The table already has a key with name 'id'.\n",
            "Would you like to add another column?(y/N)\n",
            ], self::MYSQL_COLS_TYPES, [

            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",

            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
        ]), $output);
    }
    /**
     * @test
     */
    public function testCreateTable03() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mysql',
            'Cool01Table',
            '',
            'Cool03Table',
            '',
            'cool_table_03',
            '',

            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'y',

            'name',
            '3',//type
            '400',//size
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'The name of the user',//optional comment
            'y',

            'creation-date',
            '4',//type
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'No Comment.',//optional comment
            'n',
            'n',

            'n',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $output = $runner->getOutput();
        $this->assertTrue(class_exists('\\app\\database\\Cool03Table'));
        $clazz = '\\app\\database\\Cool03Table';
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MySQLTable);
        $this->assertEquals('`cool_table_03`', $testObj->getName());
        $this->assertNull($testObj->getComment());
        $this->assertEquals(3, $testObj->getColsCount());
        $this->assertEquals([
            'id',
            'name',
            'creation-date'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '`id`',
            '`name`',
            '`creation_date`'
        ], $testObj->getColsNames());

        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Error: A class in the given namespace which has the given name was found.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_03_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ], self::MYSQL_COLS_TYPES, [
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
        ]), $output);
    }
    /**
     * @test
     */
    public function testCreateTable04() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mysql',
            'CoolWithEntity00Table',
            '',
            'cool_table_entity_00',
            '',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'y',
            'MySuperCoolEntity00',
            '',
            'y',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $output = $runner->getOutput();
        $this->removeClass('\\app\\database\\CoolWithEntity00Table');
        $clazz = '\\app\\entity\\MySuperCoolEntity00';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);

        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_with_entity_00_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
            'Info: Entity class was created at "'.ROOT_PATH.DS.'app'.DS."entity\".\n",
        ]), $output);
    }
    /**
     * @test
     */
    public function testCreateTable05() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mysql',
            'Cool05Table',
            '',
            'cool_table_05',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $clazz = '\\app\\database\\Cool05Table';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $instance = new $clazz();
        $instance instanceof Table;
        $this->assertTrue($instance->hasColumn('id'));
        $idCol = $instance->getColByKey('id');
        $this->assertEquals(DataType::INT, $idCol->getDatatype());
        $this->assertTrue($idCol->isPrimary());
        $this->assertTrue($idCol->isUnique());

        return $clazz;
    }
    /**
     * @test
     * @depends testCreateTable05
     */
    public function testCreateTable06($refTable) {
        $runner = App::getRunner();
        $runner->setInputs([
            'mysql',
            'Cool06Table',
            '',
            'cool_table_06',
            '',
            'id',
            '1',
            '11',
            'y',
            'n',
            'The unique ID of the cool thing.',
            'y',
            'ref-id',
            '1',
            '11',
            'y',
            'n',
            'no comment',
            'n',
            'y',
            $refTable,
            'ref_table_fk',
            'ref-id',
            'n',
            '0',
            '0',
            '0',
            'n',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $result = $runner->start();
        $this->assertEquals(0, $result);
        $output = $runner->getOutput();
        $clazz = '\\app\\database\\Cool06Table';
        $this->assertTrue(class_exists($clazz));

        $this->removeClass($clazz);
        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_06_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ], self::MYSQL_COLS_TYPES, [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Enter the name of the referenced table class (with namespace):\n",
            "Enter a name for the foreign key:\n",
            "Select column #1:\n",
            "0: id\n",
            "1: ref-id\n",
            "Would you like to add another column to the foreign key?(y/N)\n",
            "Select the column that will be referenced by the column 'ref-id':\n",
            "0: id\n",
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
            "Success: Foreign key added.\n",
            "Would you like to add another foreign key?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
        ]), $output);

        $instance = new $clazz();
        $instance instanceof Table;
        $this->assertEquals(1, $instance->getForeignKeysCount());
        $fk = $instance->getForeignKey('ref_table_fk');
        $this->assertEquals($refTable, '\\'.get_class($fk->getSource()));
        $this->assertEquals($clazz, '\\'.get_class($fk->getOwner()));
    }
    /**
     * @test
     */
    public function testCreateTable07() {
        $runner = App::getRunner();
        $runner->setInputs([
            'mssql',
            'Cool011Table',
            '',
            'cool_table_01',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'y',

            'age',
            '1',
            'n',
            'n',
            '',
            'n',
            'The age of the cool thing.',
            'n',
            'n',
            'n'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'table'
        ]);
        $this->assertEquals(0, $runner->start());
        $clazz = '\\app\\database\\Cool011Table';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MSSQLTable);
        $this->assertEquals('[cool_table_01]', $testObj->getName());
        $this->assertEquals('This is the first cool table that was created using CLI.', $testObj->getComment());
        $this->assertEquals(2, $testObj->getColsCount());
        $this->assertEquals([
            'id',
            'age'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '[id]',
            '[age]'
        ], $testObj->getColsNames());

        $col = $testObj->getColByKey('id');
        $this->assertTrue($col->isIdentity());

        $this->assertEquals(array_merge([
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Enter database table name: Enter = 'cool_011_table'\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ], self::MSSQL_COLS_TYPES, [
            "Is this column an identity column?(y/N)\n",
            "Is this column primary?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ], self::MSSQL_COLS_TYPES, [
                "Is this column primary?(y/N)\n",
                "Is this column unique?(y/N)\n",
                "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
                "Can this column have null values?(y/N)\n",
                "Enter your optional comment about the column:\n",
                "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."database\".\n",
        ]), $runner->getOutput());
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_PATH.$classPath.'.php');
        $file->remove();
    }
}
