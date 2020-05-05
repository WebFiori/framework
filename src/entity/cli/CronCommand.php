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

use webfiori\entity\cli\CLICommand;
use webfiori\entity\cron\Cron;
/**
 * Description of CronCommand
 *
 * @author Ibrahim
 */
class CronCommand extends CLICommand{
    public function __construct() {
        parent::__construct('--cron', [
            'p'=>[
                'optional'=>true,
                'description'=>'CRON password. If it is set in CRON, then it must be '
                . 'provided here.'
            ],
            'check'=>[
                'optional'=>true,
                'description' => 'Run a check aginst all jobs to check if '
                . 'it is time to execute them or not.'
            ],
            'force'=>[
                'optional'=>true,
                'description' => 'Force a specific job to execute.'
            ],
            'job-name'=>[
                'optional'=>true,
                'description' => 'The name of the job that will be forced to '
                . 'execute.'
            ],
            'show-log'=>[
                'optional'=>true,
                'description'=>'If set, execution log will be shown after '
                . 'execution is completed.'
            ]
        ], 'Run CRON Scheduler');
        if(Cron::password() != 'NO_PASSWORD'){
            $this->addArg('password', [
                'optional'=>false,
                'description'=>'CRON password.'
            ]);
        }
    }
    public function exec() {
        $retVal = -1;
        if($this->isArgProvided('check')){
            $pass = $this->getArgValue('p');
            if($pass !== null){
                $result = Cron::run($pass, null, false, $this);

                if ($result == 'INV_PASS') {
                    fprintf(STDERR,"Error: Provided password is incorrect.\n");
                } else {
                    $this->_printExcResult($result);
                    $this->_showLog();
                    $retVal = 0;
                }
            } else {
                fprintf(STDERR,"Error: The argument 'p' is missing. It must be provided if cron password is set.\n");
            }
        } else if ($this->isArgProvided('force')){
            $retVal = $this->_force();
        } else {
            fprintf(STDOUT,"Info: At least the option 'check' or 'force' must be provided.\n");
        }
        
        return $retVal;
    }
    private function _showLog() {
        if($this->isArgProvided('show-log')){
            fprintf(STDERR, "Execution Log: \n");
            foreach (Cron::getLogArray() as $message){
                fprintf(STDERR, $message."\n");
            }
        }
    }
    private function _force() {
        $jobName = $this->getArgValue('job-name');
        $cPass = $this->getArgValue('p');
        $retVal = -1;
        if ($jobName === null) {
            fprintf(STDERR,"Error: Job name is missing.\n");
        } else if ($cPass === null && Cron::password() != 'NO_PASSWORD') {
            fprintf(STDERR,"Error: The argument 'p' is missing. It must be provided if cron password is set.\n");
        } else {
            $result = Cron::run($cPass,$jobName.'',true, $this);

            if ($result == 'INV_PASS') {
                fprintf(STDERR,"Error: Provided password is incorrect.\n");
            } else if ($result == 'JOB_NOT_FOUND') {
                fprintf(STDERR,"Error: No job was found which has the name '".$jobName."'\n");
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
}
