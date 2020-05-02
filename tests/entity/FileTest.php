<?php
namespace webfiori\tests\entity;

use PHPUnit\Framework\TestCase;
use webfiori\entity\File;
use webfiori\entity\exceptions\FileException;
use jsonx\JsonX;
/**
 * A test class for testing the class 'webfiori\entity\File'.
 *
 * @author Ibrahim
 */
class FileTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $file = new File();
        $this->assertEquals('',$file->getName());
        $this->assertEquals('',$file->getPath());
        $this->assertEquals(-1,$file->getID());
        $this->assertNull($file->getRawData());
        $this->assertEquals('application/octet-stream',$file->getFileMIMEType());

        return $file;
    }
    /**
     * @test
     */
    public function test01() {
        $file = new File('text-file.txt',ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity');
        $this->assertEquals('text-file.txt',$file->getName());
        $this->assertEquals(ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity',$file->getPath());
        $this->assertEquals(-1,$file->getID());
        $this->assertNull($file->getRawData());
        $this->assertEquals('application/octet-stream',$file->getFileMIMEType());

        return $file;
    }
    /**
     * @test
     */
    public function test02() {
        $file = new File();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File name cannot be empty string.');
        $file->read();
    }
    /**
     * @test
     */
    public function test03() {
        $file = new File('hello.txt');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Path cannot be empty string.');
        $file->read();
    }
    /**
     * @test
     */
    public function test04() {
        $file = new File('hello.txt', ROOT_DIR);
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("File not found: '".$file->getAbsolutePath()."'");
        $file->read();
    }
    /**
     * @test
     */
    public function test05() {
        $file = new File();
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File name cannot be empty string.');
        $file->write();
    }
    /**
     * @test
     */
    public function test06() {
        $file = new File('hello.txt');
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Path cannot be empty string.');
        $file->write();
    }
    /**
     * @test
     */
    public function test07() {
        $file = new File('hello.txt', ROOT_DIR);
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("File not found: '".$file->getAbsolutePath()."'");
        $file->write();
    }
    /**
     * @depends test07
     */
    public function testWrite01() {
        $file = new File('hello.txt', ROOT_DIR);
        $file->write(true, true);
        $file->read();
        $this->assertEquals('', $file->getRawData());
        $file->setRawData('Hello.');
        $file->write();
        $file->read();
        $this->assertEquals('Hello.', $file->getRawData());
        $file->setRawData('World.');
        $file->write(false);
        $this->assertEquals('World.', $file->getRawData());
        $file->setRawData('Hello.');
        $file->write();
        $file->read();
        $this->assertEquals('World.Hello.', $file->getRawData());
        return $file;
    }
    /**
     * @test
     * @param File $file
     * @depends testWrite01
     */
    public function toJson00($file) {
        $this->assertEquals('{'
                . '"id":-1, '
                . '"mime":"text\/plain", '
                . '"name":"'.$file->getName().'", '
                . '"path":"'.JsonX::escapeJSONSpecialChars($file->getPath()).'", '
                . '"sizeInBytes":12, '
                . '"sizeInKBytes":0.01171875, '
                . '"sizeInMBytes":1.1444091796875E-5'
                . '}',$file->toJSON().'');
        return $file;
    }
    /**
     * @test
     * @depends toJson00
     * @param File $file
     */
    public function testRemove00($file) {
        $this->assertTrue($file->remove());
        $this->assertFalse(file_exists($file->getAbsolutePath()));
    }
    
}
