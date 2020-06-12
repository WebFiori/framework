<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\entity\cli;

use webfiori\entity\cron\Cron;
/**
 * A CLI command which is related to executing 
 * background jobs or performing operations on them..
 *
 * @author Ibrahim
 * @version 1.0
 */
class CronCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--cron'. This command is used to 
     * perform operations on background jobs. In addition to that, 
     * it will have the following arguments:
     * <ul>
     * <li><b>p</b>: Cron password.</li>
     * <li><b>check</b>: Run check if it is time to execute a job.</li>
     * <li><b>force</b>: Force execution of a job given its name.</li>
     * <li><b>job-name</b>: The job that will be forced to execute or 
     * its arguments will be shown.</li>
     * <li><b>show-job-args</b>: Show arguments of a job.</li>
     * <li><b>show-log</b>: Display execution log after execution is finished.</li>
     * </ul>
     */
    public function __construct() {
        parent::__construct('cron', [
            'p' => [
                'optional' => true,
                'description' => 'CRON password. If it is set in CRON, then it must be '
                .'provided here.'
            ],
            '--check' => [
                'optional' => true,
                'description' => 'Run a check aginst all jobs to check if '
                .'it is time to execute them or not.'
            ],
            '--force' => [
                'optional' => true,
                'description' => 'Force a specific job to execute.'
            ],
            '--job-name' => [
                'optional' => true,
                'description' => 'The name of the job that will be forced to '
                .'execute.'
            ],
            '--show-job-args' => [
                'optional' => true,
                'description' => 'If this one is provided with job name and a '
                .'job has custom execution args, they will be shown.'
            ],
            '--show-log' => [
                'optional' => true,
                'description' => 'If set, execution log will be shown after '
                .'execution is completed.'
            ]
        ], 'Run CRON Scheduler');

        if (Cron::password() != 'NO_PASSWORD') {
            $this->addArg('p', [
                'optional' => false,
                'description' => 'CRON password.'
            ]);
        }
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() {
        $retVal = -1;

        if ($this->isArgProvided('--check')) {
            $pass = $this->getArgValue('p');

            if ($pass !== null) {
                $result = Cron::run($pass, null, false, $this);

                if ($result == 'INV_PASS') {
                    $this->error("Provided password is incorrect");
                } else {
                    $this->_printExcResult($result);
                    $this->_showLog();
                    $retVal = 0;
                }
            } else {
                $this->error("The argument 'p' is missing. It must be provided if cron password is set.");
            }
        } else if ($this->isArgProvided('--force')) {
            $retVal = $this->_force();
        } else if ($this->isArgProvided('--show-job-args')) {
            $this->_showJobArgs();
        } else {
            $this->info("At least one of the options '--check', '--force' or '--show-job-args' must be provided.");
        }
            
        return $retVal;
    }
    private function _force() {
        $jobName = $this->getArgValue('--job-name');
        $cPass = $this->getArgValue('p');
        $retVal = -1;
        $jobsNamesArr = Cron::getJobsNames();
        $jobsNamesArr[] = 'Cancel';
        
        if ($jobName === null) {
            $jobName = $this->select('Select one of the scheduled jobs to force:', $jobsNamesArr, count($jobsNamesArr) - 1);
        } 
        
        if ($jobName == 'Cancel') {
            $retVal = 0;
        } else {
            $result = Cron::run($cPass,$jobName.'',true, $this);

            if ($result == 'INV_PASS') {
                $this->error("Provided password is incorrect.");
            } else if ($result == 'JOB_NOT_FOUND') {
                $this->error("No job was found which has the name '".$jobName."'");
            } else {
                $this->_printExcResult($result);
                $this->_showLog();
                $retVal = 0;
            }
        }
        
        return $retVal;
    }
    private function _printExcResult($result) {
        $this->println("Total number of jobs: ".$result['total-jobs']);
        $this->println("Executed Jobs: ".$result['executed-count']);
        $this->println("Successfully finished jobs:");
        $sJobs = $result['successfully-completed'];

        if (count($sJobs) == 0) {
            $this->println("    <NONE>");
        } else {
            foreach ($sJobs as $jobName) {
                $this->println("    ".$jobName);
            }
        }
        $this->println("Failed jobs:");
        $fJobs = $result['failed'];

        if (count($fJobs) == 0) {
            $this->println("    <NONE>");
        } else {
            foreach ($fJobs as $jobName) {
                $this->println("    ".$jobName);
            }
        }
    }
    private function _showJobArgs() {
        $jobName = $this->getArgValue('--job-name');
        if ($jobName === null) {
            $jobName = $this->select('Select one of the scheduled jobs to show supported args:', Cron::getJobsNames());
        } 
        $job = Cron::getJob($jobName);

        $this->println("Job Args:");
        $customArgs = $job->getExecArgsNames();

        if (count($customArgs) != 0) {
            foreach ($customArgs as $argName) {
                $this->println("$argName");
            }
        } else {
            $this->println("<NO ARGS>");
        }
    }
    private function _showLog() {
        if ($this->isArgProvided('--show-log')) {
            $this->println("\n------+-Execution Log-+------");

            foreach (Cron::getLogArray() as $message) {
                $this->println($message);
            }
        } else {
            $this->prints("TIP: ", [
                'color' => 'yellow'
            ]);
            $this->println("Supply the argument '--show-log' to show execution log.");
        }
    }
}
