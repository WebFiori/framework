<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\framework\cli\commands;

use webfiori\framework\cli\CLI;
use webfiori\framework\cli\CLICommand;

/**
 * A class that represents help command of framework's CLI.
 *
 * @author Ibrahim
 * @version 1.0
 */
class HelpCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--help'. This command 
     * is used to display help information for the registered commands.
     * The command have one extra argument which is 'command-name'. If 
     * provided, the shown help will be specific to the selected command.
     */
    public function __construct() {
        parent::__construct('help', [
            '--command-name' => [
                'optional' => true,
                'description' => 'An optional command name. If provided, help '
                .'will be specific to the given command only.'
            ]
        ], 'Display CLI Help. To display help for specific command, use the argument '
                .'"--command-name" with this command.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() {
        $regCommands = CLI::getRegisteredCommands();
        $commandName = $this->getArgValue('--command-name');

        if ($commandName !== null) {
            if (isset($regCommands[$commandName])) {
                $this->printCommandInfo($regCommands[$commandName], true);
            } else {
                $this->error("Command '$commandName' is not supported.");
            }
        } else {
            if ($_SERVER['argc'] == 1) {
                $vCommand = new VersionCommand();
                $vCommand->exec();
            }
            $formattingOptions = [
                'bold' => true,
                'color' => 'light-yellow'
            ];
            $this->println("Usage:", $formattingOptions);
            $this->println("    command [arg1 arg2=\"val\" arg3...]\n");
            $this->println("Available Commands:", $formattingOptions);

            foreach ($regCommands as $commandObj) {
                $this->printCommandInfo($commandObj);
            }
        }

        return 0;
    }

    /**
     * 
     * @param CLICommand $cliCommand
     */
    private function printCommandInfo($cliCommand, $withArgs = false) {
        $this->println("    %s", $cliCommand->getName(), [
            'color' => 'yellow',
            'bold' => true
        ]);
        $this->println("        %25s\n", $cliCommand->getDescription());

        if ($withArgs) {
            $args = $cliCommand->getArgs();

            if (count($args) != 0) {
                $this->println("    Supported Arguments:", [
                    'bold' => true,
                    'color' => 'light-blue'
                ]);

                foreach ($args as $argName => $options) {
                    $this->prints("    %25s: ", $argName, [
                        'bold' => true,
                        'color' => 'yellow'
                    ]);

                    if ($options['optional']) {
                        $this->prints("[Optional]");
                    }

                    if (isset($options['default'])) {
                        $default = $options['default'];
                        $this->prints("[Default = '$default']");
                    }
                    $this->println(" %s", $options['description']);
                }
            }
        }
    }
}
