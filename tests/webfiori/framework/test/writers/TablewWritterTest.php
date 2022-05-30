<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\TableClassWriter;
use PHPUnit\Framework\TestCase;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class TablewWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new TableClassWriter();
        $this->assertEquals('NewTable', $writter->getName());
        $this->assertEquals('app\\database', $writter->getNamespace());
        $this->assertEquals('Table', $writter->getSuffix());
        $this->assertEquals([
            
        ], $writter->getUseStatements());
        $this->assertNull($writter->getEntityName());
        $this->assertNull($writter->getEntityNamespace());
        $this->assertNull($writter->getEntityPath());
        $this->assertTrue($writter->getTable() instanceof MySQLTable);
        $this->assertFalse($writter->getTable() instanceof MSSQLTable);
    }
    /**
     * @test
     */
    public function test01() {
        $writter = new TableClassWriter();
        $writter->setClassName('CoolT');
        $writter->setEntityInfo('MyEntity', 'app\\entity', ROOT_DIR.DS.APP_DIR_NAME.DS.'entity', true);
        $this->assertEquals('CoolTTable', $writter->getName());
        $this->assertEquals('app\\database', $writter->getNamespace());
        $this->assertEquals('Table', $writter->getSuffix());
        $this->assertEquals([
            
        ], $writter->getUseStatements());
        $this->assertEquals('MyEntity', $writter->getEntityName());
        $this->assertEquals('app\\entity', $writter->getEntityNamespace());
        $this->assertEquals(ROOT_DIR.DS.APP_DIR_NAME.DS.'entity', $writter->getEntityPath());
        $this->assertTrue($writter->getTable() instanceof MySQLTable);
        $this->assertFalse($writter->getTable() instanceof MSSQLTable);
    }
}
