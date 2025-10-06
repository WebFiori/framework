<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\SchedulerCommand;
use webfiori\framework\scheduler\TasksManager;

/**
 * Description of SchedulerCommandTest
 *
 * @author Ibrahim
 */
class SchedulerCommandTest extends CLITestCase {
    public function setUp() : void {
        parent::setUp();
        TasksManager::reset();
        TasksManager::setPassword('123456');
        TasksManager::registerTasks();
    }
    /**
     * @test
     */
    public function testRunWithoutRequiredOptions() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
        ], []);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Info: At least one of the options '--check', '--force' or '--show-task-args' must be provided.\n"
        ], $output);
    }
    
    /**
     * @test
     */
    public function testCheckScheduledTasks() {
        TasksManager::setPassword('123456');
        
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--check',
            'p' => '123456'
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Total number of tasks: 5\n",
            "Executed Tasks: 4\n",
            "Successfully finished tasks:\n",
            "    Success Every Minute\n",
            "Failed tasks:\n",
            "    Fail 1\n",
            "    Fail 2\n",
            "    Fail 3\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testCheckWithoutPassword() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--check',
        ], []);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Error: The argument 'p' is missing. It must be provided if scheduler password is set.\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceTaskExecution() {
        
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            'p' => '123456'
        ], [
            '0'
        ]);

        //$this->assertEquals(0, $this->getExitCode());
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
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceTaskWithLogging() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            'p' => '123456'
        ], [
            '0'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceTaskWithExceptionLogging() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            'p' => '123456'
        ], [
            '1'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
            "#0 At class webfiori\\framework\\scheduler\\AbstractTask Line: 1109\n",
            "#1 At class webfiori\\framework\\scheduler\\AbstractTask Line: 447\n",
            "#2 At class webfiori\\framework\\scheduler\\AbstractTask Line: 951\n",
            "#3 At class webfiori\\framework\\scheduler\\TasksManager Line: 672\n",
            "#4 At class webfiori\\framework\\scheduler\\TasksManager Line: 142\n",
            "#5 At class webfiori\\framework\\cli\\commands\\SchedulerCommand Line: 89\n",
            "#6 At class webfiori\\framework\\cli\\commands\\SchedulerCommand Line: 365\n",
            "#7 At class WebFiori\\Cli\\Command Line: 735\n",
            "#8 At class WebFiori\\Cli\\Runner Line: 1132\n",
            "#9 At class WebFiori\\Cli\\Runner Line: 1016\n",
            "#10 At class WebFiori\\Cli\\Runner Line: 169\n",
            "#11 At class WebFiori\\Cli\\CommandTestCase Line: 85\n",
            "Skip"];
        $idx = 0;
        
        foreach ($expected as $item) {
            if ($item == 'Skip') {
                break;
            }
            $this->assertEquals($item, $output[$idx]);
            $idx++;
        }
    }
    
    /**
     * @test
     */
    public function testForceSpecificTaskByName() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            '--task-name' => 'Success 1',
            'p' => '123456'
        ], [
            'N'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceTaskWithCustomArguments() {
        TasksManager::execLog(true);
        
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--show-log',
            '--task-name' => 'Success 1',
            'start' => '2021',
            'end' => '2022',
            'p' => '123456'
        ], [
            'N'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceTaskWithIncorrectPassword() {
        TasksManager::reset();
        TasksManager::execLog(true);
        TasksManager::setPassword('123456');
        TasksManager::registerTasks();

        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--task-name' => 'Success 1',
        ], [
            'N'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Error: Provided password is incorrect.\n",
        ], $output);
        $this->assertEquals([
            "Running task(s) check...",
            "Error: Given password is incorrect.",
            "Check finished.",
        ], TasksManager::getLogArray());
    }
    
    /**
     * @test
     */
    public function testShowTaskArguments() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--task-name' => 'Success 1',
            '--show-task-args',
            'p' => '123456'
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Task Args:\n",
            "    start: Start date of the report.\n",
            "    end: End date of the report.\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testShowTaskArgumentsWithSelection() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--show-task-args',
            'p' => '123456'
        ], [
            '0'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Select one of the scheduled tasks to show supported args:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "Task Args:\n",
            "    <NO ARGS>\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testListAllScheduledTasks() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--list'
        ], []);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
    }
    
    /**
     * @test
     */
    public function testCheckWithValidPassword() {
        TasksManager::setPassword('123456');
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--check',
            'p' => '123456'
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Total number of tasks: 5\n",
            "Executed Tasks: 4\n",
            "Successfully finished tasks:\n",
            "    Success Every Minute\n",
            "Failed tasks:\n",
            "    Fail 1\n",
            "    Fail 2\n",
            "    Fail 3\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceTaskWithInteractiveArguments() {
        TasksManager::reset();
        TasksManager::execLog(true);
        TasksManager::setPassword('123456');
        TasksManager::registerTasks();

        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--task-name' => 'Success 1',
            'p' => '123456'
        ], [
            'Y',
            '2021-01-01',
            '2020-01-01'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Enter a value for the argument \"start\": Enter = ''\n",
            "Enter a value for the argument \"end\": Enter = ''\n",
            "Start: 2021\n",
            "End: 2022\n",
            "The task was forced.\n",
            "Total number of tasks: 5\n",
            "Executed Tasks: 1\n",
            "Successfully finished tasks:\n",
            "    Success 1\n",
            "Failed tasks:\n",
            "    <NONE>\n",
        ], $output);
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
    public function testCancelTaskSelection() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            'p' => '123456'
        ], [
            '5'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Select one of the scheduled tasks to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
        ], $output);
    }
    
    /**
     * @test
     */
    public function testForceNonExistentTask() {
        $output = $this->executeSingleCommand(new SchedulerCommand(), [
            'webfiori',
            'scheduler',
            '--force',
            '--task-name="Rand"',
            'p' => '123456'
        ], [
            'Hell',
            '5'
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Error: No task was found which has the name 'Rand'\n",
        ], $output);
    }
}
