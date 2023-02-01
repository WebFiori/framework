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

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\CLICommandClassWriter;
use webfiori\cli\InputValidator;
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
        parent::__construct($command, new CLICommandClassWriter());
        $this->setClassInfo(APP_DIR.'\\commands', 'Command');
        $commandName = $this->_getCommandName();
        $commandDesc = $this->getInput('Give a short description of the command:');

        if ($command->confirm('Would you like to add arguments to the command?', false)) {
            $argsArr = $this->getArgs();
        } else {
            $argsArr = [];
        }
        $this->getWriter()->setCommandName($commandName);
        $this->getWriter()->setCommandDescription($commandDesc);
        $this->getWriter()->setArgs($argsArr);
        
        $this->writeClass();
    }
    private function getArgs() {
        $argsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $argArr = [];
            $groupName = $this->getInput('Enter argument name:');

            if (strlen($groupName) > 0) {
                $argArr['name'] = $groupName;
            }
            $argArr['description'] = $this->getInput('Describe this argument and how to use it:', '');
            $argArr['values'] = $this->getFixedVals();
            $argArr['optional'] = $this->confirm('Is this argument optional or not?', true);
            $argArr['default'] = $this->getInput('Enter default value:');

            $argsArr[] = $argArr;
            $addToMore = $this->confirm('Would you like to add more arguments?', false);
        }

        return $argsArr;
    }
    private function _getCommandName() {
        return $this->getInput('Enter a name for the command:', null, new InputValidator(function ($val)
        {
            if (strlen($val) > 0 && strpos($val, ' ') === false) {
                return true;
            }

            return false;
        }));
    }
    private function getFixedVals() {
        if (!$this->confirm('Does this argument have a fixed set of values?', false)) {
            return [];
        }
        $addVals = true;
        $valsArr = [];

        while ($addVals) {
            $val = $this->getInput('Enter the value:');

            if (!in_array($val, $valsArr)) {
                $valsArr[] = $val;
            } else {
                $this->info('Given value was already added.');
            }
            $addVals = $this->confirm('Would you like to add more values?', false);
        }

        return $valsArr;
    }
}
