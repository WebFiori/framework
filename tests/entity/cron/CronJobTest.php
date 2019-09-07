<?php
namespace webfiori\tests\entity\cron;
use PHPUnit\Framework\TestCase;
use webfiori\entity\cron\CronJob;
/**
 * A set of test units for testing the class 'CronJob'.
 *
 * @author Ibrahim
 */
class CronJobTest extends TestCase{
    /**
     * @test
     */
    public function testConstructor00() {
        $job = new CronJob();
        $this->assertEquals('* * * * *',$job->getExpression());
        $this->assertEquals('CRON-JOB',$job->getJobName());
        $this->assertTrue(is_callable($job->getOnExecution()));
        $this->assertTrue($job->isMinute());
        $this->assertTrue($job->isHour());
        $this->assertTrue($job->isDayOfWeek());
        $this->assertTrue($job->isDayOfMonth());
        $this->assertTrue($job->isMonth());
        $arr = $job->getJobDetails();
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $job = new CronJob(null);
        $this->assertEquals('* * * * *',$job->getExpression());
        $this->assertEquals('CRON-JOB',$job->getJobName());
        $this->assertTrue(is_callable($job->getOnExecution()));
        $this->assertTrue($job->isMinute());
        $this->assertTrue($job->isHour());
        $this->assertTrue($job->isDayOfWeek());
        $this->assertTrue($job->isDayOfMonth());
        $this->assertTrue($job->isMonth());
    }
    /**
     * @test
     */
    public function testConstructor02() {
        $job = new CronJob('5,10,20 * * * *');
        $this->assertEquals('5,10,20 * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt00() {
        $job = new CronJob();
        $this->assertTrue($job->dailyAt());
        $this->assertEquals('0 0 * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt01() {
        $job = new CronJob();
        $this->assertTrue($job->dailyAt(13,6));
        $this->assertEquals('6 13 * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt02() {
        $job = new CronJob();
        $this->assertTrue($job->dailyAt(23,59));
        $this->assertEquals('59 23 * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt03() {
        $job = new CronJob();
        $this->assertFalse($job->dailyAt(24,59));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt04() {
        $job = new CronJob();
        $this->assertFalse($job->dailyAt(-1,59));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt05() {
        $job = new CronJob();
        $this->assertFalse($job->dailyAt(0,-1));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt06() {
        $job = new CronJob();
        $this->assertFalse($job->dailyAt(0,60));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn00() {
        $job = new CronJob();
        $this->assertTrue($job->everyMonthOn());
        $this->assertEquals('0 0 1 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn01() {
        $job = new CronJob();
        $this->assertFalse($job->everyMonthOn(0));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn02() {
        $job = new CronJob();
        $this->assertFalse($job->everyMonthOn(32));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn03() {
        $job = new CronJob();
        $this->assertTrue($job->everyMonthOn(31));
        $this->assertEquals('0 0 31 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn04() {
        $job = new CronJob();
        $this->assertTrue($job->everyMonthOn(5,'12:00'));
        $this->assertEquals('0 12 5 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn05() {
        $job = new CronJob();
        $this->assertTrue($job->everyMonthOn(5,'12:000'));
        $this->assertEquals('0 12 5 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn06() {
        $job = new CronJob();
        $this->assertFalse($job->everyMonthOn(5,'12:100'));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn07() {
        $job = new CronJob();
        $this->assertTrue($job->everyMonthOn(5,'012:000'));
        $this->assertEquals('0 12 5 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testOnMonth00() {
        $job = new CronJob();
        $this->assertTrue($job->onMonth());
        $this->assertEquals('0 0 1 1 *',$job->getExpression());
    }
    public function testSetJobName00() {
        $job = new CronJob();
        $job->setJobName('Hello Job');
        $this->assertEquals('Hello Job',$job->getJobName());
        $job->setJobName('  Hello Job  ');
        $this->assertEquals('Hello Job',$job->getJobName());
        $job->setJobName('   ');
        $this->assertEquals('Hello Job',$job->getJobName());
    }
    public function testEveryHoure() {
        $job = new CronJob();
        $job->everyHour();
        $this->assertEquals('0 * * * *',$job->getExpression());
    }
    public function testWeeklyOn00() {
        $job = new CronJob();
        $this->assertTrue($job->weeklyOn());
        $this->assertEquals('0 0 * * 0',$job->getExpression());
    }
}
