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

use webfiori\entity\router\Router;
/**
 * A CLI Command which is used to test the result of routing to a specific 
 * route.
 *
 * @author Ibrahim
 */
class TestRouteCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--route'. In addition to that, 
     * it will have the following arguments:
     * <ul>
     * <li><b>url</b>: The URL at which its route will be tested.</li>
     * </ul>
     */
    public function __construct() {
        parent::__construct('route', [
            '--url' => [
                'optional' => false,
                'description' => 'The URL that will be tested if it has a '
                .'route or not.'
            ]
        ], 'Test the result of routing to a URL');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() {
        $url = $this->getArgValue('--url');
        $this->println("Trying to route to \"".$url."\"...");
        Router::route($url);

        return 0;
    }
}
