<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\CronJobClassWriter;
use webfiori\framework\cron\JobArgument;
use webfiori\cli\InputValidator;
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
        
        $this->setClassInfo(APP_DIR_NAME.'\\jobs', 'Job');
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
            $argObj = new JobArgument($this->getInput('Enter argument name:'));
            $argObj->setDescription($this->getInput('Enter argument description:', 'No Description.', new InputValidator(function ($val)
            {
                if (strlen($val) > 0) {
                    return true;
                }

                return false;
            })));
            $this->getWriter()->addArgument($argObj);
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
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        }));
    }
}
