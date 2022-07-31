<?php
namespace webfiori\framework\test\cron;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cron\Cron;
use webfiori\framework\cron\webServices\CronServicesManager;
use webfiori\framework\cron\webUI\CronLoginView;
use webfiori\framework\cron\webUI\CronTasksView;
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
        $this->assertFalse(Cron::createJob('7-1 * * * *'));
    }
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
    /**
     * @test
     */
    public function testTimestamp00() {
        Cron::setDayOfMonth(15);
        Cron::setHour(23);
        Cron::setMonth(5);
        Cron::setMinute(33);
        $this->assertEquals('05-15 23:33', Cron::timestamp());
        Cron::setDayOfMonth(1);
        Cron::setHour(0);
        Cron::setMonth(11);
        Cron::setMinute(9);
        $this->assertEquals('11-01 00:09', Cron::timestamp());
    }
    /**
     * @test
     */
    public function testWeeklyJob00() {
        $this->assertTrue(Cron::weeklyJob('6-23:00', 'Job X', function()
        {
        }));
        $job = Cron::getJob('Job X');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testWeeklyJob01() {
        $this->assertFalse(Cron::weeklyJob('7-23:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testWeeklyJob02() {
        $this->assertFalse(Cron::weeklyJob('6--23:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testWeeklyJob03() {
        Cron::password('');
        $this->assertTrue(Cron::weeklyJob('sun-23:00', 'Job Ok', function(Cron $cron, TestCase $c)
        {
            $c->assertEquals('Job Ok', $cron->activeJob()->getJobName());
        }, [Cron::get(), $this]));
        $this->assertEquals('NO_PASSWORD', Cron::password());
        Cron::run('', 'Job Ok', true);
    }
    
    
    /**
     * @test
     */
    public function testDailyJob00() {
        $this->assertTrue(Cron::dailyJob('00:00', 'Job Xy', function()
        {
        }));
        $job = Cron::getJob('Job Xy');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testDailyJob01() {
        $this->assertFalse(Cron::dailyJob('23:65:6', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testDailyJob02() {
        $this->assertFalse(Cron::dailyJob('24:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testDailyJob03() {
        $this->assertTrue(Cron::dailyJob('23:00', 'Job Ok2', function(Cron $cron, TestCase $c)
        {
            $c->assertEquals('Job Ok2', $cron->activeJob()->getJobName());
        }, [Cron::get(), $this]));
        $this->assertEquals('NO_PASSWORD', Cron::password());
        Cron::run('', 'Job Ok2', true);
    }
    
    /**
     * @test
     */
    public function testMonthlyJob00() {
        $this->assertTrue(Cron::monthlyJob(1, '00:00', 'Job Xyz', function()
        {
        }));
        $job = Cron::getJob('Job Xyz');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testMonthlyJob01() {
        $this->assertFalse(Cron::monthlyJob(44, '23:65:6', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testMonthlyJob02() {
        $this->assertFalse(Cron::monthlyJob(2, '24:00', 'Job X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testMonthlyJob03() {
        $this->assertTrue(Cron::monthlyJob(15, '23:00', 'Job Ok3', function(Cron $cron, TestCase $c)
        {
            $c->assertEquals('Job Ok3', $cron->activeJob()->getJobName());
        }, [Cron::get(), $this]));
        $this->assertEquals('NO_PASSWORD', Cron::password());
        Cron::run('', 'Job Ok3', true);
        $this->assertEquals('JOB_NOT_FOUND', Cron::run('', 'Not Exist Super'));
    }
    /**
     * @test
     */
    public function testRoutes() {
        Router::removeAll();
        Cron::initRoutes();
        $this->assertEquals(4, Router::routesCount());
        
        $route1 = Router::getUriObj('/cron');
        $this->assertNotNull($route1);
        $this->assertEquals(CronLoginView::class, $route1->getRouteTo());
        
        $route2 = Router::getUriObj('/cron/login');
        $this->assertNotNull($route2);
        $this->assertEquals(CronLoginView::class, $route2->getRouteTo());
        
        $route3 = Router::getUriObj('/cron/jobs');
        $this->assertNotNull($route3);
        $this->assertEquals(CronTasksView::class, $route3->getRouteTo());
        
        $route4 = Router::getUriObj('/cron/apis/{action}');
        $this->assertNotNull($route4);
        $this->assertEquals(CronServicesManager::class, $route4->getRouteTo());
    }
}
