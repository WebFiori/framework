<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\DataType;
use WebFiori\Database\MsSql\MSSQLTable;
use WebFiori\Database\MySql\MySQLTable;
use WebFiori\Database\Table;
use WebFiori\File\File;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;

/**
 * Description of CreateTableTest
 *
 * @author Ibrahim
 */
class CreateTableTest extends CLITestCase {
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
        "11: datetime\n",
        "12: time\n",
        "13: money\n",
        "14: bit\n",
        "15: decimal\n",
        "16: float\n",
        "17: boolean\n",
        "18: bool\n",
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
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'table'
        ], [
            'mysql',
            'Cool00Table',
            "\n", // Hit Enter to pick default value (App\database)
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

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Cool00Table'));
        $clazz = '\\App\\Database\\Cool00Table';
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
            "Enter an optional namespace for the class: Enter = 'App\Database'\n",
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
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."database\".\n",
        ]), $output);
    }
    
    /**
     * @test
     */
    public function testCreateTable01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'table'
        ], [
            'mssql',
            'Cool01Table',
            "\n", // Hit Enter to pick default value (App\database)
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

        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\App\\Database\\Cool01Table';
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
            "Enter an optional namespace for the class: Enter = 'App\Database'\n",
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
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."database\".\n",
        ]), $output);
    }
    
    /**
     * @test
     */
    public function testCreateTable02() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'table'
        ], [
            'mysql',
            'Cool02Table',
            "\n", // Hit Enter to pick default value (App\database)
            'cool_table_02',
            "\n", // Hit Enter to pick default value (empty comment)
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'y',
            'id', // Duplicate column name
            'y',
            'name',
            '3',
            '400',
            'n',
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            'The name of the user',
            'n',
            'n',
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Database\\Cool02Table'));
        $clazz = '\\App\\Database\\Cool02Table';
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
            "Enter an optional namespace for the class: Enter = 'App\Database'\n",
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
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."database\".\n",
        ]), $output);
    }
    
}
