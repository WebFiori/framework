<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cron\Cron;
use webfiori\framework\WebFioriApp;
/**
 * Description of CronCommandTest
 *
 * @author Ibrahim
 */
class CronCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'cron'
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Info: At least one of the options '--check', '--force' or '--show-job-args' must be provided.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test01() {
        Cron::password(hash('sha256', '123456'));
        $runner = WebFioriApp::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--check',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Total number of jobs: 5\n",
            "Executed Jobs: 4\n",
            "Successfully finished jobs:\n",
            "    Success Every Minute\n",
            "Failed jobs:\n",
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
            'cron',
            '--check',
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: The argument 'p' is missing. It must be provided if cron password is set.\n",
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
            'cron',
            '--force',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
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
            'cron',
            '--force',
            '--show-log',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Running job(s) check...\n",
            "Forceing job 'Fail 1' to execute...\n",
            "Active job: \"Fail 1\" ...\n",
            "Calling the method app\jobs\Fail1TestJob::execute()\n",
            "Calling the method app\jobs\Fail1TestJob::onFail()\n",
            "Calling the method app\jobs\Fail1TestJob::afterExec()\n",
            "Check finished.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
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
            'cron',
            '--force',
            '--show-log',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to force:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "5: Cancel <--\n",
            "Running job(s) check...\n",
            "Forceing job 'Fail 2' to execute...\n",
            "Active job: \"Fail 2\" ...\n",
            "Calling the method app\jobs\Fail2TestJob::execute()\n",
            "WARNING: An exception was thrown while performing the operation app\jobs\Fail2TestJob::execute. The output of the job might be not as expected.\n",
            "Exception class: Error\n",
            "Exception message: Call to undefined method app\jobs\Fail2TestJob::undefined()\n",
            "Thrown in: Fail2TestJob\n",
            "Line: 45\n",
            "Calling the method app\jobs\Fail2TestJob::onFail()\n",
            "Calling the method app\jobs\Fail2TestJob::afterExec()\n",
            "Check finished.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    <NONE>\n",
            "Failed jobs:\n",
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
            'cron',
            '--force',
            '--show-log',
            '--job-name' => 'Success 1',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Running job(s) check...\n",
            "Forceing job 'Success 1' to execute...\n",
            "Active job: \"Success 1\" ...\n",
            "Calling the method app\jobs\SuccessTestJob::execute()\n",
            "Start: 2021-07-08\n",
            "End: \n",
            "The job was forced.\n",
            "Calling the method app\jobs\SuccessTestJob::onSuccess()\n",
            "Calling the method app\jobs\SuccessTestJob::afterExec()\n",
            "Check finished.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    Success 1\n",
            "Failed jobs:\n",
            "    <NONE>\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test07() {
        $runner = WebFioriApp::getRunner();
        Cron::execLog(true);
        $runner->setInputs([
            'N'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--force',
            '--show-log',
            '--job-name' => 'Success 1',
            'start' => '2021',
            'end' => '2022',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Running job(s) check...\n",
            "Forceing job 'Success 1' to execute...\n",
            "Active job: \"Success 1\" ...\n",
            "Calling the method app\jobs\SuccessTestJob::execute()\n",
            "Start: 2021\n",
            "End: 2022\n",
            "The job was forced.\n",
            "Calling the method app\jobs\SuccessTestJob::onSuccess()\n",
            "Calling the method app\jobs\SuccessTestJob::afterExec()\n",
            "Check finished.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    Success 1\n",
            "Failed jobs:\n",
            "    <NONE>\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test08() {
        $runner = WebFioriApp::getRunner();
        Cron::reset();
        Cron::execLog(true);
        Cron::password('123456');
        Cron::registerJobs();
        
        $runner->setInputs([
            'N'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--force',
            '--job-name' => 'Success 1',
            //'p' => '1234'
        ]);
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Error: Provided password is incorrect.\n",
        ], $runner->getOutput());
        $this->assertEquals([
            "Running job(s) check...",
            "Error: Given password is incorrect.",
            "Check finished.",
        ], Cron::getLogArray());
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
            'cron',
            '--job-name' => 'Success 1',
            '--show-job-args',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Job Args:\n",
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
            'cron',
            '--show-job-args',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select one of the scheduled jobs to show supported args:\n",
            "0: Fail 1\n",
            "1: Fail 2\n",
            "2: Fail 3\n",
            "3: Success Every Minute\n",
            "4: Success 1\n",
            "Job Args:\n",
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
            'cron',
            '--list'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Number Of Jobs: 5\n",
            "--------- Job #01 ---------\n",
            "Job Name          : Fail 1\n",
            "Cron Expression   : * * * * *\n",
            "--------- Job #02 ---------\n",
            "Job Name          : Fail 2\n",
            "Cron Expression   : * * * * *\n",
            "--------- Job #03 ---------\n",
            "Job Name          : Fail 3\n",
            "Cron Expression   : * * * * *\n",
            "--------- Job #04 ---------\n",
            "Job Name          : Success Every Minute\n",
            "Cron Expression   : * * * * *\n",
            "--------- Job #05 ---------\n",
            "Job Name          : Success 1\n",
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
            'cron',
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
        Cron::reset();
        Cron::execLog(true);
        Cron::password(hash('sha256', '123456'));
        Cron::registerJobs();
        
        $runner->setInputs([
            'Y',
            '2021-01-01',
            '2020-01-01'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'cron',
            '--force',
            '--job-name' => 'Success 1',
            'p' => '123456'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Would you like to customize execution arguments?(y/N)\n",
            "Enter a value for the argument \"start\": Enter = ''\n",
            "Enter a value for the argument \"end\": Enter = ''\n",
            "Start: 2021-01-01\n",
            "End: 2020-01-01\n",
            "The job was forced.\n",
            "Total number of jobs: 5\n",
            "Executed Jobs: 1\n",
            "Successfully finished jobs:\n",
            "    Success 1\n",
            "Failed jobs:\n",
            "    <NONE>\n",
        ], $runner->getOutput());
        $this->assertEquals([
            'Running job(s) check...',
            "Forceing job 'Success 1' to execute...",
            "Active job: \"Success 1\" ...",
            "Calling the method app\jobs\SuccessTestJob::execute()",
            "Calling the method app\jobs\SuccessTestJob::onSuccess()",
            "Calling the method app\jobs\SuccessTestJob::afterExec()",
            "Check finished.",
        ], Cron::getLogArray());
    }
}
