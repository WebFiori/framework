<?php
namespace webfiori\framework\test\scheduler;

use PHPUnit\Framework\TestCase;
use webfiori\framework\router\Router;
use webfiori\framework\scheduler\TasksManager;
use webfiori\framework\scheduler\webServices\TasksServicesManager;
use webfiori\framework\scheduler\webUI\ListTasksPage;
use webfiori\framework\scheduler\webUI\TasksLoginPage;
/**
 *
 * @author Ibrahim
 */
class SchedulerTest extends TestCase {
    /**
     * @test
     */
    public function testCreateTask00() {
        TasksManager::reset();
        $this->assertFalse(TasksManager::createTask('7-1 * * * *'));
    }


    /**
     * @test
     */
    public function testDailyTask00() {
        $this->assertTrue(TasksManager::dailyTask('00:00', 'Task Xy', function()
        {
        }));
        $job = TasksManager::getTask('Task Xy');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testDailyTask01() {
        $this->assertFalse(TasksManager::dailyTask('23:65:6', 'Task X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testDailyTask02() {
        $this->assertFalse(TasksManager::dailyTask('24:00', 'Task X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testDailyTask03() {
        $this->assertTrue(TasksManager::dailyTask('23:00', 'Task Ok2', function(TasksManager $task, TestCase $c)
        {
            $c->assertEquals('Task Ok2', $task->activeTask()->getTaskName());
        }, [TasksManager::get(), $this]));
        $this->assertEquals('NO_PASSWORD', TasksManager::getPassword());
        TasksManager::run('', 'Task Ok2', true);
    }
    /**
     * @test
     */
    public function testGetTask00() {
        $this->assertNull(TasksManager::getTask('Not Exist'));
    }
    /**
     * @test
     */
    public function testGetTask01() {
        TasksManager::createTask('* * * * *', 'Task 1');
        TasksManager::createTask('15 * * * *', 'Task 2');
        TasksManager::createTask('16 * * * *', 'Task 3');
        TasksManager::createTask('17 * * * *', 'Task 4');
        $job = TasksManager::getTask('Task 3');
        $this->assertEquals('16 * * * *',$job->getExpression());
    }

    /**
     * @test
     */
    public function testMonthlyTask00() {
        $this->assertTrue(TasksManager::monthlyTask(1, '00:00', 'Task Xyz', function()
        {
        }));
        $job = TasksManager::getTask('Task Xyz');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testMonthlyTask01() {
        $this->assertFalse(TasksManager::monthlyTask(44, '23:65:6', 'Task X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testMonthlyTask02() {
        $this->assertFalse(TasksManager::monthlyTask(2, '24:00', 'Task X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testMonthlyTask03() {
        $this->assertTrue(TasksManager::monthlyTask(15, '23:00', 'Task Ok3', function(TasksManager $task, TestCase $c)
        {
            $c->assertEquals('Task Ok3', $task->activeTask()->getTaskName());
        }, [TasksManager::get(), $this]));
        $this->assertEquals('NO_PASSWORD', TasksManager::getPassword());
        TasksManager::run('', 'Task Ok3', true);
        $this->assertEquals('TASK_NOT_FOUND', TasksManager::run('', 'Not Exist Super'));
    }
    /**
     * @test
     */
    public function testRoutes() {
        Router::removeAll();
        TasksManager::initRoutes();
        $this->assertEquals(5, Router::routesCount());

        $route1 = Router::getUriObj('/scheduler');
        $this->assertNotNull($route1);
        $this->assertEquals(TasksLoginPage::class, $route1->getRouteTo());

        $route2 = Router::getUriObj('/scheduler/login');
        $this->assertNotNull($route2);
        $this->assertEquals(TasksLoginPage::class, $route2->getRouteTo());

        $route3 = Router::getUriObj('/scheduler/tasks');
        $this->assertNotNull($route3);
        $this->assertEquals(ListTasksPage::class, $route3->getRouteTo());

        $route4 = Router::getUriObj('/scheduler/apis/{action}');
        $this->assertNotNull($route4);
        $this->assertEquals(TasksServicesManager::class, $route4->getRouteTo());
    }
    /**
     * @test
     */
    public function testTimestamp00() {
        TasksManager::setDayOfMonth(15);
        TasksManager::setHour(23);
        TasksManager::setMonth(5);
        TasksManager::setMinute(33);
        $this->assertEquals('05-15 23:33', TasksManager::getTimestamp());
        TasksManager::setDayOfMonth(1);
        TasksManager::setHour(0);
        TasksManager::setMonth(11);
        TasksManager::setMinute(9);
        $this->assertEquals('11-01 00:09', TasksManager::getTimestamp());
    }
    /**
     * @test
     */
    public function testWeeklyTask00() {
        $this->assertTrue(TasksManager::weeklyTask('6-23:00', 'Task X', function()
        {
        }));
        $job = TasksManager::getTask('Task X');
        $this->assertNotNull($job);
    }
    /**
     * @test
     */
    public function testWeeklyTask01() {
        $this->assertFalse(TasksManager::weeklyTask('7-23:00', 'Task X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testWeeklyTask02() {
        $this->assertFalse(TasksManager::weeklyTask('6--23:00', 'Task X', function()
        {
        }));
    }
    /**
     * @test
     */
    public function testWeeklyTask03() {
        TasksManager::setPassword('');
        $this->assertTrue(TasksManager::weeklyTask('sun-23:00', 'Task Ok', function(TasksManager $task, TestCase $c)
        {
            $c->assertEquals('Task Ok', $task->activeTask()->getTaskName());
        }, [TasksManager::get(), $this]));
        $this->assertEquals('NO_PASSWORD', TasksManager::getPassword());
        TasksManager::run('', 'Task Ok', true);
    }
}
