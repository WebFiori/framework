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
 * Description of VersionCommand
 *
 * @author Ibrahim
 */
class VersionCommand extends CLICommand {
    public function __construct() {
        parent::__construct('v', [], 'Display framework version info.');
    }
    /**
     * Execute the command
     */
    public function exec() : int {
        if (CLI::getActiveCommand()->getName() == $this->getName()) {
            $formattingOptions = [
                'color' => 'light-blue',
                'bold' => true
            ];
            $this->prints("Framework Version: ", $formattingOptions);
            $this->println(WF_VERSION);
            $this->prints("Release Date: ", $formattingOptions);
            $this->println(WF_RELEASE_DATE);
            $this->prints("Version Type: ", $formattingOptions);
            $this->println(WF_VERSION_TYPE);
        } else {
            $wfLogo = "|\                /|                          \n"
                ."| \      /\      / |              |  / \  |\n"
                ."\  \    /  \    /  / __________   |\/   \/|\n"
                ." \  \  /    \  /  / /  /______ /  | \/ \/ |\n"
                ."  \  \/  /\  \/  / /  /           |  \ /  |\n"
                ."   \    /  \    / /  /______      |\  |  /|\n"
                ."    \  /    \  / /  /______ /       \ | /  \n"
                ."     \/  /\  \/ /  /                  |    \n"
                ."      \ /  \ / /  /                   |    \n"
                ."       ______ /__/                    |    \n";
            $this->println($wfLogo);
            $formattingOptions = [
                'color' => 'light-green',
                'bold' => true
            ];
            $this->prints('WebFiori Framework ', $formattingOptions);
            $this->prints(' (c) Version ');
            $this->println(WF_VERSION." ".WF_VERSION_TYPE."\n\n", $formattingOptions);
        }
    }
}
