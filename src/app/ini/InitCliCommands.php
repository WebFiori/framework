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
namespace webfiori\ini;

use webfiori\framework\cli\CLI;
/**
 * A class that contains one method for registering custom CLI 
 * commands.
 *
 * @author Ibrahim
 */
class InitCliCommands {
    /**
     * Register user defined CLI commands.
     * 
     * This method can be used by the developers to add any custom 
     * CLI command that they have created that does not exist in the folder 
     * 'app/commands'. Assuming that we have a 
     * custom command with the name 'ProcessEmailCommand', then it 
     * can be registered as follows:<br/>
     * <code>
     * CLI::register(new ProcessEmailCommand());
     * </code>
     */
    public static function init() {
    }
}
