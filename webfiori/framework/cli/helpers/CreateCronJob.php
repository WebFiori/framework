<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\CronJobClassWriter;
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
            $argsArr = $this->_getArgs();
        } else {
            $argsArr = [];
        }
        
        $this->getWriter()->setJobName($jobName);
        $this->getWriter()->setJobDescription($jobName);
        $this->getWriter()->setArgs($argsArr);
        
        $this->writeClass();
    }
    private function _getArgs() {
        $argsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $argName = $this->getInput('Enter argument name:');

            if (strlen($argName) > 0) {
                $argsArr[$argName] = [
                    'description' => $this->getInput('Enter argument description:', 'No Description.', function ($val) {
                        if (strlen($val) != 0) {
                            return $val;
                        }
                        return false;
                    })
                ];
                
                
            }
            $addToMore = $this->confirm('Would you like to add more arguments?', false);
        }

        return $argsArr;
    }
    private function _getJobDesc() {
        return $this->getInput('Provide short description of what does the job will do:', null, function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        });
    }
    private function _getJobName() {
        return $this->getInput('Enter a name for the job:', null, function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        });
    }
}
