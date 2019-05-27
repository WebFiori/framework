<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\File;

/**
 * A test class for testing the class 'webfiori\entity\File'.
 *
 * @author Ibrahim
 */
class FileTest extends TestCase{
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
     * @depends test00
     * @param File $file
     */
    public function testRead00($file) {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File absolute path is invalid.');
        $file->read();
    }
    /**
     * @test
     * @depends test01
     * @param File $file
     */
    public function testRead01($file) {
        $file->read();
        $this->assertEquals('text/plain',$file->getFileMIMEType());
        $this->assertEquals("Testing the class 'File'.",$file->getRawData());
    }
    /**
     * @test
     */
    public function testRead02() {
        $file = new File('text-file.txt','\\'.ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity\\');
        $this->assertEquals(ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity',$file->getPath());
        $file->read();
        $this->assertEquals('text/plain',$file->getFileMIMEType());
        fprintf(STDERR, $file->getRawData());
        $this->assertEquals("Testing the class 'File'.",$file->getRawData());
    }
    /**
     * @test
     * @depends test00
     * @param File $file
     */
    public function testRead03() {
        $file = new File('text-file-2.txt','\\'.ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity\\');
        $this->expectException(\Exception::class);
        $file->read();
    }
    /**
     * @test
     * @depends test00
     * @param File $file
     */
    public function testRead04() {
        $file = new File('private.txt','\\'.ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity\\');
        $this->expectException(\Exception::class);
        $file->read();
    }
    /**
     * @test
     */
    public function testWrite00() {
        $f = new File();
        $f->setRawData('Hello World!');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File absolute path is invalid.');
        $f->write();
    }
    /**
     * @test
     */
    public function testWrite01() {
        $f = new File('write-test.txt');
        $f->setRawData('Hello World!');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File absolute path is invalid.');
        $f->write();
    }
    /**
     * @test
     */
    public function testWrite02() {
        $f = new File('write-test.txt',ROOT_DIR);
        $f->setRawData('Hello World!');
        $f->write();
        $f2 = new File('write-test.txt',ROOT_DIR);
        $f2->read();
        $this->assertEquals('Hello World!',$f2->getRawData());
    }
    /**
     * @test
     */
    public function testWrite03() {
        $f = new File();
        $f->setRawData('Hello World Again.');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File name cannot be empty string.');
        $f->write('');
    }
    /**
     * @test
     */
    public function testWrite04() {
        $f = new File('write-test-2.txt');
        $f->setRawData('Hello World Again.');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Path cannot be empty string.');
        $f->write('');
    }
    /**
     * @test
     */
    public function testWrite05() {
        $f = new File('write-test-2.txt');
        $f->setRawData('Hello World Again.');
        $f->write(ROOT_DIR);
        $f2 = new File('write-test-2.txt',ROOT_DIR);
        $f2->read();
        $this->assertEquals('Hello World Again.',$f2->getRawData());
    }
    /**
     * @test
     */
    public function toStringTest00() {
        $f = new File();
        $this->assertEquals('{"id":-1, "mime":"application\/octet-stream", "name":"", "path":"", "size-in-bytes":0, "size-in-kbytes":0, "size-in-mbytes":0}',$f.'');
    }
    /**
     * @test
     */
    public function toStringTest01() {
        $f = new File('text-file.txt','\\'.ROOT_DIR.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'entity\\');
        $f->read();
        $this->assertEquals('{"id":-1, "mime":"text\/plain", "name":"text-file.txt", '
                . '"path":"'.\jsonx\JsonX::escapeJSONSpecialChars($f->getPath()).'", "size-in-bytes":25, "size-in-kbytes":0.0244140625, "size-in-mbytes":2.3841857910156E-5}',$f.'');
    }
}