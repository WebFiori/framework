<?php

namespace webfiori\framework\test\cli;

use webfiori\framework\cron\AbstractJob;
use webfiori\framework\WebFioriApp;

/**
 * Description of CreateJobTest
 *
 * @author Ibrahim
 */
class CreateJobTest extends CreateTestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInput([
            '3',
            'SuperCoolJob',
            'app\jobs',
            'The Greatest Job',
            'The job will do nothing.',
            'N',
            '',
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\jobs'\n",
            "Enter a name for the job:\n",
            "Provide short description of what does the job will do:\n",
            "Would you like to add arguments to the job?(y/N)\n",
            "Info: New class was created at \"".ROOT_DIR.DS.'app'.DS."jobs\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\jobs\\SuperCoolJob'));
        $this->removeClass('\\app\\jobs\\SuperCoolJob');
    }
    
    /**
     * @test
     * 
     * @depends test00
     */
    public function test01() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInput([
            '3',
            'SuperCoolJob',
            'app\jobs',
            'SuperCool2',
            'app\jobs',
            'The Greatest Job',
            'The job will do nothing.',
            'N',
            '',
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\jobs'\n",
            "Error: A class in the given namespace which has the given name was found.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\jobs'\n","Enter a name for the job:\n",
            "Provide short description of what does the job will do:\n",
            "Would you like to add arguments to the job?(y/N)\n",
            "Info: New class was created at \"".ROOT_DIR.DS.'app'.DS."jobs\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\jobs\\SuperCool2Job'));
        $this->removeClass('\\app\\jobs\\SuperCoolJob');
        $this->removeClass('\\app\\jobs\\SuperCool2Job');
    }
    
    /**
     * @test
     * 
     */
    public function test02() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInput([
            '3',
            'NewRound',
            'app\jobs',
            '',
            'Invalid#',
            'Create Round Job',
            'The job will do nothing.',
            'N',
            '',
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\jobs'\n",
            "Enter a name for the job:\n",
            "Error: Provided name is invalid!\n",
            "Enter a name for the job:\n",
            "Error: Provided name is invalid!\n",
            "Enter a name for the job:\n",
            "Provide short description of what does the job will do:\n",
            "Would you like to add arguments to the job?(y/N)\n",
            "Info: New class was created at \"".ROOT_DIR.DS.'app'.DS."jobs\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\jobs\\NewRoundJob'));
        $this->removeClass('\\app\\jobs\\NewRoundJob');
    }
    /**
     * @test
     * 
     */
    public function test03() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'job'
        ]);
        $runner->setInput([
            'SendDailyReport',
            'app\jobs',
            'Send Sales Report',
            'The job will execute every day to send sales report to management.',
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
            'n'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\jobs'\n",
            "Enter a name for the job:\n",
            "Provide short description of what does the job will do:\n",
            "Would you like to add arguments to the job?(y/N)\n",
            "Enter argument name:\n",
            "Descripe the use of the argument: Enter = ''\n",
            "Default value: Enter = ''\n",
            "Would you like to add more arguments?(y/N)\n",
            "Enter argument name:\n",
            "Error: Invalid argument name: end?\n",
            "Would you like to add more arguments?(y/N)\n",
            "Enter argument name:\n",
            "Descripe the use of the argument: Enter = ''\n",
            "Default value: Enter = ''\n",
            "Would you like to add more arguments?(y/N)\n",
            "Info: New class was created at \"".ROOT_DIR.DS.'app'.DS."jobs\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\jobs\\SendDailyReportJob';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $job = new $clazz();
        $this->assertTrue($job instanceof AbstractJob);
        $this->assertEquals('Send Sales Report', $job->getJobName());
        $this->assertEquals('The job will execute every day to send sales report to management.', $job->getDescription());
        $this->assertEquals(2, count($job->getArguments()));
        $arg1 = $job->getArgument('start');
        $this->assertEquals('Start date of the report.', $arg1->getDescription());
        $this->assertNull($arg1->getDefault());
        
        $arg2 = $job->getArgument('end');
        $this->assertEquals('End date of the report.', $arg2->getDescription());
        $this->assertEquals('2021-07-07', $arg2->getDefault());
    }
}
