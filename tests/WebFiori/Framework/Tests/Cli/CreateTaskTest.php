<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Scheduler\AbstractTask;

/**
 * Description of CreateTaskTest
 *
 * @author Ibrahim
 */
class CreateTaskTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '3',
            'SuperCoolTask',
            'App\Tasks',
            'The Greatest task',
            'The task will do nothing.',
            'N',
            "\n", // Hit Enter to pick default value
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
            "10: Database migration.\n",
            "11: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Tasks'\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'App'.DS."Tasks\".\n",
        ], $output);
        $clazz = '\\App\\Tasks\\SuperCoolTask';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }

    /**
     * @test
     */
    public function test01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '3',
            'SuperCoolTask',
            'App\Tasks',
            'SuperCool2',
            'App\Tasks',
            'The Greatest task',
            'The task will do nothing.',
            'N',
            "\n", // Hit Enter to pick default value
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
            "10: Database migration.\n",
            "11: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Tasks'\n",
            "Error: A class in the given namespace which has the given name was found.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Tasks'\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'App'.DS."Tasks\".\n",
        ], $output);
        $clazz = '\\App\\Tasks\\SuperCool2Task';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass('\\App\\Tasks\\SuperCoolTask');
        $this->removeClass($clazz);
    }

    /**
     * @test
     */
    public function test02() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '3',
            'NewRound',
            'App\Tasks',
            '', // Invalid empty name
            'Invalid#', // Invalid name with special character
            'Create Round task',
            ' ', // Invalid description (space only)
            ' The task will do nothing. ',
            'N',
            "\n", // Hit Enter to pick default value
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
            "10: Database migration.\n",
            "11: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Tasks'\n",
            "Enter a name for the task:\n",
            "Error: Provided name is invalid!\n",
            "Enter a name for the task:\n",
            "Error: Provided name is invalid!\n",
            "Enter a name for the task:\n",
            "Provide short description of what does the task will do:\n",
            "Error: Invalid input is given. Try again.\n",
            "Provide short description of what does the task will do:\n",
            "Would you like to add arguments to the task?(y/N)\n",
            "Info: New class was created at \"".ROOT_PATH.DS.'App'.DS."Tasks\".\n",
        ], $output);
        $clazz = '\\App\\Tasks\\NewRoundTask';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function test03() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'task'
        ], [
            'SendDailyReport',
            'App\Tasks',
            'Send Sales Report',
            'The task will execute every day to send sales report to management.',
            'y',
            'start',
            'Start date of the report.',
            "\n", // Hit Enter to pick default value (empty default)
            'y',
            'end?', // Invalid argument name
            'y',
            'end',
            'End date of the report.',
            '2021-07-07',
            'y',
            "\n", // Hit Enter to pick default value (invalid empty name)
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Tasks'\n",
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
            "Info: New class was created at \"".ROOT_PATH.DS.'App'.DS."Tasks\".\n",
        ], $output);
        $clazz = '\\App\\Tasks\\SendDailyReportTask';
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
