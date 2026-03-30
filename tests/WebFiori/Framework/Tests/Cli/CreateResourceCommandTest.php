<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateResourceCommand;

/**
 * Test cases for CreateResourceCommand.
 *
 * @author Ibrahim
 */
class CreateResourceCommandTest extends CLITestCase {

    private function cleanupResource(string $name) {
        $this->removeClass('\\App\\Domain\\'.$name);
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$name.'Table');
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$name.'Repository');
        $this->removeClass('\\App\\Apis\\'.$name.'Service');
    }

    /**
     * @test
     * Covers: exec() happy path with all args — all 4 files created,
     *         mapTypeToDataType() int/string/float/bool branches,
     *         primary+autoIncrement and size options, nullable options
     */
    public function testCreateResourceWithArgs() {
        $name = 'Product'.time();
        $props = json_encode([
            ['name' => 'id',    'type' => 'int',    'nullable' => false, 'primary' => true, 'autoIncrement' => true],
            ['name' => 'title', 'type' => 'string', 'nullable' => false, 'primary' => false, 'size' => 100],
            ['name' => 'price', 'type' => 'float',  'nullable' => true,  'primary' => false],
            ['name' => 'active','type' => 'bool',   'nullable' => false, 'primary' => false],
        ]);

        $output = $this->executeMultiCommand([
            CreateResourceCommand::class,
            '--name'       => $name,
            '--table'      => 'products',
            '--id-field'   => 'id',
            '--properties' => $props,
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: \xE2\x9C\x93 Created entity: ".APP_PATH."Domain".DIRECTORY_SEPARATOR.$name.".php\n", $output);
        $this->assertContains("Success: \xE2\x9C\x93 Created table: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Schema".DIRECTORY_SEPARATOR.$name."Table.php\n", $output);
        $this->assertContains("Success: \xE2\x9C\x93 Created repository: ".APP_PATH."Infrastructure".DIRECTORY_SEPARATOR."Repository".DIRECTORY_SEPARATOR.$name."Repository.php\n", $output);
        $this->assertContains("Success: \xE2\x9C\x93 Created service: ".APP_PATH."Apis".DIRECTORY_SEPARATOR.$name."Service.php\n", $output);

        $this->cleanupResource($name);
    }

    /**
     * @test
     * Covers: getProperties() invalid JSON branch → returns [] → exec() returns -1
     */
    public function testCreateResourceWithInvalidJson() {
        $output = $this->executeMultiCommand([
            CreateResourceCommand::class,
            '--name'       => 'Order'.time(),
            '--table'      => 'orders',
            '--id-field'   => 'id',
            '--properties' => 'not-valid-json',
        ]);

        $this->assertContains("Error: Invalid JSON format for --properties parameter.\n", $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: exec() empty properties array → "At least one property" error, returns -1
     */
    public function testCreateResourceWithNoProperties() {
        $output = $this->executeMultiCommand([
            CreateResourceCommand::class,
            '--name'       => 'Empty'.time(),
            '--table'      => 'empties',
            '--id-field'   => 'id',
            '--properties' => '[]',
        ]);

        $this->assertContains("Error: At least one property is required.\n", $output);
        $this->assertEquals(-1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: getResourceName() interactive, getTableName() default (pluralised),
     *         getIdField() default, getProperties() interactive loop —
     *         int+primary+autoIncrement, string+size, float, bool, then finish
     */
    public function testCreateResourceInteractive() {
        $name = 'Widget'.time();

        $output = $this->executeSingleCommand(new CreateResourceCommand(), [], [
            $name,   // resource name
            '',      // table name — accept default (widgets)
            '',      // id field  — accept default (id)
            // property 1: int, primary, auto-increment
            'id',    'int',    'n', 'y', 'y',
            // property 2: string with size
            'label', 'string', 'n', 'n', '150',
            // property 3: float, nullable
            'score', 'float',  'y', 'n',
            // property 4: bool
            'active','bool',   'n', 'n',
            '',      // finish properties
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: \xE2\x9C\x93 Created entity: ".APP_PATH."Domain".DIRECTORY_SEPARATOR.$name.".php\n", $output);

        $this->cleanupResource($name);
    }

    /**
     * @test
     * Covers: mapTypeToDataType() default branch (unknown type → VARCHAR)
     */
    public function testCreateResourceWithUnknownType() {
        $name = 'Misc'.time();
        $props = json_encode([
            ['name' => 'data', 'type' => 'unknown', 'nullable' => false, 'primary' => false],
        ]);

        $output = $this->executeMultiCommand([
            CreateResourceCommand::class,
            '--name'       => $name,
            '--table'      => 'miscs',
            '--id-field'   => 'data',
            '--properties' => $props,
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertContains("Success: \xE2\x9C\x93 Created entity: ".APP_PATH."Domain".DIRECTORY_SEPARATOR.$name.".php\n", $output);

        $this->cleanupResource($name);
    }
}
