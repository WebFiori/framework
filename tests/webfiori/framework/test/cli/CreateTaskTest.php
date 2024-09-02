<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\App;
use webfiori\framework\scheduler\AbstractTask;

/**
 * Description of CreatetaskTest
 *
 * @author Ibrahim
 */
class CreateTaskTest extends CreateTestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInputs([
            '3',
            'SuperCoolTask',
            'app\tasks',
            'The Greatest task',
            'The task will do nothing.',
            'N',
            '',
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\tasks'\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'app'.DS."tasks\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\tasks\\SuperCoolTask';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }

    /**
     * @test
     *
     * @depends test00
     */
    public function test01() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInputs([
            '3',
            'SuperCoolTask',
            'app\tasks',
            'SuperCool2',
            'app\tasks',
            'The Greatest task',
            'The task will do nothing.',
            'N',
            '',
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\tasks'\n",
            "Error: A class in the given namespace which has the given name was found.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\tasks'\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'app'.DS."tasks\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\tasks\\SuperCool2Task';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass('\\app\\tasks\\SuperCoolTask');
        $this->removeClass($clazz);
    }

    /**
     * @test
     *
     */
    public function test02() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInputs([
            '3',
            'NewRound',
            'app\tasks',
            '',
            'Invalid#',
            'Create Round task',
            ' ',
            ' The task will do nothing. ',
            'N',
            '',
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\tasks'\n",
            "Enter a name for the task:\n",
            "Error: Provided name is invalid!\n",
            "Enter a name for the task:\n",
            "Error: Provided name is invalid!\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Error: Invalid input is given. Try again.\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'app'.DS."tasks\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\tasks\\NewRoundTask';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     *
     */
    public function test03() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'task'
        ]);
        $runner->setInputs([
            'SendDailyReport',
            'app\tasks',
            'Send Sales Report',
            'The task will execute every day to send sales report to management.',
            'y',
            'start',
            'Start date of the report.',
            '',
            'y',
            'end?',
            'y',
            'end',
            'End date of the report.',
            '2021-07-07',
            'y',
            '',
            'n'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\tasks'\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Enter argument name:\n",
            "Describe the use of the argument: Enter = ''\n",
            "Default value: Enter = ''\n",
            "Would you like to add more arguments?(y/N)\n",
            "Enter argument name:\n",
            "Error: Invalid argument name: end?\n",
            "Would you like to add more arguments?(y/N)\n",
            "Enter argument name:\n",
            "Describe the use of the argument: Enter = ''\n",
            "Default value: Enter = ''\n",
            "Would you like to add more arguments?(y/N)\n",
            "Enter argument name:\n",
            "Error: Invalid argument name: <empty string>\n",
            "Would you like to add more arguments?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'app'.DS."tasks\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\tasks\\SendDailyReportTask';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $task = new $clazz();
        $this->assertTrue($task instanceof AbstractTask);
        $this->assertEquals('Send Sales Report', $task->gettaskName());
        $this->assertEquals('The task will execute every day to send sales report to management.', $task->getDescription());
        $this->assertEquals(2, count($task->getArguments()));
        $arg1 = $task->getArgument('start');
        $this->assertEquals('Start date of the report.', $arg1->getDescription());
        $this->assertNull($arg1->getDefault());

        $arg2 = $task->getArgument('end');
        $this->assertEquals('End date of the report.', $arg2->getDescription());
        $this->assertEquals('2021-07-07', $arg2->getDefault());
    }
}
