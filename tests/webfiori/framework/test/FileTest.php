<?php
namespace webfiori\framework\test;

use PHPUnit\Framework\TestCase;
use webfiori\framework\File;
use webfiori\framework\exceptions\FileException;
use webfiori\http\Response;
use webfiori\json\Json;
/**
 * A test class for testing the class 'webfiori\framework\File'.
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
        $file->setId(100);
        $this->assertEquals(100, $file->getID());
        return $file;
    }
    /**
     * @test
     */
    public function test01() {
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $this->assertEquals('text-file.txt',$file->getName());
        $this->assertEquals(ROOT_DIR.DS.'tests'.DS.'entity',$file->getPath());
        $this->assertEquals(-1,$file->getID());
        $this->assertNull($file->getRawData());
        $this->assertEquals('text/plain',$file->getFileMIMEType());

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
        $this->expectExceptionMessage("No data is set to write.");
        $file->write();
    }
    /**
     * @test
     */
    public function testRead00() {
        $file = new File('not-exist.txt', ROOT_DIR);
        $this->expectException(FileException::class);
        $this->expectExceptionMessage("File not found: '".$file->getAbsolutePath()."'");
        $file->read();
    }
    /**
     * @test
     */
    public function testRead02() {
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->read(0, $file->getSize());
        $this->assertEquals('Testing the class \'File\'.', $file->getRawData());
    }
    /**
     * @test
     */
    public function testRead03() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Reached end of file while trying to read 26 byte(s).');
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->read(0, $file->getSize() + 1);
    }
    /**
     * @test
     */
    public function testRead04() {
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('Reached end of file while trying to read 6 byte(s).');
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->read(20, $file->getSize() + 1);
    }
    /**
     * @test
     */
    public function testRead05() {
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->read(20, $file->getSize());
        $this->assertEquals('ile\'.', $file->getRawData());
    }
    /**
     * @test
     */
    public function testRead06() {
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->read(2, $file->getSize());
        $this->assertEquals('sting the class \'File\'.', $file->getRawData());
    }
    /**
     * @test
     */
    public function testRead07() {
        $file = new File('text-file.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->read(2, 4);
        $this->assertEquals('st', $file->getRawData());
    }
    /**
     * @test
     * @depends testRead00
     */
    public function removeTest() {
        $file = new File(ROOT_DIR.'/not-exist.txt');
        $this->assertFalse($file->remove());
    }
    /**
     * @depends test07
     */
    public function testWrite01() {
        $file = new File('hello.txt', ROOT_DIR);
        $file->setRawData('b');
        $file->write(true, true);
        $file->read();
        $this->assertEquals('b', $file->getRawData());
        $file->setRawData('Hello.');
        $file->write(false);
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
        $j = $file->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{'
                . '"id":-1,'
                . '"mime":"text\/plain",'
                . '"name":"'.$file->getName().'",'
                . '"directory":"'.Json::escapeJSONSpecialChars($file->getPath()).'",'
                . '"sizeInBytes":12,'
                . '"sizeInKBytes":0.01171875,'
                . '"sizeInMBytes":1.1444091796875E-5'
                . '}',$j.'');
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
    /**
     * @test
     */
    public function viewTest00() {
        $file = new File('super.txt');
        $file->setRawData('Hello world!');
        $file->view();
        $this->assertEquals('Hello world!', Response::getBody());
        $this->assertEquals([
            'accept-ranges' => [
                'bytes'
            ],
            'content-type' => [
                'text/plain'
            ],
            'content-length' => [
                $file->getSize()
            ],
            'content-disposition' => [
                'inline; filename="super.txt"'
            ]
        ], Response::getHeaders());
        Response::clear();
        $file->view(true);
        $this->assertEquals([
            'accept-ranges' => [
                'bytes'
            ],
            'content-type' => [
                'text/plain'
            ],
            'content-length' => [
                $file->getSize()
            ],
            'content-disposition' => [
                'attachment; filename="super.txt"'
            ]
        ], Response::getHeaders());
        Response::clear();
    }
    /**
     * @test
     */
    public function viewTest01() {
        $file = new File('text-file-2.txt',ROOT_DIR.DS.'tests'.DS.'entity');
        $file->view();
        $this->assertEquals('Testing the class \'File\'.', Response::getBody());
        $this->assertEquals([
            'accept-ranges' => [
                'bytes'
            ],
            'content-type' => [
                'text/plain'
            ],
            'content-length' => [
                $file->getSize()
            ],
            'content-disposition' => [
                'inline; filename="text-file-2.txt"'
            ]
        ], Response::getHeaders());
        Response::clear();
    }
}
