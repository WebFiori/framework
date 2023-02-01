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
namespace webfiori\framework\cli\helpers;

use webfiori\cli\InputValidator;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\CronJobClassWriter;
use webfiori\framework\cron\CronJob;
use webfiori\framework\cron\JobArgument;
/**
 * A helper class which is used to help in creating cron jobs classes using CLI.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class CreateCronJob extends CreateClassHelper {
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new CronJobClassWriter());
        
        $this->setClassInfo(APP_DIR.'\\jobs', 'Job');
        $jobName = $this->_getJobName();
        $jobDesc = $this->_getJobDesc();
        
        if ($this->confirm('Would you like to add arguments to the job?', false)) {
            $this->_getArgs();
        }
        
        $this->getWriter()->setJobName($jobName);
        $this->getWriter()->setJobDescription($jobDesc);
        
        $this->writeClass();
    }
    private function _getArgs() {
        $addToMore = true;

        while ($addToMore) {
            try {
                $argObj = new JobArgument($this->getInput('Enter argument name:'));
                $argObj->setDescription($this->getInput('Descripe the use of the argument:', ''));
                $argObj->setDefault($this->getInput('Default value:', ''));
                
                $this->getWriter()->addArgument($argObj);
            } catch (\InvalidArgumentException $ex) {
                $this->error($ex->getMessage());
            }
            $addToMore = $this->confirm('Would you like to add more arguments?', false);
        }
    }
    private function _getJobDesc() {
        return $this->getInput('Provide short description of what does the job will do:', null, new InputValidator(function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        }));
    }
    private function _getJobName() {
        return $this->getInput('Enter a name for the job:', null, new InputValidator(function ($val)
        {
            $temp = new CronJob();
            
            if ($temp->setJobName($val)) {
                return true;
            }

            return false;
        }, 'Provided name is invalid!'));
    }
}
