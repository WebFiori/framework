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
namespace webfiori\entity\cli;

use webfiori\entity\cli\CLI;
use webfiori\entity\Util;
use webfiori\WebFiori;
/**
 * A class that represents help command of framework's CLI.
 *
 * @author Ibrahim
 * @version 1.0
 */
class HelpCommand extends CLICommand {
    public function __construct() {
        parent::__construct('--help', [
            'command-name' => [
                'optional' => true,
                'description' => 'An optional command name. If provided, help '
                .'will be specific to the given command only.'
            ]
        ], 'Display CLI Help. To display help for specific command, use the argument '
                .'"command-name" with this command.');
    }
    /**
     * Execute the help command.
     */
    public function exec() {
        $this->showVersionInfo();
        $regCommands = CLI::getRegisteredCommands();
        $commandName = $this->getArgValue('command-name');

        if ($commandName !== null) {
            if (isset($regCommands[$commandName])) {
                $this->printCommandInfo($regCommands[$commandName], true);
            } else {
                fprintf(STDOUT, "Error: Command '$commandName' is not supported.\n");
            }
        } else {
            Util::print_r("Options:");

            foreach ($regCommands as $commandObj) {
                $this->printCommandInfo($commandObj);
            }
        }
    }

    /**
     * 
     * @param CLICommand $cliCommand
     */
    private function printCommandInfo($cliCommand, $withArgs = false) {
        fprintf(STDOUT, "    %s\n", $cliCommand->getName());
        fprintf(STDOUT, "        %25s\n", $cliCommand->getDescription());

        if ($withArgs) {
            $args = $cliCommand->getArgs();

            if (count($args) != 0) {
                fprintf(STDOUT, "    Supported Arguments:\n");

                foreach ($args as $argName => $options) {
                    fprintf(STDOUT, "    %20s: ", $argName);

                    if ($options['optional']) {
                        fprintf(STDOUT, "[Optional]");
                    }

                    if (isset($options['default'])) {
                        $default = $options['default'];
                        fprintf(STDOUT, "[Default = '$default']");
                    }
                    fprintf(STDOUT, " %s\n", $options['description']);
                }
            }
        }
    }
    private function showVersionInfo() {
        Util::print_r("WebFiori Framework (c) v"
        .WebFiori::getConfig()->getVersion()." ".WebFiori::getConfig()->getVersionType().
        ", All Rights Reserved.\n");
    }
}
