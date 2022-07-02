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

use webfiori\cli\CLICommand;
use webfiori\framework\router\Router;
/**
 * A CLI command which is used to show a list of all added routes.
 *
 * @author Ibrahim
 */
class ListRoutesCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--list-routes'. This command 
     * is used to list all registered routes and at which resource they 
     * point to.
     */
    public function __construct() {
        parent::__construct('list-routes', [], 'List all created routes and which resource they point to.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $routesArr = Router::routes();
        $maxRouteLen = 0;

        foreach ($routesArr as $requestedUrl => $routeTo) {
            $len = strlen($requestedUrl);

            if ($len > $maxRouteLen) {
                $maxRouteLen = $len;
            }
        }
        $maxRouteLen += 4;

        foreach ($routesArr as $requestedUrl => $routeTo) {
            $location = $maxRouteLen - strlen($requestedUrl);

            if (gettype($routeTo) == 'object') {
                $this->println("$requestedUrl %".$location."s <object>", " => ");
            } else {
                $this->println("$requestedUrl %".$location."s $routeTo"," => ");
            }
        }

        return 0;
    }
}
