<?php
namespace webfiori\framework\test\cron;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use webfiori\framework\cron\Cron;
use webfiori\framework\cron\CronJob;
use webfiori\framework\cron\JobArgument;
/**
 * A set of test units for testing the class 'CronJob'.
 *
 * @author Ibrahim
 */
class CronJobTest extends TestCase {
    /**
     * @test
     */
    public function testAttributes00() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: Hello&world');
        $job = new CronJob();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('Hello&world');
    }
    /**
     * @test
     */
    public function testAttributes01() {
        $job = new CronJob();
        $job->addExecutionArg('hello');
        $this->assertEquals(1,count($job->getArgsValues()));
        $job->addExecutionArg('Hello');
        $this->assertEquals(2,count($job->getArgsValues()));
    }
    /**
     * @test
     */
    public function testAttributes02() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: hello#world');
        $job = new CronJob();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('hello#world');
    }
    /**
     * @test
     */
    public function testAttributes03() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: hello=x');
        $job = new CronJob();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('hello=x');
    }
    /**
     * @test
     */
    public function testAttributes04() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: ?hello World');
        $job = new CronJob();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('?hello World');
    }
    /**
     * @test
     */
    public function testAttributes05() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: <empty string>');
        $job = new CronJob();
        $job->addExecutionArg('    ');
    }
    /**
     * @test
     */
    public function testAttributes06() {
        $job = new CronJob();
        $job->addExecutionArgs([
            'one' => [
                'description' => 'Arg #1'
            ],
            'new-arg',
            new JobArgument('three', 'The Third Arg')
        ]);
        $this->assertEquals(3,count($job->getArgsValues()));
        $arg1 = $job->getArgument('one');
        $this->assertEquals('one', $arg1->getName());
        $this->assertEquals('Arg #1', $arg1->getDescription());
        
        $arg2 = $job->getArgument('new-arg');
        $this->assertEquals('new-arg', $arg2->getName());
        $this->assertEquals('NO DESCRIPTION', $arg2->getDescription());
        
        $arg3 = $job->getArgument('three');
        $this->assertEquals('three', $arg3->getName());
        $this->assertEquals('The Third Arg', $arg3->getDescription());
        
        $this->assertEquals([
            'one', 'new-arg', 'three'
        ], $job->getExecArgsNames());
    }
    /**
     * @test
     */
    public function testConstructor00() {
        $job = new CronJob();
        $this->assertEquals('* * * * *',$job->getExpression());
        $this->assertEquals('CRON-JOB',$job->getJobName());
        $this->assertTrue(!is_callable($job->getOnExecution()));
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
        $job = new CronJob();
        $this->assertEquals('* * * * *',$job->getExpression());
        $this->assertEquals('CRON-JOB',$job->getJobName());
        $this->assertTrue(!is_callable($job->getOnExecution()));
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
    public function testConstructor03() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'\'.');
        $cron = new CronJob('');
    }
    /**
     * @test
     */
    public function testConstructor04() {
        $cron = new CronJob('0-5,7,15 0-4,8 * * *');
        $this->assertEquals('0-5,7,15 0-4,8 * * *',$cron->getExpression());
    }
    /**
     * @test
     */
    public function testConstructor05() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'0-5,7,15,60 0-4,8 * * *\'.');
        $cron = new CronJob('0-5,7,15,60 0-4,8 * * *');
    }
    /**
     * @test
     */
    public function testConstructor06() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'0-5,7,15 0-4,24,8 * * *\'.');
        $cron = new CronJob('0-5,7,15 0-4,24,8 * * *');
    }
    /**
     * @test
     */
    public function testConstructor07() {
        $cron = new CronJob('15 8 * jan-mar 0,mon,3-6');
        $this->assertEquals('15 8 * jan-mar 0,mon,3-6',$cron->getExpression());
    }
    /**
     * @test
     */
    public function testConstructor08() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/15,*/20,30-35 */2 */3,6,9 jan-mar 0,mon,3-6\'.');
        $cron = new CronJob('*/15,*/20,30-35 */2 */3,6,9 jan-mar 0,mon,3-6');
    }
    /**
     * @test
     */
    public function testConstructor09() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/15,*/20,30-35 */2 * */3 0,mon,3-6\'.');
        $cron = new CronJob('*/15,*/20,30-35 */2 * */3 0,mon,3-6');
    }
    /**
     * @test
     */
    public function testConstructor10() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/15,*/20,30-35 */2 * * */3\'.');
        $cron = new CronJob('*/15,*/20,30-35 */2 * * */3');
    }
    /**
     * @test
     */
    public function testConstructor11() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5,a * * * *\'');
        $cron = new CronJob('5,a * * * *');
    }
    /**
     * @test
     */
    public function testConstructor12() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5, * * * *\'.');
        $cron = new CronJob('5, * * * *');
    }
    /**
     * @test
     */
    public function testConstructor13() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-0 * * * *\'.');
        $cron = new CronJob('5-0 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor14() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'60-60 * * * *\'.');
        $cron = new CronJob('60-60 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor15() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-60 * * * *\'.');
        $cron = new CronJob('5-60 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor16() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 15,a * * *\'');
        $cron = new CronJob('* 15,a * * *');
    }
    /**
     * @test
     */
    public function testConstructor17() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 7, * * *\'.');
        $cron = new CronJob('* 7, * * *');
    }
    /**
     * @test
     */
    public function testConstructor18() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 23-9 * * *\'.');
        $cron = new CronJob('* 23-9 * * *');
    }
    /**
     * @test
     */
    public function testConstructor19() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'24-24 * * * *\'.');
        $cron = new CronJob('24-24 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor20() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 5-24 * * *\'.');
        $cron = new CronJob('* 5-24 * * *');
    }
    /**
     * @test
     */
    public function testConstructor21() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 1-32 * *\'.');
        $cron = new CronJob('* * 1-32 * *');
    }
    /**
     * @test
     */
    public function testConstructor22() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 0-30 * *\'.');
        $cron = new CronJob('* * 0-30 * *');
    }
    /**
     * @test
     */
    public function testConstructor23() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 30-30 * *\'.');
        $cron = new CronJob('* * 30-30 * *');
    }
    /**
     * @test
     */
    public function testConstructor24() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 20-10 * *\'.');
        $cron = new CronJob('* * 20-10 * *');
    }
    /**
     * @test
     */
    public function testConstructor25() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 20, * *\'.');
        $cron = new CronJob('* * 20, * *');
    }
    /**
     * @test
     */
    public function testConstructor26() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 20,a * *\'.');
        $cron = new CronJob('* * 20,a * *');
    }
    /**
     * @test
     */
    public function testConstructor27() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 5-c * * *\'.');
        $cron = new CronJob('* 5-c * * *');
    }
    /**
     * @test
     */
    public function testConstructor28() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-a * * * *\'.');
        $cron = new CronJob('5-a * * * *');
    }
    /**
     * @test
     */
    public function testCron00() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'\'.');
        $cron = new CronJob('');
    }
    /**
     * @test
     */
    public function testCron01() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'11-100 * * * *\'.');
        $cron = new CronJob('11-100 * * * *');
    }
    /**
     * @test
     */
    public function testCron02() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/60 * * * *\'.');
        $cron = new CronJob('*/60 * * * *');
    }
    /**
     * @test
     */
    public function testCron03() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 15-24 * * *\'.');
        $cron = new CronJob('* 15-24 * * *');
    }
    /**
     * @test
     */
    public function testCron04() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* */24 * * *\'.');
        $cron = new CronJob('* */24 * * *');
    }
    /**
     * @test
     */
    public function testCron05() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 25-37 * *\'.');
        $cron = new CronJob('* * 25-37 * *');
    }
    /**
     * @test
     */
    public function testCron06() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 12-13 *\'.');
        $cron = new CronJob('* * * 12-13 *');
    }
    /**
     * @test
     */
    public function testCron07() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 13 *\'.');
        $cron = new CronJob('* * * 13 *');
    }
    /**
     * @test
     */
    public function testCron08() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 12-1 *\'.');
        $cron = new CronJob('* * * 12-1 *');
    }
    /**
     * @test
     */
    public function testCron09() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 5-16 *\'.');
        $cron = new CronJob('* * * 5-16 *');
    }
    /**
     * @test
     */
    public function testCron10() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 0-7\'.');
        $cron = new CronJob('* * * * 0-7');
    }
    /**
     * @test
     */
    public function testCron11() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 0-7\'.');
        $cron = new CronJob('* * * * 0-7');
    }
    /**
     * @test
     */
    public function testCron12() {
        $cron = new CronJob('* * * * 0-6');
        $this->assertEquals('* * * * 0-6',$cron->getExpression());
    }
    /**
     * @test
     */
    public function testCron13() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 7\'.');
        $cron = new CronJob('* * * * 7');
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
    public function testEveryHoure() {
        $job = new CronJob();
        $job->everyHour();
        $this->assertEquals('0 * * * *',$job->getExpression());
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
    public function testExecute00() {
        $job = new CronJob();
        $job->setOnExecution(function()
        {
            //do nothing
        });
        $isExe = $job->exec();
        $this->assertTrue($isExe);
        $this->assertTrue($job->isSuccess());
    }
    /**
     * @test
     */
    public function testExecute01() {
        $job = new CronJob();
        $job->setOnExecution(function()
        {
            return false;
        });
        $isExe = $job->exec();
        $this->assertTrue($isExe);
        $this->assertFalse($job->isSuccess());
    }
    /**
     * @test
     */
    public function testExecute02() {
        $job = new CronJob();
        $job->setOnExecution(function()
        {
            return true;
        });
        $isExe = $job->exec();
        $this->assertTrue($isExe);
        $this->assertTrue($job->isSuccess());
    }
    /**
     * @test
     */
    public function testExecute03() {
        $job = new CronJob();
        $job->setOnExecution(function()
        {
            throw new \Exception();
        });
        $job->exec();
        $this->assertFalse($job->isSuccess());
    }
    /**
     * @test
     */
    public function testExecute04() {
        $job = new CronJob();
        $job->dailyAt(23);
        $job->setOnExecution(function()
        {
        });
        $r = $job->exec();
        $this->assertFalse($r);
        $this->assertFalse($job->isSuccess());
        $r2 = $job->exec(true);
        $this->assertTrue($r2);
        $this->assertTrue($job->isSuccess());
    }
    /**
     * @test
     */
    public function testExecute05() {
        $job = new CronJob();
        $job->dailyAt(23);
        $job->setOnExecution(function()
        {
            return false;
        });
        $job->setOnFailure(function()
        {
            throw new \Exception();
        });
        $r = $job->exec();
        $this->assertFalse($r);
        $this->assertFalse($job->isSuccess());
        $r2 = $job->exec(true);
        $this->assertTrue($r2);
        $this->assertFalse($job->isSuccess());
    }
    /**
     * @test
     */
    public function testOnMonth00() {
        $job = new CronJob();
        $this->assertTrue($job->onMonth());
        $this->assertEquals('0 0 1 1 *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testOnMonth01() {
        $job = new CronJob();
        $this->assertFalse($job->onMonth(0, 3));
        $this->assertTrue($job->onMonth(1, 3));
        $this->assertEquals('0 0 3 1 *',$job->getExpression());
        $this->assertTrue($job->onMonth(2, 3));
        $this->assertEquals('0 0 3 2 *',$job->getExpression());
        $this->assertTrue($job->onMonth(3, 3));
        $this->assertEquals('0 0 3 3 *',$job->getExpression());
        $this->assertTrue($job->onMonth(4, 3));
        $this->assertEquals('0 0 3 4 *',$job->getExpression());
        $this->assertTrue($job->onMonth(5, 3));
        $this->assertEquals('0 0 3 5 *',$job->getExpression());
        $this->assertTrue($job->onMonth(6, 3));
        $this->assertEquals('0 0 3 6 *',$job->getExpression());
        $this->assertTrue($job->onMonth(7, 3));
        $this->assertEquals('0 0 3 7 *',$job->getExpression());
        $this->assertTrue($job->onMonth(8, 3));
        $this->assertEquals('0 0 3 8 *',$job->getExpression());
        $this->assertTrue($job->onMonth(9, 3));
        $this->assertEquals('0 0 3 9 *',$job->getExpression());
        $this->assertTrue($job->onMonth(10, 3));
        $this->assertEquals('0 0 3 10 *',$job->getExpression());
        $this->assertTrue($job->onMonth(11, 3));
        $this->assertEquals('0 0 3 11 *',$job->getExpression());
        $this->assertTrue($job->onMonth(12, 3));
        $this->assertEquals('0 0 3 12 *',$job->getExpression());
        $this->assertFalse($job->onMonth(13, 3));
        $this->assertEquals('0 0 3 12 *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testOnMonth02() {
        $job = new CronJob();
        $this->assertTrue($job->onMonth('feb', 23, '23:00'));
        $this->assertEquals('0 23 23 2 *',$job->getExpression());
        $this->assertFalse($job->onMonth('febx', 23, '23:00'));
        $this->assertFalse($job->onMonth('feb', 32, '23:00'));
        $this->assertFalse($job->onMonth('feb', 0, '23:00'));
        $this->assertFalse($job->onMonth('feb', 23, '24:00'));
        $this->assertFalse($job->onMonth('feb', 23, '23:60'));
    }
    /**
     * @test
     */
    public function testOnMonth03() {
        $job = new CronJob();
        $this->assertTrue($job->onMonth('2', 23, '23:00'));
        $this->assertEquals('0 23 23 2 *',$job->getExpression());
        $this->assertTrue($job->onMonth(2, '20', '10:00'));
        $this->assertEquals('0 10 20 2 *',$job->getExpression());
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
    /**
     * @test
     */
    public function testSetOnExc00() {
        $job = new CronJob();
        $job->setOnFailure(function()
        {
        }, ['1',2]);
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testWeeklyOn00() {
        $job = new CronJob();
        $this->assertTrue($job->weeklyOn());
        $this->assertEquals('0 0 * * 0',$job->getExpression());
    }
    /**
     * @test
     */
    public function testWeeklyOn01() {
        $job = new CronJob();
        $this->assertTrue($job->weeklyOn(1));
        $this->assertEquals('0 0 * * 1',$job->getExpression());
        $this->assertTrue($job->weeklyOn(2));
        $this->assertEquals('0 0 * * 2',$job->getExpression());
        $this->assertTrue($job->weeklyOn(3));
        $this->assertEquals('0 0 * * 3',$job->getExpression());
        $this->assertTrue($job->weeklyOn(4));
        $this->assertEquals('0 0 * * 4',$job->getExpression());
        $this->assertTrue($job->weeklyOn(5));
        $this->assertEquals('0 0 * * 5',$job->getExpression());
        $this->assertTrue($job->weeklyOn(6));
        $this->assertEquals('0 0 * * 6',$job->getExpression());
        $this->assertFalse($job->weeklyOn(7));
        $this->assertEquals('0 0 * * 6',$job->getExpression());
    }
    /**
     * @test
     */
    public function testWeeklyOn02() {
        $job = new CronJob();
        $this->assertTrue($job->weeklyOn('sun','23:59'));
        $this->assertEquals('59 23 * * 0',$job->getExpression());
        $this->assertTrue($job->weeklyOn('Mon','1:05'));
        $this->assertEquals('5 1 * * 1',$job->getExpression());
        $this->assertFalse($job->weeklyOn('Mon','1:60'));
        $this->assertEquals('5 1 * * 1',$job->getExpression());
        $this->assertFalse($job->weeklyOn('Mon','24:05'));
        $this->assertEquals('5 1 * * 1',$job->getExpression());
        $this->assertFalse($job->weeklyOn('Monday','1:05'));
        $this->assertEquals('5 1 * * 1',$job->getExpression());
    }
    /**
     * @test
     */
    public function testWeeklyOn03() {
        $job = new CronJob();
        $this->assertTrue($job->weeklyOn('3','23:00'));
        $this->assertEquals('0 23 * * 3',$job->getExpression());
        $this->assertTrue($job->weeklyOn('6','23:00'));
        $this->assertEquals('0 23 * * 6',$job->getExpression());
    }
    /**
     * @test
     */
    public function testWeeklyOn04() {
        $job = new CronJob();
        $this->assertFalse($job->weeklyOn('7','23:00'));
        $this->assertFalse($job->weeklyOn(7,'23:00'));
    }
    /**
     * @test
     */
    public function testWeeklyOn05() {
        $job = new CronJob();
        $this->assertFalse($job->weeklyOn('0','23:60'));
        $this->assertFalse($job->weeklyOn('0','24:00'));
        $this->assertTrue($job->weeklyOn('0','00:00'));
    }
    /**
     * @test
     */
    public function testIsDayOfMonth00() {
        $job = new CronJob();
        $job->everyMonthOn(1);
        Cron::setDayOfMonth(1);
        $this->assertTrue($job->isDayOfMonth());
        for ($x = 2 ; $x < 31 ; $x++) {
            Cron::setDayOfMonth($x);
            $this->assertFalse($job->isDayOfMonth());
        }
    }
    /**
     * @test
     */
    public function testIsDayOfMonth01() {
        $job = new CronJob('5 4 1-10,25-29 * *');

        $this->assertTrue($job->isDayOfMonth());
        for ($x = 1 ; $x <= 10 ; $x++) {
            Cron::setDayOfMonth($x);
            $this->assertTrue($job->isDayOfMonth());
        }
        for ($x = 11 ; $x <= 24 ; $x++) {
            Cron::setDayOfMonth($x);
            $this->assertFalse($job->isDayOfMonth());
        }
        for ($x = 25 ; $x <= 29 ; $x++) {
            Cron::setDayOfMonth($x);
            $this->assertTrue($job->isDayOfMonth());
        }
    }
    /**
     * @test
     */
    public function testIsDayOfMonth02() {
        $job = new CronJob('5 4 1,3,4,10,29 * *');

        Cron::setDayOfMonth(1);
        $this->assertTrue($job->isDayOfMonth());
        
        Cron::setDayOfMonth(3);
        $this->assertTrue($job->isDayOfMonth());
        
        Cron::setDayOfMonth(4);
        $this->assertTrue($job->isDayOfMonth());
        
        Cron::setDayOfMonth(10);
        $this->assertTrue($job->isDayOfMonth());
        
        Cron::setDayOfMonth(29);
        $this->assertTrue($job->isDayOfMonth());
        
        Cron::setDayOfMonth(11);
        $this->assertFalse($job->isDayOfMonth());
        
        Cron::setDayOfMonth(2);
        $this->assertFalse($job->isDayOfMonth());
    }
    
    /**
     * @test
     */
    public function testIsDayOfWeek00() {
        $job = new CronJob();
        $job->weeklyOn('sun');
        Cron::setDayOfWeek(0);
        $this->assertTrue($job->isDayOfWeek());
        for ($x = 1 ; $x < 7 ; $x++) {
            Cron::setDayOfWeek($x);
            $this->assertFalse($job->isDayOfWeek());
        }
    }
    /**
     * @test
     */
    public function testIsDayOfWeek01() {
        $job = new CronJob('5 4 * * 0-3,5-6');

        for ($x = 0 ; $x <= 3 ; $x++) {
            Cron::setDayOfWeek($x);
            $this->assertTrue($job->isDayOfWeek());
        }
        Cron::setDayOfWeek(4);
        $this->assertFalse($job->isDayOfWeek());
        
        Cron::setDayOfWeek(5);
        $this->assertTrue($job->isDayOfWeek());
        Cron::setDayOfWeek(6);
        $this->assertTrue($job->isDayOfWeek());
    }
    /**
     * @test
     */
    public function testIsDayOfWeek02() {
        $job = new CronJob('5 4 * * 0,3,6');

        Cron::setDayOfWeek(0);
        $this->assertTrue($job->isDayOfWeek());
        
        Cron::setDayOfWeek(3);
        $this->assertTrue($job->isDayOfWeek());
        
        Cron::setDayOfWeek(6);
        $this->assertTrue($job->isDayOfWeek());
        
        Cron::setDayOfWeek(1);
        $this->assertFalse($job->isDayOfWeek());
        
        Cron::setDayOfWeek(5);
        $this->assertFalse($job->isDayOfWeek());
    }
    
     
    /**
     * @test
     */
    public function testIsMinute00() {
        $job = new CronJob();
        $job->dailyAt(0, 25);
        Cron::setMinute(25);
        $this->assertTrue($job->isMinute());
        for ($x = 0 ; $x < 25 ; $x++) {
            Cron::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
        for ($x = 26 ; $x < 59 ; $x++) {
            Cron::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
    }
    /**
     * @test
     */
    public function testIsMinute01() {
        $job = new CronJob('5-10,20-40 4 * * 0-3,5-6');

        for ($x = 0 ; $x <= 4 ; $x++) {
            Cron::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
        for ($x = 5 ; $x <= 10 ; $x++) {
            Cron::setMinute($x);
            $this->assertTrue($job->isMinute());
        }
        for ($x = 11 ; $x <= 19 ; $x++) {
            Cron::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
        for ($x = 20 ; $x <= 40 ; $x++) {
            Cron::setMinute($x);
            $this->assertTrue($job->isMinute());
        }
        for ($x = 41 ; $x <= 59 ; $x++) {
            Cron::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
    }
    /**
     * @test
     */
    public function testIsMinute02() {
        $job = new CronJob('5,20,40,59 4 * * 0,3,6');

        Cron::setMinute(5);
        $this->assertTrue($job->isMinute());
        
        Cron::setMinute(20);
        $this->assertTrue($job->isMinute());
        
        Cron::setMinute(40);
        $this->assertTrue($job->isMinute());
        
        Cron::setMinute(59);
        $this->assertTrue($job->isMinute());
        
        Cron::setMinute(0);
        $this->assertFalse($job->isMinute());
        
        Cron::setMinute(1);
        $this->assertFalse($job->isMinute());
    }
    /**
     * @test
     */
    public function testtestIsHour00() {
        $job = new CronJob();
        $job->everyHour(1);

        for ($x = 0 ; $x < 24 ; $x++) {
            Cron::setHour($x);
            $this->assertTrue($job->isHour());
        }
    }
    /**
     * @test
     */
    public function testtestIsHour01() {
        $job = new CronJob('5 0-2,12-19 1-10,25-29 * *');

        for ($x = 0 ; $x <= 2 ; $x++) {
            Cron::setHour($x);
            $this->assertTrue($job->isHour());
        }
        for ($x = 3 ; $x <= 11 ; $x++) {
            Cron::setHour($x);
            $this->assertFalse($job->isHour());
        }
        for ($x = 12 ; $x <= 19 ; $x++) {
            Cron::setHour($x);
            $this->assertTrue($job->isHour());
        }
        for ($x = 20 ; $x <= 23 ; $x++) {
            Cron::setHour($x);
            $this->assertFalse($job->isHour());
        }
    }
    /**
     * @test
     */
    public function testIsHour02() {
        $job = new CronJob('5 4,8,9,13,16 1,3,4,10,29 * *');

        Cron::setHour(4);
        $this->assertTrue($job->isHour());
        
        Cron::setHour(8);
        $this->assertTrue($job->isHour());
        
        Cron::setHour(9);
        $this->assertTrue($job->isHour());
        
        Cron::setHour(13);
        $this->assertTrue($job->isHour());
        
        Cron::setHour(16);
        $this->assertTrue($job->isHour());
        
        Cron::setHour(11);
        $this->assertFalse($job->isHour());
        
        Cron::setHour(2);
        $this->assertFalse($job->isHour());
    }
}
