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
namespace webfiori\framework\cli;

use webfiori\WebFiori;
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
    public function exec() {
        if (CLI::getActiveCommand()->getName() == $this->getName()) {
            $this->prints("Framework Version: ", [
                'color' => 'light-blue',
                'bold' => true
            ]);
            $this->println(WebFiori::getConfig()->getVersion());
            $this->prints("Release Date: ", [
                'color' => 'light-blue',
                'bold' => true
            ]);
            $this->println(WebFiori::getConfig()->getReleaseDate());
            $this->prints("Version Type: ", [
                'color' => 'light-blue',
                'bold' => true
            ]);
            $this->println(WebFiori::getConfig()->getVersionType());
        } else {
            $this->println(
                 "|\                /|                          \n"
                ."| \      /\      / |              |  / \  |\n"
                ."\  \    /  \    /  / __________   |\/   \/|\n"
                ." \  \  /    \  /  / /  /______ /  | \/ \/ |\n"
                ."  \  \/  /\  \/  / /  /           |  \ /  |\n"
                ."   \    /  \    / /  /______      |\  |  /|\n"
                ."    \  /    \  / /  /______ /       \ | /  \n"
                ."     \/  /\  \/ /  /                  |    \n"
                ."      \ /  \ / /  /                   |    \n"
                ."       ______ /__/                    |    \n");

            $this->prints('WebFiori Framework ', [
                'color' => 'light-green',
                'bold' => true
            ]);
            $this->prints(' (c) Version ');
            $this->println(WebFiori::getConfig()->getVersion()." ".WebFiori::getConfig()->getVersionType()."\n\n", [
                'color' => 'light-yellow',
                'bold' => true
            ]);
        }
    }
}
