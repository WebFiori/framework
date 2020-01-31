<?php
namespace webfiori\tests\entity\cron;
use PHPUnit\Framework\TestCase;
use webfiori\entity\cron\Cron;
/**
 * Description of CronTest
 *
 * @author Ibrahim
 */
class CronTest extends TestCase{
    /**
     * @test
     */
    public function testGetJob00() {
        $this->assertNull(Cron::getJob('Not Exist'));
    }
    /**
     * @test
     */
    public function testGetJob01() {
        Cron::createJob('* * * * *', 'Job 1');
        Cron::createJob('15 * * * *', 'Job 2');
        Cron::createJob('16 * * * *', 'Job 3');
        Cron::createJob('17 * * * *', 'Job 4');
        $job = Cron::getJob('Job 3');
        $this->assertEquals('16 * * * *',$job->getExpression());
    }
    public function testWeeklyJob00() {
        Cron::weeklyJob('6-23:00', 'Job X', function(){
            
        });
        $job = Cron::getJob('Job X');
        $this->assertNotNull($job);
    }
}
