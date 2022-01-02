<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CLI;

/**
 * Description of CLITest
 *
 * @author Ibrahim
 */
class CLITest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $this->assertNotNull(CLI::getInputStream());
        $this->assertNotNull(CLI::getOutputStream());
    }
}
