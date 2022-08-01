<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use tables\UserInfoTable;
use webfiori\framework\writers\DBClassWriter;

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
        $table = new UserInfoTable();
        $mapper = $table->getEntityMapper();
        $mapper->setEntityName('CoolUser');
        $mapper->setNamespace('webfiori\\entity');
        $writter = new DBClassWriter('UserDBClass', 'webfiori\\db', $table);
        $writter->writeClass();
        $this->assertTrue(true);
    }

}
