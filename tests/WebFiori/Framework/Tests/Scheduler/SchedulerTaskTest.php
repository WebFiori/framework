<?php
namespace WebFiori\Framework\Test\Scheduler;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Exceptions\InvalidCRONExprException;
use WebFiori\Framework\Privilege;
use WebFiori\Framework\Scheduler\BaseTask;
use WebFiori\Framework\Scheduler\TaskArgument;
use WebFiori\Framework\Scheduler\TasksManager;
/**
 *
 * @author Ibrahim
 */
class SchedulerTaskTest extends TestCase {
    /**
     * @test
     */
    public function testAttributes00() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: Hello&world');
        $job = new BaseTask();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('Hello&world');
    }
    /**
     * @test
     */
    public function testAttributes01() {
        $job = new BaseTask();
        $job->addExecutionArg('hello');
        $this->assertEquals(1,count($job->getArgsValues()));
        $job->addExecutionArg('Hello');
        $this->assertEquals(2,count($job->getArgsValues()));
        $this->assertNull($job->getArgValue('not-exist'));
    }
    /**
     * @test
     */
    public function testAttributes02() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: hello#world');
        $job = new BaseTask();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('hello#world');
    }
    /**
     * @test
     */
    public function testAttributes03() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: hello=x');
        $job = new BaseTask();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('hello=x');
    }
    /**
     * @test
     */
    public function testAttributes04() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: ?hello World');
        $job = new BaseTask();
        $this->assertEquals(0,count($job->getArgsValues()));
        $job->addExecutionArg('?hello World');
    }
    /**
     * @test
     */
    public function testAttributes05() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: <empty string>');
        $job = new BaseTask();
        $job->addExecutionArg('    ');
    }
    /**
     * @test
     */
    public function testAttributes06() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid argument type. Expected 'string' or 'WebFiori\\Framework\\Scheduler\\TaskArgument'");
        $job = new BaseTask();
        $job->addExecutionArg(new Privilege());
    }
    /**
     * @test
     */
    public function testAttributes07() {
        $job = new BaseTask();
        $job->addExecutionArgs([
            'one' => [
                'description' => 'Arg #1'
            ],
            'new-arg',
            new TaskArgument('three', 'The Third Arg')
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
        $job = new BaseTask();
        $this->assertEquals('* * * * *',$job->getExpression());
        $this->assertEquals('SCHEDULER-TASK',$job->getTaskName());
        $this->assertTrue(!is_callable($job->getOnExecution()));
        $this->assertTrue($job->isMinute());
        $this->assertTrue($job->isHour());
        $this->assertTrue($job->isDayOfWeek());
        $this->assertTrue($job->isDayOfMonth());
        $this->assertTrue($job->isMonth());
        $arr = $job->getTaskDetails();
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $job = new BaseTask();
        $this->assertEquals('* * * * *',$job->getExpression());
        $this->assertEquals('SCHEDULER-TASK',$job->getTaskName());
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
        $job = new BaseTask('5,10,20 * * * *');
        $this->assertEquals('5,10,20 * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testConstructor03() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'\'.');
        $task = new BaseTask('');
    }
    /**
     * @test
     */
    public function testConstructor04() {
        $task = new BaseTask('0-5,7,15 0-4,8 * * *');
        $this->assertEquals('0-5,7,15 0-4,8 * * *',$task->getExpression());
    }
    /**
     * @test
     */
    public function testConstructor05() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'0-5,7,15,60 0-4,8 * * *\'.');
        $task = new BaseTask('0-5,7,15,60 0-4,8 * * *');
    }
    /**
     * @test
     */
    public function testConstructor06() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'0-5,7,15 0-4,24,8 * * *\'.');
        $task = new BaseTask('0-5,7,15 0-4,24,8 * * *');
    }
    /**
     * @test
     */
    public function testConstructor07() {
        TasksManager::setMinute(33);
        TasksManager::setDayOfWeek(4);
        TasksManager::setMonth(5);
        TasksManager::setHour(6);
        $task = new BaseTask('15 8 * jan-mar 0,mon,3-5');
        $this->assertEquals('15 8 * jan-mar 0,mon,3-5',$task->getExpression());
        $this->assertTrue($task->isDayOfWeek());
        $this->assertFalse($task->isMinute());
        $this->assertFalse($task->isHour());
        $this->assertFalse($task->isMonth());
        $this->assertTrue($task->isDayOfMonth());

        $this->assertequals('{'
        .'"name":"SCHEDULER-TASK",'
        .'"expression":"15 8 * jan-mar 0,mon,3-5",'
        .'"args":[],'
        .'"description":"NO DESCRIPTION",'
        .'"is_time":false,'
        .'"time":{'
        .'"is_minute":false,'
        .'"is_day_of_week":true,'
        .'"is_month":false,'
        .'"is_hour":false,'
        .'"is_day_of_month":true'
        .'}'
        .'}', $task->toJSON().'');
    }
    /**
     * @test
     */
    public function testConstructor08() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/15,*/20,30-35 */2 */3,6,9 jan-mar 0,mon,3-6\'.');
        $task = new BaseTask('*/15,*/20,30-35 */2 */3,6,9 jan-mar 0,mon,3-6');
    }
    /**
     * @test
     */
    public function testConstructor09() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/15,*/20,30-35 */2 * */3 0,mon,3-6\'.');
        $task = new BaseTask('*/15,*/20,30-35 */2 * */3 0,mon,3-6');
    }
    /**
     * @test
     */
    public function testConstructor10() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/15,*/20,30-35 */2 * * */3\'.');
        $task = new BaseTask('*/15,*/20,30-35 */2 * * */3');
    }
    /**
     * @test
     */
    public function testConstructor11() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5,a * * * *\'');
        $task = new BaseTask('5,a * * * *');
    }
    /**
     * @test
     */
    public function testConstructor12() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5, * * * *\'.');
        $task = new BaseTask('5, * * * *');
    }
    /**
     * @test
     */
    public function testConstructor13() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-0 * * * *\'.');
        $task = new BaseTask('5-0 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor14() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'60-60 * * * *\'.');
        $task = new BaseTask('60-60 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor15() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-60 * * * *\'.');
        $task = new BaseTask('5-60 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor16() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 15,a * * *\'');
        $task = new BaseTask('* 15,a * * *');
    }
    /**
     * @test
     */
    public function testConstructor17() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 7, * * *\'.');
        $task = new BaseTask('* 7, * * *');
    }
    /**
     * @test
     */
    public function testConstructor18() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 23-9 * * *\'.');
        $task = new BaseTask('* 23-9 * * *');
    }
    /**
     * @test
     */
    public function testConstructor19() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'24-24 * * * *\'.');
        $task = new BaseTask('24-24 * * * *');
    }
    /**
     * @test
     */
    public function testConstructor20() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 5-24 * * *\'.');
        $task = new BaseTask('* 5-24 * * *');
    }
    /**
     * @test
     */
    public function testConstructor21() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 1-32 * *\'.');
        $task = new BaseTask('* * 1-32 * *');
    }
    /**
     * @test
     */
    public function testConstructor22() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 0-30 * *\'.');
        $task = new BaseTask('* * 0-30 * *');
    }
    /**
     * @test
     */
    public function testConstructor23() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 30-30 * *\'.');
        $task = new BaseTask('* * 30-30 * *');
    }
    /**
     * @test
     */
    public function testConstructor24() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 20-10 * *\'.');
        $task = new BaseTask('* * 20-10 * *');
    }
    /**
     * @test
     */
    public function testConstructor25() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 20, * *\'.');
        $task = new BaseTask('* * 20, * *');
    }
    /**
     * @test
     */
    public function testConstructor26() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 20,a * *\'.');
        $task = new BaseTask('* * 20,a * *');
    }
    /**
     * @test
     */
    public function testConstructor27() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 5-c * * *\'.');
        $task = new BaseTask('* 5-c * * *');
    }
    /**
     * @test
     */
    public function testConstructor28() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-a * * * *\'.');
        $task = new BaseTask('5-a * * * *');
    }
    /**
     * @test
     */
    public function testConstructor29() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5-7,8- * * * *\'.');
        $task = new BaseTask('5-7,8- * * * *');
    }
    /**
     * @test
     */
    public function testConstructor30() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5/,8- * * * *\'.');
        $task = new BaseTask('5/,8- * * * *');
    }
    /**
     * @test
     */
    public function testConstructor31() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'5/b,8- * * * *\'.');
        $task = new BaseTask('5/b,8- * * * *');
    }
    /**
     * @test
     */
    public function testDailyAt00() {
        $job = new BaseTask();
        $this->assertTrue($job->dailyAt());
        $this->assertEquals('0 0 * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt01() {
        $job = new BaseTask();
        $this->assertTrue($job->dailyAt(13,6));
        $this->assertEquals('6 13 * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt02() {
        $job = new BaseTask();
        $this->assertTrue($job->dailyAt(23,59));
        $this->assertEquals('59 23 * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt03() {
        $job = new BaseTask();
        $this->assertFalse($job->dailyAt(24,59));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt04() {
        $job = new BaseTask();
        $this->assertFalse($job->dailyAt(-1,59));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt05() {
        $job = new BaseTask();
        $this->assertFalse($job->dailyAt(0,-1));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testDailyAt06() {
        $job = new BaseTask();
        $this->assertFalse($job->dailyAt(0,60));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryHoure() {
        $job = new BaseTask();
        $job->everyHour();
        $this->assertEquals('0 * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn00() {
        $job = new BaseTask();
        $this->assertTrue($job->everyMonthOn());
        $this->assertEquals('0 0 1 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn01() {
        $job = new BaseTask();
        $this->assertFalse($job->everyMonthOn(0));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn02() {
        $job = new BaseTask();
        $this->assertFalse($job->everyMonthOn(32));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn03() {
        $job = new BaseTask();
        $this->assertTrue($job->everyMonthOn(31));
        $this->assertEquals('0 0 31 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn04() {
        $job = new BaseTask();
        $this->assertTrue($job->everyMonthOn(5,'12:00'));
        $this->assertEquals('0 12 5 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn05() {
        $job = new BaseTask();
        $this->assertTrue($job->everyMonthOn(5,'12:000'));
        $this->assertEquals('0 12 5 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn06() {
        $job = new BaseTask();
        $this->assertFalse($job->everyMonthOn(5,'12:100'));
        $this->assertEquals('* * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testEveryMonthOn07() {
        $job = new BaseTask();
        $this->assertTrue($job->everyMonthOn(5,'012:000'));
        $this->assertEquals('0 12 5 * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testExecute00() {
        $job = new BaseTask();
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
        $job = new BaseTask();
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
        $job = new BaseTask();
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
        $job = new BaseTask();
        $job->setOnExecution(function()
        {
            throw new Exception();
        });
        $job->exec();
        $this->assertFalse($job->isSuccess());
    }
    /**
     * @test
     */
    public function testExecute04() {
        $job = new BaseTask();
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
        $job = new BaseTask();
        $job->dailyAt(23);
        $job->setOnExecution(function()
        {
            return false;
        });
        $job->setOnFailure(function()
        {
            throw new Exception();
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
    public function testIsDayOfMonth00() {
        $job = new BaseTask();
        $job->everyMonthOn(1);
        TasksManager::setDayOfMonth(1);
        $this->assertTrue($job->isDayOfMonth());

        for ($x = 2 ; $x < 31 ; $x++) {
            TasksManager::setDayOfMonth($x);
            $this->assertFalse($job->isDayOfMonth());
        }
    }
    /**
     * @test
     */
    public function testIsDayOfMonth01() {
        $job = new BaseTask('5 4 1-10,25-29 * *');

        for ($x = 1 ; $x <= 10 ; $x++) {
            TasksManager::setDayOfMonth($x);
            $this->assertTrue($job->isDayOfMonth());
        }

        for ($x = 11 ; $x <= 24 ; $x++) {
            TasksManager::setDayOfMonth($x);
            $this->assertFalse($job->isDayOfMonth());
        }

        for ($x = 25 ; $x <= 29 ; $x++) {
            TasksManager::setDayOfMonth($x);
            $this->assertTrue($job->isDayOfMonth());
        }
    }
    /**
     * @test
     */
    public function testIsDayOfMonth02() {
        $job = new BaseTask('5 4 1,3,4,10,29 * *');

        TasksManager::setDayOfMonth(1);
        $this->assertTrue($job->isDayOfMonth());

        TasksManager::setDayOfMonth(3);
        $this->assertTrue($job->isDayOfMonth());

        TasksManager::setDayOfMonth(4);
        $this->assertTrue($job->isDayOfMonth());

        TasksManager::setDayOfMonth(10);
        $this->assertTrue($job->isDayOfMonth());

        TasksManager::setDayOfMonth(29);
        $this->assertTrue($job->isDayOfMonth());

        TasksManager::setDayOfMonth(11);
        $this->assertFalse($job->isDayOfMonth());

        TasksManager::setDayOfMonth(2);
        $this->assertFalse($job->isDayOfMonth());
    }

    /**
     * @test
     */
    public function testIsDayOfWeek00() {
        $job = new BaseTask();
        $job->weeklyOn('sun');
        TasksManager::setDayOfWeek(0);
        $this->assertTrue($job->isDayOfWeek());

        for ($x = 1 ; $x < 7 ; $x++) {
            TasksManager::setDayOfWeek($x);
            $this->assertFalse($job->isDayOfWeek());
        }
    }
    /**
     * @test
     */
    public function testIsDayOfWeek01() {
        $job = new BaseTask('5 4 * * 0-3,5-6');

        for ($x = 0 ; $x <= 3 ; $x++) {
            TasksManager::setDayOfWeek($x);
            $this->assertTrue($job->isDayOfWeek());
        }
        TasksManager::setDayOfWeek(4);
        $this->assertFalse($job->isDayOfWeek());

        TasksManager::setDayOfWeek(5);
        $this->assertTrue($job->isDayOfWeek());
        TasksManager::setDayOfWeek(6);
        $this->assertTrue($job->isDayOfWeek());
    }
    /**
     * @test
     */
    public function testIsDayOfWeek02() {
        $job = new BaseTask('5 4 * * 0,3,6');

        TasksManager::setDayOfWeek(0);
        $this->assertTrue($job->isDayOfWeek());

        TasksManager::setDayOfWeek(3);
        $this->assertTrue($job->isDayOfWeek());

        TasksManager::setDayOfWeek(6);
        $this->assertTrue($job->isDayOfWeek());

        TasksManager::setDayOfWeek(1);
        $this->assertFalse($job->isDayOfWeek());

        TasksManager::setDayOfWeek(5);
        $this->assertFalse($job->isDayOfWeek());
    }
    /**
     * @test
     */
    public function testIsDayOfWeek03() {
        $job = new BaseTask();
        $job->everyWeek();
                
        TasksManager::setDayOfWeek(0);
        TasksManager::setHour(0);
        $this->assertTrue($job->isDayOfWeek());
        $this->assertTrue($job->isHour());
        
    }
    /**
     * @test
     */
    public function testIsDayOfWeek04() {
        $this->expectException(InvalidCRONExprException::class);
        $this->expectExceptionMessage("Invalid cron expression: '5 4 * * 0-a,5-6'");
        $job = new BaseTask('5 4 * * 0-a,5-6');

    }
    /**
     * @test
     */
    public function testIsDayOfWeek05() {
        $this->expectException(InvalidCRONExprException::class);
        $this->expectExceptionMessage("Invalid cron expression: '5 4 * * 0-,5-6'");
        $job = new BaseTask('5 4 * * 0-,5-6');

    }
    /**
     * @test
     */
    public function testIsHour00() {
        $job = new BaseTask();
        $job->everyHour(1);

        for ($x = 0 ; $x < 24 ; $x++) {
            TasksManager::setHour($x);
            $this->assertTrue($job->isHour());
        }
    }
    /**
     * @test
     */
    public function testIsHour01() {
        $job = new BaseTask('5 0-2,12-19 1-10,25-29 * *');

        for ($x = 0 ; $x <= 2 ; $x++) {
            TasksManager::setHour($x);
            $this->assertTrue($job->isHour());
        }

        for ($x = 3 ; $x <= 11 ; $x++) {
            TasksManager::setHour($x);
            $this->assertFalse($job->isHour());
        }

        for ($x = 12 ; $x <= 19 ; $x++) {
            TasksManager::setHour($x);
            $this->assertTrue($job->isHour());
        }

        for ($x = 20 ; $x <= 23 ; $x++) {
            TasksManager::setHour($x);
            $this->assertFalse($job->isHour());
        }
    }
    /**
     * @test
     */
    public function testIsHour02() {
        $job = new BaseTask('5 4,8,9,13,16 1,3,4,10,29 * *');

        TasksManager::setHour(4);
        $this->assertTrue($job->isHour());

        TasksManager::setHour(8);
        $this->assertTrue($job->isHour());

        TasksManager::setHour(9);
        $this->assertTrue($job->isHour());

        TasksManager::setHour(13);
        $this->assertTrue($job->isHour());

        TasksManager::setHour(16);
        $this->assertTrue($job->isHour());

        TasksManager::setHour(11);
        $this->assertFalse($job->isHour());

        TasksManager::setHour(2);
        $this->assertFalse($job->isHour());
    }

    /**
     * @test
     */
    public function testIsHour03() {
        $job = new BaseTask();
        $job->everyXHour(2);

        for ($x = 0 ; $x < 24 ; $x++) {
            TasksManager::setHour($x);
            
            if ($x % 2 == 0) {
                $this->assertTrue($job->isHour());
            } else {
                $this->assertFalse($job->isHour());
            }
        }
    }
    /**
     * @test
     */
    public function testIsHour04() {
        $job = new BaseTask();
        $job->everyXHour(3);

        for ($x = 0 ; $x < 24 ; $x++) {
            TasksManager::setHour($x);
            
            if ($x % 3 == 0) {
                $this->assertTrue($job->isHour());
            } else {
                $this->assertFalse($job->isHour());
            }
        }
    }
    /**
     * @test
     */
    public function testIsMinute00() {
        $job = new BaseTask();
        $job->dailyAt(0, 25);
        TasksManager::setMinute(25);
        $this->assertTrue($job->isMinute());

        for ($x = 0 ; $x < 25 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertFalse($job->isMinute());
        }

        for ($x = 26 ; $x < 59 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
    }
    /**
     * @test
     */
    public function testIsMinute01() {
        $job = new BaseTask('5-10,20-40 4 * * 0-3,5-6');

        for ($x = 0 ; $x <= 4 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertFalse($job->isMinute());
        }

        for ($x = 5 ; $x <= 10 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertTrue($job->isMinute());
        }

        for ($x = 11 ; $x <= 19 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertFalse($job->isMinute());
        }

        for ($x = 20 ; $x <= 40 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertTrue($job->isMinute());
        }

        for ($x = 41 ; $x <= 59 ; $x++) {
            TasksManager::setMinute($x);
            $this->assertFalse($job->isMinute());
        }
    }
    /**
     * @test
     */
    public function testIsMinute02() {
        $job = new BaseTask('5,20,40,59 4 * * 0,3,6');

        TasksManager::setMinute(5);
        $this->assertTrue($job->isMinute());

        TasksManager::setMinute(20);
        $this->assertTrue($job->isMinute());

        TasksManager::setMinute(40);
        $this->assertTrue($job->isMinute());

        TasksManager::setMinute(59);
        $this->assertTrue($job->isMinute());

        TasksManager::setMinute(0);
        $this->assertFalse($job->isMinute());

        TasksManager::setMinute(1);
        $this->assertFalse($job->isMinute());
    }

    /**
     * @test
     */
    public function testIsMonth00() {
        $job = new BaseTask();
        $job->onMonth(5);
        TasksManager::setMonth(5);
        $this->assertTrue($job->isMonth());

        for ($x = 1 ; $x < 4 ; $x++) {
            TasksManager::setMonth($x);
            $this->assertFalse($job->isMonth());
        }

        for ($x = 6 ; $x < 13 ; $x++) {
            TasksManager::setMonth($x);
            $this->assertFalse($job->isMonth());
        }
    }
    /**
     * @test
     */
    public function testIsMonth01() {
        $job = new BaseTask('* 4 * 1-3,9-12 *');

        for ($x = 1 ; $x <= 3 ; $x++) {
            TasksManager::setMonth($x);
            $this->assertTrue($job->isMonth());
        }

        for ($x = 4 ; $x <= 8 ; $x++) {
            TasksManager::setMonth($x);
            $this->assertFalse($job->isMonth());
        }

        for ($x = 9 ; $x <= 12 ; $x++) {
            TasksManager::setMonth($x);
            $this->assertTrue($job->isMonth());
        }
    }
    /**
     * @test
     */
    public function testIsMonth02() {
        $job = new BaseTask('5,20,40,59 4 * 1,5,8,4,12 0,3,6');

        TasksManager::setMonth(1);
        $this->assertTrue($job->isMonth());

        TasksManager::setMonth(5);
        $this->assertTrue($job->isMonth());

        TasksManager::setMonth(8);
        $this->assertTrue($job->isMonth());

        TasksManager::setMonth(4);
        $this->assertTrue($job->isMonth());

        TasksManager::setMonth(12);
        $this->assertTrue($job->isMonth());

        TasksManager::setMinute(1);
        $this->assertFalse($job->isMinute());
    }
    /**
     * @test
     */
    public function testIsMonth03() {
        $this->expectException(InvalidCRONExprException::class);
        $this->expectExceptionMessage("Invalid cron expression: '* 4 * 1-3,9-b *");
        $job = new BaseTask('* 4 * 1-3,9-b *');

    }
    /**
     * @test
     */
    public function testIsMonth04() {
        $this->expectException(InvalidCRONExprException::class);
        $this->expectExceptionMessage("Invalid cron expression: '* 4 * 1-3,9- *");
        $job = new BaseTask('* 4 * 1-3,9- *');

    }
    /**
     * @test
     */
    public function testOnMonth00() {
        $job = new BaseTask();
        $this->assertTrue($job->onMonth());
        $this->assertEquals('0 0 1 1 *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testOnMonth01() {
        $job = new BaseTask();
        $this->assertFalse($job->onMonth(0, 3));
        $this->assertTrue($job->onMonth('jan', 3));
        $this->assertEquals('0 0 3 1 *',$job->getExpression());
        $this->assertTrue($job->onMonth(2, 3));
        $this->assertEquals('0 0 3 2 *',$job->getExpression());
        $this->assertTrue($job->onMonth(3, 3));
        $this->assertEquals('0 0 3 3 *',$job->getExpression());
        $this->assertTrue($job->onMonth(4, 3));
        $this->assertEquals('0 0 3 4 *',$job->getExpression());
        $this->assertTrue($job->onMonth(5, '3'));
        $this->assertEquals('0 0 3 5 *',$job->getExpression());
        $this->assertTrue($job->onMonth(6, 3));
        $this->assertEquals('0 0 3 6 *',$job->getExpression());
        $this->assertTrue($job->onMonth(7, 3));
        $this->assertEquals('0 0 3 7 *',$job->getExpression());
        $this->assertTrue($job->onMonth(8, '3'));
        $this->assertEquals('0 0 3 8 *',$job->getExpression());
        $this->assertTrue($job->onMonth(9, 3));
        $this->assertEquals('0 0 3 9 *',$job->getExpression());
        $this->assertTrue($job->onMonth(10, 3));
        $this->assertEquals('0 0 3 10 *',$job->getExpression());
        $this->assertTrue($job->onMonth(11, 3));
        $this->assertEquals('0 0 3 11 *',$job->getExpression());
        $this->assertTrue($job->onMonth('dec', '3'));
        $this->assertEquals('0 0 3 12 *',$job->getExpression());
        $this->assertFalse($job->onMonth(13, 3));
        $this->assertEquals('0 0 3 12 *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testOnMonth02() {
        $job = new BaseTask();
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
        $job = new BaseTask();
        $this->assertTrue($job->onMonth('2', 23, '23:00'));
        $this->assertEquals('0 23 23 2 *',$job->getExpression());
        $this->assertTrue($job->onMonth(2, '20', '10:00'));
        $this->assertEquals('0 10 20 2 *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testSetName00() {
        $job = new BaseTask();
        TasksManager::registerTasks();
        $this->assertTrue($job->setTaskName('Fail 1 '));
    }
    /**
     * @test
     */
    public function testSetOnExc00() {
        $job = new BaseTask();
        $job->setOnFailure(function()
        {
        }, ['1',2]);
        $this->assertTrue(true);
    }
    public function testSetTaskName00() {
        $job = new BaseTask();
        $job->setTaskName('Hello Task');
        $this->assertEquals('Hello Task',$job->getTaskName());
        $job->setTaskName('  Hello Task  ');
        $this->assertEquals('Hello Task',$job->getTaskName());
        $job->setTaskName('   ');
        $this->assertEquals('Hello Task',$job->getTaskName());
    }
    /**
     * @test
     */
    public function testTasksManager00() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'\'.');
        $task = new BaseTask('');
    }
    /**
     * @test
     */
    public function testTasksManager01() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'11-100 * * * *\'.');
        $task = new BaseTask('11-100 * * * *');
    }
    /**
     * @test
     */
    public function testTasksManager02() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'*/60 * * * *\'.');
        $task = new BaseTask('*/60 * * * *');
    }
    /**
     * @test
     */
    public function testTasksManager03() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 15-24 * * *\'.');
        $task = new BaseTask('* 15-24 * * *');
    }
    /**
     * @test
     */
    public function testTasksManager04() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* */24 * * *\'.');
        $task = new BaseTask('* */24 * * *');
    }
    /**
     * @test
     */
    public function testTasksManager05() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * 25-37 * *\'.');
        $task = new BaseTask('* * 25-37 * *');
    }
    /**
     * @test
     */
    public function testTasksManager06() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 12-13 *\'.');
        $task = new BaseTask('* * * 12-13 *');
    }
    /**
     * @test
     */
    public function testTasksManager07() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 13 *\'.');
        $task = new BaseTask('* * * 13 *');
    }
    /**
     * @test
     */
    public function testTasksManager08() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 12-1 *\'.');
        $task = new BaseTask('* * * 12-1 *');
    }
    /**
     * @test
     */
    public function testTasksManager09() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * 5-16 *\'.');
        $task = new BaseTask('* * * 5-16 *');
    }
    /**
     * @test
     */
    public function testTasksManager10() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 0-7\'.');
        $task = new BaseTask('* * * * 0-7');
    }
    /**
     * @test
     */
    public function testTasksManager11() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 0-7\'.');
        $task = new BaseTask('* * * * 0-7');
    }
    /**
     * @test
     */
    public function testTasksManager12() {
        $task = new BaseTask('* * * * 0-6');
        $this->assertEquals('* * * * 0-6',$task->getExpression());
    }
    /**
     * @test
     */
    public function testTasksManager13() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 7\'.');
        $task = new BaseTask('* * * * 7');
    }
    /**
     * @test
     */
    public function testTasksManager14() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'40-30 * * * *\'.');
        $task = new BaseTask('40-30 * * * *');
    }
    /**
     * @test
     */
    public function testTasksManager15() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'40-30 * * * *\'.');
        $task = new BaseTask('40-30 * * * *');
    }
    /**
     * @test
     */
    public function testTasksManager16() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* * * * 1,2,7,8\'.');
        $task = new BaseTask('* * * * 1,2,7,8');
    }
    /**
     * @test
     */
    public function testTasksManager17() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 23-8 * * *\'.');
        $task = new BaseTask('* 23-8 * * *');
    }
    /**
     * @test
     */
    public function testTasksManager18() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 0-13,14,24 * * *\'.');
        $task = new BaseTask('* 0-13,14,24 * * *');
    }
    /**
     * @test
     */
    public function testTasksManager19() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression: \'* 0-13 * 5,6,13 *\'.');
        $task = new BaseTask('* 0-13 * 5,6,13 *');
    }
    /**
     * @test
     */
    public function testWeeklyOn00() {
        $job = new BaseTask();
        $this->assertTrue($job->weeklyOn());
        $this->assertEquals('0 0 * * 0',$job->getExpression());
    }
    /**
     * @test
     */
    public function testWeeklyOn01() {
        $job = new BaseTask();
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
        $job = new BaseTask();
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
        $job = new BaseTask();
        $this->assertTrue($job->weeklyOn('3','23:00'));
        $this->assertEquals('0 23 * * 3',$job->getExpression());
        $this->assertTrue($job->weeklyOn('6','23:00'));
        $this->assertEquals('0 23 * * 6',$job->getExpression());
    }
    /**
     * @test
     */
    public function testWeeklyOn04() {
        $job = new BaseTask();
        $this->assertFalse($job->weeklyOn('7','23:00'));
        $this->assertFalse($job->weeklyOn(7,'23:00'));
    }
    /**
     * @test
     */
    public function testWeeklyOn05() {
        $job = new BaseTask();
        $this->assertFalse($job->weeklyOn('0','23:60'));
        $this->assertFalse($job->weeklyOn('0','24:00'));
        $this->assertTrue($job->weeklyOn('0','00:00'));
    }
    /**
     * @test
     */
    public function testEveryXMinute00() {
        $task = new BaseTask();
        $task->everyXMinuts(5);
        $this->assertEquals('*/5 * * * *', $task->getExpression());
 
        TasksManager::setDayOfMonth(15);
        TasksManager::setHour(23);
        TasksManager::setMonth(5);
        TasksManager::setMinute(33);
        $this->assertEquals('05-15 23:33', TasksManager::getTimestamp());
        $this->assertTrue($task->isDayOfWeek());
        $this->assertFalse($task->isMinute());
        $this->assertTrue($task->isHour());
        $this->assertTrue($task->isMonth());
        $this->assertTrue($task->isDayOfMonth());
        
    }
    /**
     * @test
     */
    public function testEveryXMinute01() {
        $task = new BaseTask();
        $task->everyXMinuts(17);
        TasksManager::setDayOfMonth(15);
        TasksManager::setHour(23);
        TasksManager::setMonth(5);
        TasksManager::setMinute(33);
        
        for ($x = 1 ; $x < 60 ; $x++) {
            TasksManager::setMinute($x);
            if ($x % 17 == 0) {
                $this->assertTrue($task->isMinute());
            } else {
                $this->assertFalse($task->isMinute());
            }
        }
        
    }
}
