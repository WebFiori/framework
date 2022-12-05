<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\commands;

use webfiori\cli\CLICommand;
use webfiori\framework\cron\Cron;
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
            '--list' => [
                'optional' => true,
                'description' => 'List all scheduled CRON jobs.'
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
                .'execute or to show its arguments.'
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
        ], 'Run CRON Scheduler.');

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
    public function exec() : int {
        $retVal = -1;
        
        if ($this->isArgProvided('--list')) {
            $this->listJobs();
            $retVal = 0;
        } else if ($this->isArgProvided('--check')) {
            $pass = $this->getArgValue('p');

            if ($pass !== null) {
                $result = Cron::run($pass, null, false, $this);

                if ($result == 'INV_PASS') {
                    $this->error("Provided password is incorrect");
                } else {
                    $this->_printExcResult($result);
                    $retVal = 0;
                }
            } else {
                $this->error("The argument 'p' is missing. It must be provided if cron password is set.");
            }
        } else if ($this->isArgProvided('--force')) {
            $retVal = $this->_force();
        } else if ($this->isArgProvided('--show-job-args')) {
            $this->_showJobArgs();
            $retVal = 0;
        } else {
            $this->info("At least one of the options '--check', '--force' or '--show-job-args' must be provided.");
        }

        return $retVal;
    }
    private function _checkJobArgs($jobName) {
        $job = Cron::getJob($jobName);
        $args = $job->getExecArgsNames();

        if (count($args) != 0 && $this->confirm('Would you like to customize execution arguments?', false)) {
            $this->_setArgs($args, $job);
        }
    }
    private function _force() {
        $jobName = $this->getArgValue('--job-name');
        $cPass = $this->getArgValue('p').'';
        $retVal = -1;
        $jobsNamesArr = Cron::getJobsNames();
        $jobsNamesArr[] = 'Cancel';

        if ($jobName === null) {
            $jobName = $this->select('Select one of the scheduled jobs to force:', $jobsNamesArr, count($jobsNamesArr) - 1);
        } 

        if ($jobName == 'Cancel') {
            $retVal = 0;
        } else {
            $this->_checkJobArgs($jobName);
            $result = Cron::run($cPass,$jobName.'',true, $this);

            if ($result == 'INV_PASS') {
                $this->error("Provided password is incorrect.");
            } else if ($result == 'JOB_NOT_FOUND') {
                $this->error("No job was found which has the name '".$jobName."'");
            } else {
                $this->_printExcResult($result);
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
    private function _setArgs($argsArr, \webfiori\framework\cron\AbstractJob $job) {
        $setArg = true;
        $index = 0;
        $count = count($argsArr);

        do {
            $val = $this->getInput('Enter a value for the argument "'.$argsArr[$index].'":', '');

            if (strlen($val) != 0) {
                $job->getArgument($argsArr[$index])->setValue($val);
            }

            if ($index + 1 == $count) {
                $setArg = false;
            }
            $index++;
        } while ($setArg);
    }
    private function _showJobArgs() {
        $jobName = $this->getArgValue('--job-name');

        if ($jobName === null) {
            $jobName = $this->select('Select one of the scheduled jobs to show supported args:', Cron::getJobsNames());
        } 
        $job = Cron::getJob($jobName);

        $this->println("Job Args:");
        $customArgs = $job->getArguments();

        if (count($customArgs) != 0) {
            foreach ($customArgs as $argObj) {
                $this->println("    %s: %s", $argObj->getName(), $argObj->getDescription());
            }
        } else {
            $this->println("    <NO ARGS>");
        }
    }
    public function listJobs() {
        $jobs = Cron::jobsQueue();
        $i = 1;
        $this->println("Number Of Jobs: ".$jobs->size());

        while ($job = $jobs->dequeue()) {
            $num = $i < 10 ? '0'.$i : $i;
            $this->println("--------- Job #$num ---------", [
                'color' => 'light-blue',
                'bold' => true
            ]);
            $this->println("Job Name %".(18 - strlen('Job Name'))."s %s",[], ":",$job->getJobName());
            $this->println("Cron Expression %".(18 - strlen('Cron Expression'))."s %s",[],":",$job->getExpression());
            $i++;
        }
    }
}
