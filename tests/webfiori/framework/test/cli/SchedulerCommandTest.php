<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\scheduler\TasksManager;
use webfiori\framework\WebFioriApp;
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
        $runner = WebFioriApp::getRunner();
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
        TasksManager::password(hash('sha256', '123456'));
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
            "Calling the method app\\tasks\Fail1TestTask::onFail()\n",
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
        $runner = WebFioriApp::getRunner();
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
        $this->assertEquals([
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
            "Line: 45\n",
            "Calling the method app\\tasks\Fail2TestTask::onFail()\n",
            "Calling the method app\\tasks\Fail2TestTask::afterExec()\n",
            "Check finished.\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    <NONE>\n",
            "Failed tasks:\n",
            "    Fail 2\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test06() {
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
        TasksManager::reset();
        TasksManager::execLog(true);
        TasksManager::password('123456');
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
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
        $runner = WebFioriApp::getRunner();
        TasksManager::reset();
        TasksManager::execLog(true);
        TasksManager::password(hash('sha256', '123456'));
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
}
