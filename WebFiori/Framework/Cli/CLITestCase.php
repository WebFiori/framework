<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli;

use WebFiori\Cli\Command;
use WebFiori\Cli\CommandTestCase;
use WebFiori\File\File;
use WebFiori\Framework\App;

/**
 * A base test case class that can be used to write command line commands test cases.
 *
 * @author Ibrahim
 */
class CLITestCase extends CommandTestCase {
    public function __construct($name = null, array $data = [], $dataName = "") {
        parent::__construct($name, $data, $dataName);
        $this->setRunner(App::getRunner());
    }
    /**
     * Register multiple commands and simulate the process of executing the app
     * as if in production environment.
     * 
     * @param array $argv An array that represents arguments vector. The array
     * can be indexed or associative. If associative, the key will represent
     * an option and the value of the key will represent its value. First
     * index should contain the name of the command that will be executed from
     * the registered commands. Note that first argument can be the name of
     * the class that represent the command obtained using the syntax Class::class.
     * 
     * @param array $userInputs An array that holds user inputs. Each index
     * should hold one line that represent an input to specific prompt.
     * 
     * @param array $commands An array that holds objects of type 'Command'.
     * Each object represents the registered command.
     * 
     * @param string $default A string that represents the name of the command
     * that will get executed by default if no command name is provided
     * in arguments victor.
     * 
     * @return array The method will return an array of strings that represents
     * the output of execution.
     */
    public function executeMultiCommand(array $argv = [], array $userInputs = [], array $commands = [], string $default = ''): array {
        if (count($argv) != 0) {
            if (class_exists($argv[0])) {
                $c = new $argv[0];
                if ($c instanceof Command) {
                    $argv[0] = $c->getName();
                }
            }
        }
        return parent::executeMultiCommand($argv, $userInputs, $commands, $default);
    }
    /**
     * Removes a class given its file path.
     * 
     * This method is mainly used by the test cases which creates classes. Its
     * a cleanup method.
     * 
     * @param string $classPath
     */
    public function removeClass(string $classPath) {
        $file = new File(ROOT_PATH.DS.trim($classPath,'\\').'.php');
        $file->remove();
    }
}
