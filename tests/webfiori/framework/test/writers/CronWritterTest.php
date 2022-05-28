<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\CronJobClassWriter;
use PHPUnit\Framework\TestCase;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class CronWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new CronJobClassWriter();
        $this->assertEquals('NewJob', $writter->getName());
        $this->assertEquals('app\\jobs', $writter->getNamespace());
        $this->assertEquals('Job', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\cron\\AbstractJob",
            "webfiori\\framework\\cron\\CronEmail",
            "webfiori\\framework\\cron\\Cron",
        ], $writter->getUseStatements());
        $this->assertEquals('No Description', $writter->getJobDescription());
        $this->assertEquals(0, count($writter->getJob()->getArguments()));
    }
}
