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
    public function __construct() {
        parent::__construct('--route', [
            'url' => [
                'optional' => false,
                'description' => 'The URL that will be tested if it has a '
                .'route or not.'
            ]
        ], 'Test the result of routing to a URL');
    }

    public function exec() {
        $url = $this->getArgValue('url');
        fwrite(STDOUT, "Trying to route to \"".$url."\"...\n");
        Router::route($url);
    }
}