<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\Schema\AbstractMigration;
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
        DatabaseMigrationWriter::resetCounter();
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $clazz = "\\app\\database\\migrations\\Migration000";
        $this->removeClass($clazz);
        $runner = new SchemaRunner(null);
        $writter = new DatabaseMigrationWriter($runner);
        $this->assertEquals('Migration000', $writter->getName());
        $this->assertEquals('app\\database\\migrations', $writter->getNamespace());
        $this->assertEquals('', $writter->getSuffix());
        $this->assertEquals([
            "WebFiori\Database\Database",
            "WebFiori\Database\Schema\AbstractMigration",
        ], $writter->getUseStatements());
        $writter->writeClass();
        
        $this->assertTrue(class_exists($clazz));
        $runner->register($clazz);
            $allClasses[] = $clazz;
        $migrations = $runner->getChanges();
        $this->assertEquals(1, count($migrations));
        $m00 = $migrations[0];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('app\\database\\migrations\\Migration000', $m00->getName());
        $this->removeClass($clazz);
        $runner = new SchemaRunner(null);
    }
    /**
     * @test
     */
    public function test01() {
        DatabaseMigrationWriter::resetCounter();
        $runner = new SchemaRunner(null);
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $writter = new DatabaseMigrationWriter($runner);
        $writter->setClassName('MyMigration');
        $this->assertEquals('MyMigration', $writter->getName());
        $this->assertEquals('app\\database\\migrations', $writter->getNamespace());

        $writter->writeClass();
        $clazz = "\\app\\database\\migrations\\MyMigration";
        $this->assertTrue(class_exists($clazz));
        $runner->register($clazz);
            $allClasses[] = $clazz;
        $migrations = $runner->getChanges();
        $this->assertEquals(1, count($migrations));
        $m00 = $migrations[0];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('app\\database\\migrations\\MyMigration', $m00->getName());
        $this->removeClass($clazz);
        $runner = new SchemaRunner(null);
    }
    /**
     * @test
     */
    public function test02() {
        DatabaseMigrationWriter::resetCounter();
        $runner = new SchemaRunner(null);
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $writter = new DatabaseMigrationWriter($runner);
        $this->assertEquals('Migration000', $writter->getName());
        $writter->writeClass();
        $clazz = "\\app\\database\\migrations\\Migration000";
        $this->assertTrue(class_exists($clazz));
        $runner->register($clazz);
            $allClasses[] = $clazz;
        $runner2 = new SchemaRunner(null);
        $runner2->register($clazz);
        $migrations = $runner2->getChanges();
        $this->assertEquals(1, count($migrations));
        $m00 = $migrations[0];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('app\\database\\migrations\\Migration000', $m00->getName());
        
        $writter2 = new DatabaseMigrationWriter($runner2);
        $this->assertEquals('Migration001', $writter2->getName());
        $writter2->writeClass();
        $clazz2 = "\\app\\database\\migrations\\Migration001";
        $this->assertTrue(class_exists($clazz));
        $runner->register($clazz);
            $allClasses[] = $clazz;
        $runner3 = new SchemaRunner(null);
        $runner3->register($clazz);
        $runner3->register($clazz2);
        $migrations2 = $runner3->getChanges();
        $this->assertEquals(2, count($migrations2));
        $m01 = $migrations2[1];
        $this->assertTrue($m00 instanceof AbstractMigration);
        $this->assertEquals('app\\database\\migrations\\Migration001', $m01->getName());
        $this->removeClass($clazz);
        $runner = new SchemaRunner(null);
        $this->removeClass($clazz2);
    }
    /**
     * @test
     */
    public function test03() {
        DatabaseMigrationWriter::resetCounter();
        $runner = new SchemaRunner(null);
        $path = APP_PATH.DS.'database'.DS.'migrations';
        $ns = '\\app\\database\\migrations';
        $allClasses = [];
        for ($x = 0 ; $x < 110 ; $x++) {
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
        $runner->register($clazz);
            $allClasses[] = $clazz;
            $xRunner = new SchemaRunner(null);
            foreach ($allClasses as $cls) {
                $xRunner->register($cls);
            }
            
            $migrations = $xRunner->getChanges();
            $this->assertEquals($x + 1, count($migrations));
            $m = $migrations[$x];
            $this->assertTrue($m instanceof AbstractMigration);
            $this->assertEquals("app\\database\\migrations\\" . $name, $m->getName());
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
