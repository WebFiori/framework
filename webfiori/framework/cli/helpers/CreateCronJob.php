<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
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
        parent::__construct($command);
        
        $this->setClassInfo(APP_DIR_NAME.'\\jobs', 'Job');
        $jobName = $this->_getJobName();
        $jobDesc = $this->_getJobDesc();
        
        if ($this->confirm('Would you like to add arguments to the job?', false)) {
            $argsArr = $this->_getArgs();
        } else {
            $argsArr = [];
        }
        
        $this->appendTop();
        
        $this->append([
            "use webfiori\\framework\\cron\\AbstractJob;",
            "use webfiori\\framework\\cron\\CronEmail;",
            "use webfiori\\framework\\cron\\Cron;\n",
            '/**',
            ' * A background process which was created using the command "create".',
            ' *',
            " * The process will have the name '$jobName'."
        ]);
        
        $argsPartArr = [];
        
        if (count($argsArr) != 0) {
            $argsPartArr = [
                ' * In addition, the proces have the following args:',
                ' * <ul>'
            ];
            

            foreach ($argsArr as $argName => $options) {
                $argsPartArr[] = " * <li>$argName: ".$options['description']."</li>";
            }
            $argsPartArr[] = ' * </ul>';
        }
        $argsPartArr[] = ' */';
        $argsPartArr[] = 'class '.$this->getWriter()->getName().' extends AbstractJob {';
        $this->append($argsPartArr);

        $this->_writeConstructor($jobName, $argsArr, $jobDesc);
        
        $this->append([
            '/**',
            ' * Execute the process.',
            ' */',
            'public function execute() {'
        ], 1);
        
        $this->append('//TODO: Write the code that represents the process.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the job failed to complete without errors.',
            ' */',
            'public function onFail() {'
        ], 1);
        $this->append('//TODO: Implement the action to perform when the job fails to complete without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the job completed without errors.',
            ' */',
            'public function onSuccess() {',
        ], 1);

        $this->append('//TODO: Implement the action to perform when the job executes without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after the job has finished to execute.',
            ' */',
            'public function afterExec() {',
        ], 1);

        $this->append('//TODO: Implement the action to perform when the job finishes to execute.', 2);
        $this->append("//\$email = new CronEmail('no-reply', [", 2);
        $this->append("//    'webfiori@example.com' => 'Ibrahim Ali'", 2);
        $this->append('//]);', 2);
        $this->append('}', 1);

        $this->append("}");

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

    private function _writeConstructor($name, array $args, $jobDesc) {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){'
        ], 1);
        $this->append([
            "parent::__construct('$name');",
            "\$this->setDescription('". str_replace('\'', '\\\'', $jobDesc)."');"
        ], 2);
        
        
        if (count($args) > 0) {
            $this->append('$this->addExecutionArgs([', 2);

            foreach ($args as $argName => $argOptions) {
                $this->append("'$argName' => [", 3);
                $this->append("'description' => '".str_replace('\'', '\\\'', $argOptions['description'])."'", 4);
                $this->append("],", 3);
            }
            $this->append(']);', 2);
        }
        $this->append([
            '// TODO: Specify the time at which the process will run at.',
            '// You can use one of the following methods to specifiy the time:',
            '//$this->dailyAt(4, 30)',
            '//$this->everyHour();',
            '//$this->everyMonthOn(1, \'00:00\');',
            "//\$this->onMonth('jan', 15, '13:00');",
            "//\$this->weeklyOn('sun', '23:00');",
            "//\$this->cron('* * * * *');"
        ], 2);
        $this->append('}', 1);
    }
}
