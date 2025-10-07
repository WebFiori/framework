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
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Cli\InputValidator;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\CommandClassWriter;
/**
 * A helper class which is used to help in creating CLI command classes using CLI.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class CreateCLIClassHelper extends CreateClassHelper {
    /**
     * @var CommandClassWriter
     */
    private $cliWriter;
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new CommandClassWriter());
        $this->cliWriter = $this->getWriter();
    }
    public function readClassInfo() {
        $this->setClassInfo(APP_DIR.'\\commands', 'Command');
        $commandName = $this->getCommandName();
        $commandDesc = $this->getInput('Give a short description of the command:');

        if ($this->getCommand()->confirm('Would you like to add arguments to the command?', false)) {
            $argsArr = $this->getArgs();
        } else {
            $argsArr = [];
        }
        $this->cliWriter->setCommandName($commandName);
        $this->cliWriter->setCommandDescription($commandDesc);
        $this->cliWriter->setArgs($argsArr);

        $this->writeClass();
    }
    private function getArgs() : array {
        $argsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $argObj = new \WebFiori\Cli\Argument();
            $argName = $this->getInput('Enter argument name:');

            if (!$argObj->setName($argName)) {
                $this->error('Invalid name provided.');
                continue;
            }
            $argObj->setDescription($this->getInput('Describe this argument and how to use it:', ''));

            foreach ($this->getFixedValues() as $v) {
                $argObj->addAllowedValue($v);
            }
            $argObj->setIsOptional($this->confirm('Is this argument optional or not?', true));
            $argObj->setDefault($this->getInput('Enter default value:').'');

            $argsArr[] = $argObj;
            $addToMore = $this->confirm('Would you like to add more arguments?', false);
        }

        return $argsArr;
    }
    private function getCommandName(): string {
        return $this->getInput('Enter a name for the command:', null, new InputValidator(function ($val)
        {
            if (strlen($val) > 0 && strpos($val, ' ') === false) {
                return true;
            }

            return false;
        }));
    }
    private function getFixedValues() : array {
        if (!$this->confirm('Does this argument have a fixed set of values?', false)) {
            return [];
        }
        $addValues = true;
        $valuesArr = [];

        while ($addValues) {
            $val = $this->getInput('Enter the value:');

            if (!in_array($val, $valuesArr)) {
                $valuesArr[] = $val;
            } else {
                $this->info('Given value was already added.');
            }
            $addValues = $this->confirm('Would you like to add more values?', false);
        }

        return $valuesArr;
    }
}
