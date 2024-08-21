<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\App;
use webfiori\framework\scheduler\TasksManager;
/**
 * Description of CronCommandTest
 *
 * @author Ibrahim
 */
class SchedulerCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Info: At least one of the options '--check', '--force' or '--show-task-args' must be provided.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test01() {
        TasksManager::setPassword(hash('sha256', '123456'));
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--check',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Total number of tasks: 5\n",
            "Executed Tasks: 4\n",
            "Successfully finished tasks:\n",
            "    Success Every Minute\n",
            "Failed tasks:\n",
            "    Fail 1\n",
            "    Fail 2\n",
            "    Fail 3\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test02() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--check',
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: The argument 'p' is missing. It must be provided if scheduler password is set.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test03() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled tasks to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    <NONE>\n",
            "Failed tasks:\n",
            "    Fail 1\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test04() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled tasks to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Running task(s) check...\n",
            "Forcing task 'Fail 1' to execute...\n",
            "Active task: \"Fail 1\" ...\n",
            "Calling the method app\\tasks\Fail1TestTask::execute()\n",
            "Info: Task Fail 1 Is executing...\n",
            "Calling the method app\\tasks\Fail1TestTask::onFail()\n",
            "Error: Task Fail 1 Failed.\n",
            "Calling the method app\\tasks\Fail1TestTask::afterExec()\n",
            "Check finished.\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    <NONE>\n",
            "Failed tasks:\n",
            "    Fail 1\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test05() {
        $runner = App::getRunner();
        $runner->setInputs([
            '1'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $expected = [
            "Select one of the scheduled tasks to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Running task(s) check...\n",
            "Forcing task 'Fail 2' to execute...\n",
            "Active task: \"Fail 2\" ...\n",
            "Calling the method app\\tasks\Fail2TestTask::execute()\n",
            "WARNING: An exception was thrown while performing the operation app\\tasks\Fail2TestTask::execute. The output of the task might be not as expected.\n",
            "Exception class: Error\n",
            "Exception message: Call to undefined method app\\tasks\Fail2TestTask::undefined()\n",
            "Thrown in: Fail2TestTask\n",
            "Line: 44\n",
            "Stack Trace:\n",
            "#0 At class app\\tasks\Fail2TestTask line 44\n",
            "#1 At class webfiori\\framework\scheduler\AbstractTask line 1128\n",
            "#2 At class webfiori\\framework\scheduler\AbstractTask line 449\n",
            "#3 At class webfiori\\framework\scheduler\AbstractTask line 951\n",
            "#4 At class webfiori\\framework\scheduler\TasksManager line 673\n",
            "#5 At class webfiori\\framework\scheduler\TasksManager line 139\n",
            "#6 At class webfiori\\framework\cli\commands\SchedulerCommand line 86\n",
            "#7 At class webfiori\\framework\cli\commands\SchedulerCommand line 331\n",
            "#8 At class webfiori\\cli\CLICommand line 409\n",
            "#9 At class webfiori\\cli\Runner line 725\n",
            "#10 At class webfiori\\cli\Runner line 656\n",
            "#11 At class webfiori\cli\Runner line 156\n",
            "Skip"];
        $actual = $runner->getOutput();
        $idx = 0;
        
        foreach ($expected as $item) {
            if ($item == 'Skip') {
                break;
            }
            $this->assertEquals($item, $actual[$idx]);
            $idx++;
        }
    }
    /**
     * @test
     */
    public function test06() {
        $runner = App::getRunner();
        $runner->setInputs([
            'N'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            '--task-name' => 'Success 1',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Running task(s) check...\n",
            "Forcing task 'Success 1' to execute...\n",
            "Active task: \"Success 1\" ...\n",
            "Calling the method app\\tasks\SuccessTestTask::execute()\n",
            "Start: 2021-07-08\n",
            "End: \n",
            "The task was forced.\n",
            "Calling the method app\\tasks\SuccessTestTask::onSuccess()\n",
            "Calling the method app\\tasks\SuccessTestTask::afterExec()\n",
            "Check finished.\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    Success 1\n",
            "Failed tasks:\n",
            "    <NONE>\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test07() {
        $runner = App::getRunner();
        TasksManager::execLog(true);
        $runner->setInputs([
            'N'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            '--task-name' => 'Success 1',
            'start' => '2021',
            'end' => '2022',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Running task(s) check...\n",
            "Forcing task 'Success 1' to execute...\n",
            "Active task: \"Success 1\" ...\n",
            "Calling the method app\\tasks\SuccessTestTask::execute()\n",
            "Start: 2021\n",
            "End: 2022\n",
            "The task was forced.\n",
            "Calling the method app\\tasks\SuccessTestTask::onSuccess()\n",
            "Calling the method app\\tasks\SuccessTestTask::afterExec()\n",
            "Check finished.\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    Success 1\n",
            "Failed tasks:\n",
            "    <NONE>\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test08() {
        $runner = App::getRunner();
        TasksManager::reset();
        TasksManager::execLog(true);
        TasksManager::setPassword('123456');
        TasksManager::registerTasks();

        $runner->setInputs([
            'N'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--task-name' => 'Success 1',
            //'p' => '1234'
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Error: Provided password is incorrect.\n",
        ], $runner->getOutput());
        $this->assertEquals([
            "Running task(s) check...",
            "Error: Given password is incorrect.",
            "Check finished.",
        ], TasksManager::getLogArray());
    }
    /**
     * @test
     */
    public function test09() {
        $runner = App::getRunner();
        $runner->setInputs([

        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--task-name' => 'Success 1',
            '--show-task-args',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Task Args:\n",
            "    start: Start date of the report.\n",
            "    end: End date of the report.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test10() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--show-task-args',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled tasks to show supported args:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "Task Args:\n",
            "    <NO ARGS>\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test11() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--list'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Number Of Scheduled Tasks: 5\n",
            "--------- Task #01 ---------\n",
            "Task Name         : Fail 1\n",
            "Cron Expression   : * * * * *\n",
            "--------- Task #02 ---------\n",
            "Task Name         : Fail 2\n",
            "Cron Expression   : * * * * *\n",
            "--------- Task #03 ---------\n",
            "Task Name         : Fail 3\n",
            "Cron Expression   : * * * * *\n",
            "--------- Task #04 ---------\n",
            "Task Name         : Success Every Minute\n",
            "Cron Expression   : * * * * *\n",
            "--------- Task #05 ---------\n",
            "Task Name         : Success 1\n",
            "Cron Expression   : 30 4 * * *\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test12() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--check',
            'p' => '4'
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: Provided password is incorrect\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test13() {
        $runner = App::getRunner();
        TasksManager::reset();
        TasksManager::execLog(true);
        TasksManager::setPassword(hash('sha256', '123456'));
        TasksManager::registerTasks();

        $runner->setInputs([
            'Y',
            '2021-01-01',
            '2020-01-01'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--task-name' => 'Success 1',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Enter a value for the argument \"start\": Enter = ''\n",
            "Enter a value for the argument \"end\": Enter = ''\n",
            "Start: 2021-01-01\n",
            "End: 2020-01-01\n",
            "The task was forced.\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    Success 1\n",
            "Failed tasks:\n",
            "    <NONE>\n",
        ], $runner->getOutput());
        $this->assertEquals([
            'Running task(s) check...',
            "Forcing task 'Success 1' to execute...",
            "Active task: \"Success 1\" ...",
            "Calling the method app\\tasks\SuccessTestTask::execute()",
            "Calling the method app\\tasks\SuccessTestTask::onSuccess()",
            "Calling the method app\\tasks\SuccessTestTask::afterExec()",
            "Check finished.",
        ], TasksManager::getLogArray());
    }
    /**
     * @test
     */
    public function test14() {
        $runner = App::getRunner();
        $runner->setInputs([
            '5'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled tasks to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test15() {
        $runner = App::getRunner();
        $runner->setInputs([
            'Hell',
            '5'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'scheduler',
            '--force',
            '--task-name="Rand"',
            'p' => '123456'
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: No task was found which has the name 'Rand'\n",
        ], $runner->getOutput());
    }
}
