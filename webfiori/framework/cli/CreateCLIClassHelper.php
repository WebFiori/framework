<?php
namespace webfiori\framework\cli;

/**
 * A helper class which is used to help in creating cron jobs classes using CLI.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class CreateCLIClassHelper {
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
        $classInfo = $command->getClassInfo(APP_DIR_NAME.'\\commands');
        $commandName = $this->_getCommandName();

        if ($command->confirm('Would you like to add arguments to the command?', false)) {
            $argsArr = $this->_getArgs();
        } else {
            $argsArr = [];
        }
        $writer = new ClassWriter($classInfo);

        $writer->append('<?php');
        $writer->append("namespace ".$writer->getNamespace().";\n");
        $writer->append("use webfiori\\framework\\cli\\CLICommand;");
        $writer->append('/**');
        $writer->append(' * A CLI command  which was created using the command "create".');
        $writer->append(' *');
        $writer->append(" * The command will have the name '$commandName'.");

        if (count($argsArr) != 0) {
            $writer->append(' * In addition, the command have the following args:');
            $writer->append(' * <ul>');

            foreach ($argsArr as $argArr) {
                $writer->append(" * <li>".$argArr['name']."</li>");
            }
            $writer->append(' * </ul>');
        }
        $writer->append(' */');
        $writer->append('class '.$writer->getName().' extends CLICommand {');

        $this->_writeConstructor($writer, $commandName, $argsArr);

        $writer->append('/**', 1);
        $writer->append(' * Execute the command.', 1);
        $writer->append(' */', 1);
        $writer->append('public function exec() {', 1);
        $writer->append('//TODO: Write the code that represents the command.', 2);
        $writer->append('return 0;', 2);
        $writer->append('}', 1);

        $writer->append("}");

        $writer->writeClass();
        $command->info('New CLI class was created at "'.$writer->getPath().'".');
    }
    private function _getArgs() {
        $argsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $argArr = [];
            $groupName = $this->_getCommand()->getInput('Enter argument name:');

            if (strlen($groupName) > 0) {
                
                $argArr['name'] = $groupName;
            }
            $argArr['description'] = $this->_getCommand()->getInput('Describe this argument and how to use it:', '');
            $argArr['values'] = $this->_getFixedVals();
            $argArr['optional'] = $this->_getCommand()->confirm('Is this argument optional or not?', true);
            $argArr['default'] = $this->_getCommand()->getInput('Enter default value:');
            
            $argsArr[] = $argArr;
            $addToMore = $this->_getCommand()->confirm('Would you like to add more arguments?', false);
        }

        return $argsArr;
    }
    private function _getFixedVals() {
        
        if (!$this->_getCommand()->confirm('Does this argument have a fixed set of values?', false)) {
            return [];
        }
        $addVals = true;
        $valsArr = [];
        
        while ($addVals) {
            $val = $this->_getCommand()->getInput('Enter the value:');
            
            if (!in_array($val, $valsArr)) {
                $valsArr[] = $val;
            }
            $addVals = $this->_getCommand()->confirm('Would you like to add more values?', false);
        }
        return $valsArr;
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
    }
    private function _getCommandName() {
        return $this->_getCommand()->getInput('Enter a name for the command:', null, function ($val)
        {
            if (strlen($val) > 0) {
                if (strpos($val, ' ') === false) {
                    return true;
                }
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
        $writer->append("parent::__construct('$name', [", 2);

        if (count($args) > 0) {

            foreach ($args as $argArr) {
                $writer->append("'".$argArr['name']."' => [", 3);
                if (strlen($argArr['description']) != 0) {
                    $writer->append("'description' => ". str_replace("'", "\'", $argArr['description'])."',", 4);
                }
                $writer->append("'optional' => ".($argArr['optional'] === true ? 'true' : 'false').",", 4);
                if (count($argArr['values']) != 0) {
                    $writer->append("'values' => [", 4);
                    foreach ($argArr['values'] as $val) {
                        $writer->append("'". str_replace("'", "\'", $val)."',", 4);
                    }
                    $writer->append("]", 3);
                }
            }
            $writer->append(']', 2);
        }
        $writer->append(');', 2);
        $writer->append('// TODO: Specify the time at which the process will run at.', 2);
        $writer->append('// You can use one of the following methods to specifiy the time:', 2);

        $writer->append('}', 1);
    }
}
