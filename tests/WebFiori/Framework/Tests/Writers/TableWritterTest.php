<?php
namespace WebFiori\Framework\Test\Writers;

use WebFiori\Database\MsSql\MSSQLTable;
use WebFiori\Database\MySql\MySQLColumn;
use WebFiori\Database\MySql\MySQLTable;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Writers\TableClassWriter;
/**
 *
 * @author Ibrahim
 */
class TableWritterTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new TableClassWriter();
        $this->assertEquals('NewTable', $writter->getName());
        $this->assertEquals('App\\Database', $writter->getNamespace());
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
        $writter->setEntityInfo('MyEntity', 'App\\Entity', ROOT_PATH.DS.APP_DIR.DS.'Entity', true);
        $this->assertEquals('CoolTTable', $writter->getName());
        $this->assertEquals('App\\Database', $writter->getNamespace());
        $this->assertEquals('Table', $writter->getSuffix());
        $this->assertEquals([

        ], $writter->getUseStatements());
        $this->assertEquals('MyEntity', $writter->getEntityName());
        $this->assertEquals('App\\Entity', $writter->getEntityNamespace());
        $this->assertEquals(ROOT_PATH.DS.APP_DIR.DS.'Entity', $writter->getEntityPath());
        $this->assertTrue($writter->getTable() instanceof MySQLTable);
        $this->assertFalse($writter->getTable() instanceof MSSQLTable);
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof MySQLTable);
        $this->assertEquals('`new_table`', $clazzObj->getName());
        $this->assertEquals(0, $clazzObj->getColsCount());
        $this->removeClass('App\\Entity\\MyEntity');
    }
    /**
     * @test
     */
    public function test02() {
        $writter = new TableClassWriter();
        $writter->setClassName('CoolT2Table');
        $writter->setTableType('mssql');
        $writter->setEntityInfo('MyEntity', 'App\\Entity', ROOT_PATH.DS.APP_DIR.DS.'Entity', true);
        $this->assertEquals('CoolT2Table', $writter->getName());

        $this->assertFalse($writter->getTable() instanceof MySQLTable);
        $this->assertTrue($writter->getTable() instanceof MSSQLTable);
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof MSSQLTable);
        $this->assertEquals('[new_table]', $clazzObj->getName());
        $this->assertEquals(0, $clazzObj->getColsCount());
        $this->removeClass('App\\Entity\\MyEntity');
    }
    /**
     * @test
     */
    public function test03() {
        $writter = new TableClassWriter();
        $writter->setClassName('CoolT3Table');
        $writter->setTableType('mssql');

        $writter->getTable()->addColumns([
            'col-1' => [],
            'col-2' => [],
            'col-3' => []
        ]);
        $writter->getTable()->setName('super');
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof MSSQLTable);
        $this->assertEquals('[super]', $clazzObj->getName());
        $this->assertEquals(3, $clazzObj->getColsCount());
        $col00 = $clazzObj->getColByKey('col-1');
        $this->assertEquals('mixed', $col00->getDatatype());
        $this->assertEquals(1, $col00->getSize());
        $this->assertNull($col00->getDefault());
        $this->assertFalse($col00->isNull());
        $this->assertFalse($col00->isPrimary());
        $this->assertFalse($col00->isUnique());
    }
    /**
     * @test
     */
    public function test04() {
        $writter = new TableClassWriter();
        $writter->setClassName('CoolT4Table');

        $writter->getTable()->addColumns([
            'col-1' => [],
            'col-2' => [],
            'col-3' => []
        ]);
        $writter->getTable()->setName('super');
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof MySQLTable);
        $this->assertEquals('`super`', $clazzObj->getName());
        $this->assertEquals(3, $clazzObj->getColsCount());
        $col00 = $clazzObj->getColByKey('col-1');
        $this->assertEquals('mixed', $col00->getDatatype());
        $this->assertEquals(1, $col00->getSize());
        $this->assertNull($col00->getDefault());
        $this->assertFalse($col00->isNull());
        $this->assertFalse($col00->isPrimary());
        $this->assertFalse($col00->isUnique());
    }
    /**
     * @test
     */
    public function test05() {
        $writter = new TableClassWriter();
        $writter->setClassName('CoolT5Table');
        $writter->getTable()->setComment('The table that holds user info.');
        $writter->getTable()->addColumns([
            'col-1' => [
                'type' => 'int',
                'primary' => true,
                'comment' => 'The unique identifier of the table.',
                'auto-inc' => true
            ],
            'col-2' => [
                'type' => 'varchar',
                'size' => 300,
                'is-null' => true,
                'default' => 'Hello World!'
            ],
            'col-3' => [
                'type' => 'timestamp',
                'default' => 'current_timestamp'
            ],
            'col-4' => [
                'type' => 'bool',
                'default' => true
            ],
            'col-5' => [
                'type' => 'bool',
                'default' => false
            ],
            'col-6' => [
                'type' => 'decimal',
                'size' => 10,
                'scale' => '4',
                'default' => true
            ]
        ]);
        $writter->getTable()->setName('super');
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof MySQLTable);
        $this->assertEquals('`super`', $clazzObj->getName());
        $this->assertEquals(6, $clazzObj->getColsCount());
        $this->assertEquals('The table that holds user info.', $clazzObj->getComment());

        $col00 = $clazzObj->getColByKey('col-1');
        $this->assertTrue($col00 instanceof MySQLColumn);
        $this->assertEquals('int', $col00->getDatatype());
        $this->assertEquals(1, $col00->getSize());
        $this->assertNull($col00->getDefault());
        $this->assertTrue($col00->isPrimary());
        $this->assertFalse($col00->isNull());
        $this->assertTrue($col00->isUnique());
        $this->assertTrue($col00->isAutoInc());
        $this->assertFalse($col00->isAutoUpdate());
        $this->assertEquals('The unique identifier of the table.', $col00->getComment());


        $col01 = $clazzObj->getColByKey('col-2');
        $this->assertTrue($col01 instanceof MySQLColumn);
        $this->assertEquals('varchar', $col01->getDatatype());
        $this->assertEquals(300, $col01->getSize());
        $this->assertEquals('Hello World!', $col01->getDefault());
        $this->assertFalse($col01->isPrimary());
        $this->assertTrue($col01->isNull());
        $this->assertFalse($col01->isUnique());
        $this->assertFalse($col01->isAutoInc());
        $this->assertFalse($col01->isAutoUpdate());
        $this->assertNull($col01->getComment());

        $col04 = $clazzObj->getColByKey('col-4');
        $this->assertTrue($col04 instanceof MySQLColumn);
        $this->assertEquals('bool', $col04->getDatatype());
        $this->assertFalse($col04->isNull());
        $this->assertFalse($col04->isUnique());
        $this->assertTrue($col04->getDefault());
        $this->assertNull($col04->getComment());

        $col05 = $clazzObj->getColByKey('col-5');
        $this->assertTrue($col05 instanceof MySQLColumn);
        $this->assertEquals('bool', $col05->getDatatype());
        $this->assertFalse($col05->isNull());
        $this->assertFalse($col05->isUnique());
        $this->assertFalse($col05->getDefault());
        $this->assertNull($col05->getComment());

        $col06 = $clazzObj->getColByKey('col-6');
        $this->assertTrue($col06 instanceof MySQLColumn);
        $this->assertEquals('decimal', $col06->getDatatype());
        $this->assertEquals(10, $col06->getSize());
        $this->assertEquals(4, $col06->getScale());
    }
}
