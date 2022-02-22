<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\ClassWriter;
/**
 * A helper class which is used to help in creating CLI command classes using CLI.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class CreateCLIClassHelper extends CreateClassHelper {
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command);
        $this->setClassInfo(APP_DIR_NAME.'\\commands', 'Command');
        $commandName = $this->_getCommandName();
        $commandDesc = $this->getInput('Give a short description of the command:');

        if ($command->confirm('Would you like to add arguments to the command?', false)) {
            $argsArr = $this->_getArgs();
        } else {
            $argsArr = [];
        }
        $this->appendTop();
        $topArr = [
            "use webfiori\\framework\\cli\\CLICommand;",
            '/**',
            ' * A CLI command  which was created using the command "create".',
            ' *',
            " * The command will have the name '$commandName'."
        ];

        if (count($argsArr) != 0) {
            $topArr[] = ' * In addition, the command have the following args:';
            $topArr[] = ' * <ul>';

            foreach ($argsArr as $argArr) {
                $topArr[] = " * <li>".$argArr['name']."</li>";
            }
            $topArr[] = ' * </ul>';
        }
        $topArr[] = ' */';
        $topArr[] = 'class '.$this->getWriter()->getName().' extends CLICommand {';
        $this->append($topArr, 0);
        $this->_writeConstructor($commandName, $argsArr, $commandDesc);
        
        $this->append([
            '/**',
            ' * Execute the command.',
            ' */',
            'public function exec() {',
        ], 1);
        $this->append([
            '//TODO: Write the code that represents the command.',
            'return 0;',
        ], 2);
        $this->append('}', 1);

        $this->append("}");
        $this->append("return __NAMESPACE__;");

        $this->writeClass();
    }
    private function _getArgs() {
        $argsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $argArr = [];
            $groupName = $this->getInput('Enter argument name:');

            if (strlen($groupName) > 0) {
                $argArr['name'] = $groupName;
            }
            $argArr['description'] = $this->getInput('Describe this argument and how to use it:', '');
            $argArr['values'] = $this->_getFixedVals();
            $argArr['optional'] = $this->confirm('Is this argument optional or not?', true);
            $argArr['default'] = $this->getInput('Enter default value:');

            $argsArr[] = $argArr;
            $addToMore = $this->confirm('Would you like to add more arguments?', false);
        }

        return $argsArr;
    }
    private function _getCommandName() {
        return $this->getInput('Enter a name for the command:', null, function ($val)
        {
            if (strlen($val) > 0 && strpos($val, ' ') === false) {
                return true;
            }

            return false;
        });
    }
    private function _getFixedVals() {
        if (!$this->confirm('Does this argument have a fixed set of values?', false)) {
            return [];
        }
        $addVals = true;
        $valsArr = [];

        while ($addVals) {
            $val = $this->getInput('Enter the value:');

            if (!in_array($val, $valsArr)) {
                $valsArr[] = $val;
            }
            $addVals = $this->confirm('Would you like to add more values?', false);
        }

        return $valsArr;
    }

    /**
     * 
     * @param ClassWriter $writer
     * @param type $name
     * @param type $priority
     * @param array $args
     * @param string $commandDesc Description
     */
    private function _writeConstructor($name, array $args, $commandDesc) {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){'
        ], 1);

        if (count($args) > 0) {
            $this->append(["parent::__construct('$name', ["], 2);

            foreach ($args as $argArr) {
                $this->append("'".$argArr['name']."' => [", 3);

                if (strlen($argArr['description']) != 0) {
                    $this->append("'description' => '".str_replace("'", "\'", $argArr['description'])."',", 4);
                }
                $this->append("'optional' => ".($argArr['optional'] === true ? 'true' : 'false').",", 4);

                if (count($argArr['values']) != 0) {
                    $writer->append("'values' => [", 4);

                    foreach ($argArr['values'] as $val) {
                        $this->append("'".str_replace("'", "\'", $val)."',", 5);
                    }
                    $this->append("]", 4);
                }
                $this->append("],", 3);
            }
            $this->append("], '".str_replace("'", "\'", $commandDesc)."');", 2);
        } else {
            $this->append("parent::__construct('$name', '".str_replace("'", "\'", $commandDesc)."');", 2);
        }

        $this->append('}', 1);
    }
}
