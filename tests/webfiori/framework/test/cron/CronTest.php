<?php
namespace webfiori\framework\test\cron;

use PHPUnit\Framework\TestCase;
use webfiori\framework\scheduler\TasksManager;
use webfiori\framework\scheduler\webServices\TasksServicesManager;
use webfiori\framework\scheduler\webUI\TasksLoginPage;
use webfiori\framework\scheduler\webUI\ListTasksPage;
use webfiori\framework\router\Router;
/**
 * Description of CronTest
 *
 * @author Ibrahim
 */
class CronTest extends TestCase {
    /**
     * @test
     */
    public function testCreateJob00() {
        $this->assertFalse(TasksManager::createJob('7-1 * * * *'));
    }
    /**
     * @test
     */
    public function testGetJob00() {
        $this->assertNull(TasksManager::getJob('Not Exist'));
    }
    /**
     * @test
     */
    public function testGetJob01() {
        TasksManager::createJob('* * * * *', 'Job 1');
        TasksManager::createJob('15 * * * *', 'Job 2');
        TasksManager::createJob('16 * * * *', 'Job 3');
        TasksManager::createJob('17 * * * *', 'Job 4');
        $job = TasksManager::getJob('Job 3');
        $this->assertEquals('16 * * * *',$job->getExpression());
    }
    /**
     * @test
     */
    public function testTimestamp00() {
        TasksManager::setDayOfMonth(15);
        TasksManager::setHour(23);
        TasksManager::setMonth(5);
        TasksManager::setMinute(33);
        $this->assertEquals('05-15 23:33', TasksManager::timestamp());
        TasksManager::setDayOfMonth(1);
        TasksManager::setHour(0);
        TasksManager::setMonth(11);
        TasksManager::setMinute(9);
        $this->assertEquals('11-01 00:09', TasksManager::timestamp());
    }
    /**
     * @test
     */
    public function testWeeklyJob00() {
        $this->assertTrue(TasksManager::weeklyJob('6-23:00', 'Job X', function()
        {
        }));
        $job = TasksManager::getJob('Job X');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testWeeklyJob01() {
        $this->assertFalse(TasksManager::weeklyJob('7-23:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testWeeklyJob02() {
        $this->assertFalse(TasksManager::weeklyJob('6--23:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testWeeklyJob03() {
        TasksManager::password('');
        $this->assertTrue(TasksManager::weeklyJob('sun-23:00', 'Job Ok', function(TasksManager $task, TestCase $c)
        {
            $c->assertEquals('Job Ok', $task->activeJob()->getJobName());
        }, [TasksManager::get(), $this]));
        $this->assertEquals('NO_PASSWORD', TasksManager::password());
        TasksManager::run('', 'Job Ok', true);
    }
    
    
    /**
     * @test
     */
    public function testDailyJob00() {
        $this->assertTrue(TasksManager::dailyJob('00:00', 'Job Xy', function()
        {
        }));
        $job = TasksManager::getJob('Job Xy');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testDailyJob01() {
        $this->assertFalse(TasksManager::dailyJob('23:65:6', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testDailyJob02() {
        $this->assertFalse(TasksManager::dailyJob('24:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testDailyJob03() {
        $this->assertTrue(TasksManager::dailyJob('23:00', 'Job Ok2', function(TasksManager $task, TestCase $c)
        {
            $c->assertEquals('Job Ok2', $task->activeJob()->getJobName());
        }, [TasksManager::get(), $this]));
        $this->assertEquals('NO_PASSWORD', TasksManager::password());
        TasksManager::run('', 'Job Ok2', true);
    }
    
    /**
     * @test
     */
    public function testMonthlyJob00() {
        $this->assertTrue(TasksManager::monthlyJob(1, '00:00', 'Job Xyz', function()
        {
        }));
        $job = TasksManager::getJob('Job Xyz');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testMonthlyJob01() {
        $this->assertFalse(TasksManager::monthlyJob(44, '23:65:6', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testMonthlyJob02() {
        $this->assertFalse(TasksManager::monthlyJob(2, '24:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testMonthlyJob03() {
        $this->assertTrue(TasksManager::monthlyJob(15, '23:00', 'Job Ok3', function(TasksManager $task, TestCase $c)
        {
            $c->assertEquals('Job Ok3', $task->activeJob()->getJobName());
        }, [TasksManager::get(), $this]));
        $this->assertEquals('NO_PASSWORD', TasksManager::password());
        TasksManager::run('', 'Job Ok3', true);
        $this->assertEquals('JOB_NOT_FOUND', TasksManager::run('', 'Not Exist Super'));
    }
    /**
     * @test
     */
    public function testRoutes() {
        Router::removeAll();
        TasksManager::initRoutes();
        $this->assertEquals(4, Router::routesCount());
        
        $route1 = Router::getUriObj('/scheduler');
        $this->assertNotNull($route1);
        $this->assertEquals(TasksLoginPage::class, $route1->getRouteTo());
        
        $route2 = Router::getUriObj('/scheduler/login');
        $this->assertNotNull($route2);
        $this->assertEquals(TasksLoginPage::class, $route2->getRouteTo());
        
        $route3 = Router::getUriObj('/scheduler/jobs');
        $this->assertNotNull($route3);
        $this->assertEquals(ListTasksPage::class, $route3->getRouteTo());
        
        $route4 = Router::getUriObj('/scheduler/apis/{action}');
        $this->assertNotNull($route4);
        $this->assertEquals(TasksServicesManager::class, $route4->getRouteTo());
    }
}
