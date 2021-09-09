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
class CreateCronJob {
    /**
     *
     * @var CLICommand 
     */
    private $command;
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->command = $command;
        $classInfo = $command->getClassInfo(APP_DIR_NAME.'\\jobs');
        $jobName = $this->_getJobName();

        if ($command->confirm('Would you like to add arguments to the job?', false)) {
            $argsArr = $this->_getArgs();
        } else {
            $argsArr = [];
        }
        $writer = new ClassWriter($classInfo);

        $writer->append('<?php');
        $writer->append("namespace ".$writer->getNamespace().";\n");
        $writer->append("use webfiori\\framework\\cron\\AbstractJob;");
        $writer->append("use webfiori\\framework\\cron\\CronEmail;");
        $writer->append("use webfiori\\framework\\cron\\Cron;\n");
        $writer->append('/**');
        $writer->append(' * A background process which was created using the command "create".');
        $writer->append(' *');
        $writer->append(" * The process will have the name '$jobName'.");

        if (count($argsArr) != 0) {
            $writer->append(' * In addition, the proces have the following args:');
            $writer->append(' * <ul>');

            foreach ($argsArr as $argName) {
                $writer->append(" * <li>$argName</li>");
            }
            $writer->append(' * </ul>');
        }
        $writer->append(' */');
        $writer->append('class '.$writer->getName().' extends AbstractJob {');

        $this->_writeConstructor($writer, $jobName, $argsArr);

        $writer->append('/**', 1);
        $writer->append(' * Execute the process.', 1);
        $writer->append(' */', 1);
        $writer->append('public function execute() {', 1);
        $writer->append('//TODO: Write the code that represents the process.', 2);
        $writer->append('}', 1);

        $writer->append('/**', 1);
        $writer->append(' * Execute a set of instructions when the job faild to complete without errors.', 1);
        $writer->append(' */', 1);
        $writer->append('public function onFail() {', 1);
        $writer->append('//TODO: Implement the action to perform when the job fails to complete without errors.', 2);
        $writer->append('}', 1);

        $writer->append('/**', 1);
        $writer->append(' * Execute a set of instructions when the job completed without errors.', 1);
        $writer->append(' */', 1);
        $writer->append('public function onSuccess() {', 1);
        $writer->append('//TODO: Implement the action to perform when the job executes without errors.', 2);
        $writer->append('}', 1);

        $writer->append('/**', 1);
        $writer->append(' * Execute a set of instructions after the job has finished to execute.', 1);
        $writer->append(' */', 1);
        $writer->append('public function afterExec() {', 1);
        $writer->append('//TODO: Implement the action to perform when the job finishes to execute.', 2);
        $writer->append("//\$email = new CronEmail('no-reply', [", 2);
        $writer->append("//    'webfiori@example.com' => 'Ibrahim Ali'", 2);
        $writer->append('//]);', 2);
        $writer->append('}', 1);

        $writer->append("}");

        $writer->writeClass();
        $command->info('New background job class was created at "'.$writer->getPath().'".');
    }
    private function _getArgs() {
        $argsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $groupName = $this->_getCommand()->getInput('Enter argument name:');

            if (strlen($groupName) > 0) {
                $argsArr[] = $groupName;
            }
            $addToMore = $this->_getCommand()->confirm('Would you like to add more arguments?', false);
        }

        return $argsArr;
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
    }
    private function _getJobName() {
        return $this->_getCommand()->getInput('Enter a name for the job:', null, function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        });
    }
    /**
     * 
     * @param ClassWriter $writer
     * @param type $name
     * @param type $priority
     * @param array $args
     */
    private function _writeConstructor($writer, $name,array $args) {
        $writer->append('/**', 1);
        $writer->append(' * Creates new instance of the class.', 1);
        $writer->append(' */', 1);
        $writer->append('public function __construct(){', 1);
        $writer->append("parent::__construct('$name');", 2);

        if (count($args) > 0) {
            $writer->append('$this->addExecutionArgs([', 2);

            foreach ($args as $gName) {
                $writer->append("'$gName',", 3);
            }
            $writer->append(']);', 2);
        }
        $writer->append('// TODO: Specify the time at which the process will run at.', 2);
        $writer->append('// You can use one of the following methods to specifiy the time:', 2);

        $writer->append('//$this->dailyAt(4, 30)', 2);
        $writer->append('//$this->everyHour();', 2);
        $writer->append('//$this->everyMonthOn(1, \'00:00\');', 2);
        $writer->append("//\$this->onMonth('jan', 15, '13:00');", 2);
        $writer->append("//\$this->weeklyOn('sun', '23:00');", 2);
        $writer->append("//\$this->cron('* * * * *');", 2);

        $writer->append('}', 1);
    }
}
