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
        $this->assertEquals("Testing the class 'File'.\n",$file->getRawData());
    }
}