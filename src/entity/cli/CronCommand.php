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
        parent::__construct('--cron', [
            'p' => [
                'optional' => true,
                'description' => 'CRON password. If it is set in CRON, then it must be '
                .'provided here.'
            ],
            'check' => [
                'optional' => true,
                'description' => 'Run a check aginst all jobs to check if '
                .'it is time to execute them or not.'
            ],
            'force' => [
                'optional' => true,
                'description' => 'Force a specific job to execute.'
            ],
            'job-name' => [
                'optional' => true,
                'description' => 'The name of the job that will be forced to '
                .'execute.'
            ],
            'show-job-args' => [
                'optional' => true,
                'description' => 'If this one is provided with job name and a '
                .'job has custom execution args, they will be shown.'
            ],
            'show-log' => [
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

        if ($this->isArgProvided('check')) {
            $pass = $this->getArgValue('p');

            if ($pass !== null) {
                $result = Cron::run($pass, null, false, $this);

                if ($result == 'INV_PASS') {
                    $this->error("Provided password is incorrect.\n");
                } else {
                    $this->_printExcResult($result);
                    $this->_showLog();
                    $retVal = 0;
                }
            } else {
                $this->error("The argument 'p' is missing. It must be provided if cron password is set.\n");
            }
        } else if ($this->isArgProvided('force')) {
            $retVal = $this->_force();
        } else if ($this->isArgProvided('show-job-args')) {
            $this->_showJobArgs();
        } else {
            fprintf(STDOUT, $this->formatOutput("Info:", [
                'color' => 'blue'
            ])." At least one of the options 'check', 'force' or 'show-job-args' must be provided.\n");
        }

        return $retVal;
    }
    private function _force() {
        $jobName = $this->getArgValue('job-name');
        $cPass = $this->getArgValue('p');
        $retVal = -1;

        if ($jobName === null) {
            $this->error("Job name is missing.\n");
        } else if ($cPass === null && Cron::password() != 'NO_PASSWORD') {
            $this->error("The argument 'p' is missing. It must be provided if cron password is set.\n");
        } else {
            $result = Cron::run($cPass,$jobName.'',true, $this);

            if ($result == 'INV_PASS') {
                $this->error(STDERR,"Provided password is incorrect.\n");
            } else if ($result == 'JOB_NOT_FOUND') {
                $this->error(STDERR,"No job was found which has the name '".$jobName."'\n");
            } else {
                $this->_printExcResult($result);
                $this->_showLog();
                $retVal = 0;
            }
        }

        return $retVal;
    }
    private function _printExcResult($result) {
        fprintf(STDOUT,"Total number of jobs: ".$result['total-jobs']."\n");
        fprintf(STDOUT,"Executed Jobs: ".$result['executed-count']."\n");
        fprintf(STDOUT,"Successfully finished jobs:\n");
        $sJobs = $result['successfully-completed'];

        if (count($sJobs) == 0) {
            fprintf(STDOUT,"    <NONE>\n");
        } else {
            foreach ($sJobs as $jobName) {
                fprintf(STDOUT,"    ".$jobName."\n");
            }
        }
        fprintf(STDOUT,"Failed jobs:\n");
        $fJobs = $result['failed'];

        if (count($fJobs) == 0) {
            fprintf(STDOUT,"    <NONE>\n");
        } else {
            foreach ($fJobs as $jobName) {
                fprintf(STDOUT,"    ".$jobName."\n");
            }
        }
    }
    private function _showJobArgs() {
        $jobName = $this->getArgValue('job-name');

        if ($this->_checkPass()) {
            if ($jobName !== null) {
                $job = Cron::getJob($jobName);

                if ($job !== null) {
                    fprintf(STDOUT,"Job Args:\n");
                    $customArgs = $job->getExecArgsNames();

                    if (count($customArgs) != 0) {
                        foreach ($customArgs as $argName) {
                            fprintf(STDOUT,"$argName\n");
                        }
                    } else {
                        fprintf(STDOUT,"<NO ARGS>\n");
                    }
                } else {
                    $this->error("No job which has the given name was found.\n");
                }
            } else {
                $this->error("The argument 'job-name' is missing.\n");
            }
        }
    }
    private function _showLog() {
        if ($this->isArgProvided('show-log')) {
            fprintf(STDOUT, "\n------+-Execution Log-+------\n");

            foreach (Cron::getLogArray() as $message) {
                fprintf(STDOUT, $message."\n");
            }
        } else {
            fprintf(STDOUT, $this->formatOutput("TIP:", [
                'color' => 'yellow'
            ])." Supply the argument 'show-log' to show execution log.\n");
        }
    }
    private function _checkPass() {
        $cronPass = Cron::password();
        if($cronPass == 'NO_PASSWORD'){
            return true;
        }
        $givenPass = $this->getArgValue('p');
        if ($givenPass === null) {
            $this->error("Password is missing. It must be provided as argument 'p=PASS'.\n");
            return false;
        }
        $hash = hash('sha256', $givenPass);
        $same = $hash == $cronPass;
        if (!$same) {
            $this->error("Provided password is incorrect.\n");
        }
        return $same;
    }
}
