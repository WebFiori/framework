<?php
namespace webfiori\tests\entity;

use PHPUnit\Framework\TestCase;
use webfiori\framework\DatabaseSchema;
/**
 * A test class for testing the class 'webfiori\framework\DatabaseSchema'.
 *
 * @author Ibrahim
 */
class DatabaseSchemaTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $schema = DatabaseSchema::get();
        //$schema->add('webfiori\tests\entity\TestQuery_1', 20);
        $this->assertEquals("create database if not exists hello_world;\n"
                ."use hello_world;\n",$schema->getCreateDatabaseStatement('hello_world'));
    }
}
