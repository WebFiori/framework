<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\ArrayOutputStream;
use PHPUnit\Framework\TestCase;
/**
 * Description of ArrayInputStreamTest
 *
 * @author Ibrahim
 */
class ArrayOutputStreamTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $stream = new ArrayOutputStream();
        $this->assertEquals([], $stream->getOutputArray());
        $stream->println('Hello');
        $this->assertEquals([
            "Hello\n"
        ], $stream->getOutputArray());
        $stream->prints(' World!');
        $this->assertEquals([
            "Hello\n",
            " World!",
        ], $stream->getOutputArray());
        $stream->println('Good');
        $this->assertEquals([
            "Hello\n",
            " World!Good\n",
        ], $stream->getOutputArray());
        $stream->reset();
        $this->assertEquals([], $stream->getOutputArray());
    }
}
