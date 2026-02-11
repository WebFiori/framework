<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateTableCommand;

/**
 * Test cases for CreateTableCommand
 *
 * @author Ibrahim
 */
class CreateTableCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateTable00() {
        $className = 'TestTable'.time();

        $output = $this->executeSingleCommand(new CreateTableCommand(), [], [
            $className,
            "\n", // Use default table name
            'n'   // Don't add columns
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter table class name:\n",
            "Enter table name: Enter = '".strtolower($className)."'\n",
            "Add columns to the table?(y/N)\n",
            "Success: Table class created at: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Schema".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateTable01() {
        $className = 'TestTable'.time();

        $output = $this->executeSingleCommand(new CreateTableCommand(), [], [
            '',  // Empty class name - will be rejected
            $className,  // Valid class name
            "\n",  // Use default table name
            'n'    // No columns
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter table class name:\n",
            "Error: Class name cannot be empty.\n",
            "Enter table class name:\n",
            "Enter table name: Enter = '".strtolower($className)."'\n",
            "Add columns to the table?(y/N)\n",
            "Success: Table class created at: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Schema".DIRECTORY_SEPARATOR.$className.".php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateTableWithArgs00() {
        $className = 'TestTable'.time();

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'users'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: Table class created at: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Schema".DIRECTORY_SEPARATOR.$className.".php\n", $output);

        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateTableWithArgs01() {
        $className = 'TestTable'.time();
        $columnsJson = json_encode([
            ['name' => 'id', 'type' => 'INT', 'size' => 11, 'primary' => true, 'autoIncrement' => true],
            ['name' => 'name', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => false],
            ['name' => 'email', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'users',
            '--columns' => $columnsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    /**
     * @test
     */
    public function testCreateTableWithArgs02() {
        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => '',
            '--table-name' => 'test'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertContains("Error: Class name cannot be empty.\n", $output);
    }
    /**
     * @test
     */
    public function testCreateTableWithArgs03() {
        $className = 'TestTable'.time();

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'test_table',
            '--columns' => 'invalid-json'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Error: Invalid JSON format for --columns parameter.\n", $output);
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateTableWithMultipleColumnsViaJson() {
        $className = 'TestTable'.time();
        $columnsJson = json_encode([
            ['name' => 'id', 'type' => 'INT', 'size' => 11, 'primary' => true, 'autoIncrement' => true],
            ['name' => 'name', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => false],
            ['name' => 'email', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => true],
            ['name' => 'created_at', 'type' => 'DATETIME', 'nullable' => false]
        ]);

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'users',
            '--columns' => $columnsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertStringContainsString("Success: Table class created", implode('', $output));
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateTableWithNullableColumn() {
        $className = 'TestTable'.time();
        $columnsJson = json_encode([
            ['name' => 'description', 'type' => 'TEXT', 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'items',
            '--columns' => $columnsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertStringContainsString("Success: Table class created", implode('', $output));
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateTableWithPrimaryKeyNoAutoIncrement() {
        $className = 'TestTable'.time();
        $columnsJson = json_encode([
            ['name' => 'uuid', 'type' => 'VARCHAR', 'size' => 36, 'primary' => true, 'nullable' => false]
        ]);

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'entities',
            '--columns' => $columnsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertStringContainsString("Success: Table class created", implode('', $output));
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
    
    /**
     * @test
     */
    public function testCreateTableWithInteractiveColumnAddition() {
        $className = 'TestTable'.time();

        $output = $this->executeSingleCommand(new CreateTableCommand(), [], [
            $className,
            'users',
            'y',   // Add columns
            'id',  // Column name
            '11',  // INT type (index 11 in getSupportedDataTypes)
            '11',  // Size
            'y',   // Primary key
            'y',   // Auto increment
            'n',   // Not nullable
            ''     // Finish adding columns
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $outputStr = implode('', $output);
        $this->assertStringContainsString('Enter table class name:', $outputStr);
        $this->assertStringContainsString('Add columns to the table?', $outputStr);
        $this->assertStringContainsString('Enter column name', $outputStr);
        $this->assertStringContainsString('Select column type:', $outputStr);
        $this->assertStringContainsString('Success: Table class created', $outputStr);
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);
    }
}
