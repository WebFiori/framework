<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity;
use webfiori\WebFiori;
use webfiori\entity\router\Router;
/**
 * Description of CLIInterface
 *
 * @author Ibrahim
 */
class CLIInterface {
    /**
     * 
     */
    public static function showVersionInfo() {
        echo "WebFiori Framework (c) v"
        .WebFiori::getConfig()->getVersion()." ".WebFiori::getConfig()->getVersionType().
        ", All Rights Reserved.\n";
    }
    /**
     * 
     */
    private static function showHelp() {
        CLIInterface::showVersionInfo();
        echo "Options: \n";
        self::printCommandInfo('--h', "Show this help. Similar command: --help.");
        self::printCommandInfo('--hello', "Show 'Hello world' Message.");
        self::printCommandInfo('--route <url>', "Test the result of a route.");
        self::printCommandInfo('--show-routes', "Display all available routes.");
        exit(0);
    }
    /**
     * 
     * @param string $command
     * @param string $help
     */
    private static function printCommandInfo($command,$help){
        $dist = 20 - strlen($command);
        fprintf(STDOUT, "    %s %".$dist."s %s\n", $command,":",$help);
    }

    public static function init() {
        $sapi = php_sapi_name();
        if($sapi == 'cli'){
            $_SERVER['HTTP_HOST'] = '127.0.0.1';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['DOCUMENT_ROOT'] = trim($_SERVER['argv'][0],'WebFiori.php');
            putenv('HTTP_HOST=127.0.0.1');
            putenv('REQUEST_URI=example');
        }
    }
    /**
     * 
     */
    private static function displayRoutes() {
        $routesArr = Router::routes();
        $maxRouteLen = 0;
        foreach ($routesArr as $requestedUrl => $routeTo){
            $len = strlen($requestedUrl);
            if($len > $maxRouteLen){
                $maxRouteLen = $len;
            }
        }
        $maxRouteLen += 4;
        foreach ($routesArr as $requestedUrl => $routeTo){
            $location = $maxRouteLen - strlen($requestedUrl);
            if(gettype($routeTo) == 'object'){
                fprintf(STDOUT, "$requestedUrl %".$location."s <object>\n", " => ");
            }
            else{
                fprintf(STDOUT, "$requestedUrl %".$location."s $routeTo\n"," => ");
            }
        }
    }
    /**
     * 
     */
    public static function runCLI() {
        if($_SERVER['argc'] == 1){
            self::showHelp();
        }
        else{
            $commands = $_SERVER['argv'];
            if($commands[1] == "--hello"){
                echo "Hello World!";
            }
            else if($commands[1] == "--route"){
                self::routeCommand();
            }
            else if($commands[1] == "--h" || $commands[1] == "--help"){
                self::showHelp();
            }
            else if($commands[1] == '--show-routes'){
                self::displayRoutes();
            }
            exit(0);
        }
    }
    /**
     * 
     */
    private static function routeCommand() {
        $commands = $_SERVER['argv'];
        if(isset($commands[2])){
            $url = $commands[2];
            fwrite(STDOUT, "Trying to route to \"".$url."\"...\n");
            Router::route($url);
        }
        else{
            fwrite(STDERR, "Error: The argument <url> is missing.\n");
        }
    }
}
