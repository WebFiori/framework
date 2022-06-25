<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\ArrayInputStream;
use PHPUnit\Framework\TestCase;
use webfiori\framework\exceptions\ArrayIndexOutOfBoundsException;
/**
 * Description of ArrayInputStreamTest
 *
 * @author Ibrahim
 */
class ArrayInputStreamTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $stream = new ArrayInputStream([
            'one',
            'two'
        ]);
        $this->assertEquals('', $stream->read(1000));
        $this->assertEquals('one', $stream->readLine());
        $this->assertEquals('two', $stream->readLine());
    }
    /**
     * @test
     */
    public function test01() {
        $this->expectException(ArrayIndexOutOfBoundsException::class);
        $this->expectExceptionMessage('Reached end of stream while trying to read line number 3');
        $stream = new ArrayInputStream([
            'one',
            'two'
        ]);
        $this->assertEquals('', $stream->read(1000));
        $this->assertEquals('one', $stream->readLine());
        $this->assertEquals('two', $stream->readLine());
        $stream->readLine();
    }
}
