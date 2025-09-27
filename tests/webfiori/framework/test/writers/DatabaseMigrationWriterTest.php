<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\migration\AbstractMigration;
use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\File\File;
use webfiori\framework\writers\DatabaseMigrationWriter;

/**
 * @author Ibrahim
 */
class DatabaseMigrationWriterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $clazz = "\\app\\database\\migrations\\Migration000";
        $this->removeClass($clazz);
        $runner = new MigrationsRunner($path, $ns, null);
        $writter = new DatabaseMigrationWriter($runner);
        $this->assertEquals('Migration000', $writter->getName());
        $this->assertEquals('app\\database\\migrations', $writter->getNamespace());
        $this->assertEquals('', $writter->getSuffix());
        $this->assertEquals([
            "WebFiori\Database\Database",
            "WebFiori\Database\migration\AbstractMigration",
        ], $writter->getUseStatements());
        $writter->writeClass();
        
        $this->assertTrue(class_exists($clazz));
        $runner = new MigrationsRunner($path, $ns, null);
        $migrations = $runner->getMigrations();
        $this->assertEquals(1, count($migrations));
        $m00 = $migrations[0];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('Migration000', $m00->getName());
        $this->assertEquals(0, $m00->getOrder());
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function test01() {
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $runner = new MigrationsRunner($path, $ns, null);
        $writter = new DatabaseMigrationWriter($runner);
        $writter->setClassName('MyMigration');
        $writter->setMigrationName('A test migration.');
        $writter->setMigrationOrder(3);
        $this->assertEquals('MyMigration', $writter->getName());
        $this->assertEquals('app\\database\\migrations', $writter->getNamespace());

        $writter->writeClass();
        $clazz = "\\app\\database\\migrations\\MyMigration";
        $this->assertTrue(class_exists($clazz));
        $runner = new MigrationsRunner($path, $ns, null);
        $migrations = $runner->getMigrations();
        $this->assertEquals(1, count($migrations));
        $m00 = $migrations[0];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('A test migration.', $m00->getName());
        $this->assertEquals(3, $m00->getOrder());
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function test02() {
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $runner = new MigrationsRunner($path, $ns, null);
        $writter = new DatabaseMigrationWriter($runner);
        $this->assertEquals('Migration000', $writter->getName());
        $writter->writeClass();
        $clazz = "\\app\\database\\migrations\\Migration000";
        $this->assertTrue(class_exists($clazz));
        $runner2 = new MigrationsRunner($path, $ns, null);
        $migrations = $runner2->getMigrations();
        $this->assertEquals(1, count($migrations));
        $m00 = $migrations[0];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('Migration000', $m00->getName());
        $this->assertEquals(0, $m00->getOrder());
        
        $writter2 = new DatabaseMigrationWriter($runner2);
        $this->assertEquals('Migration001', $writter2->getName());
        $writter2->writeClass();
        $clazz2 = "\\app\\database\\migrations\\Migration001";
        $this->assertTrue(class_exists($clazz));
        $runner3 = new MigrationsRunner($path, $ns, null);
        $migrations2 = $runner3->getMigrations();
        $this->assertEquals(2, count($migrations2));
        $m01 = $migrations2[1];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('Migration001', $m01->getName());
        $this->assertEquals(1, $m01->getOrder());
        $this->removeClass($clazz);
        $this->removeClass($clazz2);
    }
    /**
     * @test
     */
    public function test03() {
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        for ($x = 0 ; $x < 110 ; $x++) {
            $runner = new MigrationsRunner($path, $ns, null);
            $writter = new DatabaseMigrationWriter($runner);
            if ($x < 10) {
                $name = 'Migration00'.$x;
            } else if ($x < 100) {
                $name = 'Migration0'.$x;
            } else {
                $name = 'Migration'.$x;
            }
            $this->assertEquals($name, $writter->getName());
            $writter->writeClass();
            $clazz = "\\app\\database\\migrations\\".$name;
            $this->assertTrue(class_exists($clazz));
            $xRunner = new MigrationsRunner($path, $ns, null);
            
            $migrations = $xRunner->getMigrations();
            $this->assertEquals($x + 1, count($migrations));
            $m = $migrations[$x];
            $this->assertTrue($m instanceof AbstractMigration);
            $this->assertEquals($name, $m->getName());
            $this->assertEquals($x, $m->getOrder());
        }
        foreach ($migrations as $m) {
            $this->removeClass("\\app\\database\\migrations\\".$m->getName());
        }
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_PATH.$classPath.'.php');
        $file->remove();
    }
}
