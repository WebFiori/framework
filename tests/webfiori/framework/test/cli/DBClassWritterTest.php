<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use tables\UsersInfoTable;
use webfiori\framework\cli\writers\DBClassWriter;
/**
 * Description of DBClassWritterTest
 *
 * @author Ibrahim
 */
class DBClassWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $table = new UsersInfoTable();
        $mapper = $table->getEntityMapper();
        $mapper->setEntityName('CoolUser');
        $mapper->setNamespace('webfiori\\entity');
        $writter = new DBClassWriter('UserDBClass', 'webfiori\\db', $table);
        $writter->writeClass();
        $this->assertTrue(true);
    }

}
