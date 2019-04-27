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
use webfiori\entity\Theme;
use webfiori\entity\Access;
/**
 * Description of CLIInterface
 *
 * @author Ibrahim
 */
class CLI {
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
        CLI::showVersionInfo();
        echo "Options: \n";
        self::printCommandInfo('--h', "Show this help. Similar command: --help.");
        self::printCommandInfo('--hello', "Show 'Hello world!' Message.");
        self::printCommandInfo('--route <url>', "Test the result of a route.");
        self::printCommandInfo('--view-conf', "Display system configuration settings.");
        self::printCommandInfo('--view-cron <cron-password>', "Display a list of cron jobs. If cron password is set, it must be provided.");
        self::printCommandInfo('--view-privileges', "Display all created privileges groups and all privileges inside each group.");
        self::printCommandInfo('--view-routes', "Display all available routes.");
        self::printCommandInfo('--view-themes', "Display a list of available themes.");
        exit(0);
    }
    /**
     * 
     */
    private static function viewThmes() {
        $themesArr = Theme::getAvailableThemes();
        $spaceSize = 15;
        $themsCount = count($themesArr);
        fprintf(STDOUT, "Total Number of Themes: $themsCount .\n");
        $index = 1;
        foreach ($themesArr as $themeObj){
            if($index < 10){
                fprintf(STDOUT, "------------ Theme #0$index ------------\n");
            }
            else{
                fprintf(STDOUT, "------------ Theme #$index ------------\n");
            }
            $len00 = $spaceSize - strlen('Theme Name');
            $len01 = $spaceSize - strlen('Author');
            $len02 = $spaceSize - strlen('Author URL');
            $len03 = $spaceSize - strlen('License');
            $len04 = $spaceSize - strlen('License URL');

            fprintf(STDOUT, "Theme Name: %".$len00."s %s\n",':',$themeObj->getName());
            fprintf(STDOUT, "Author: %".$len01."s %s\n",':',$themeObj->getAuthor());
            fprintf(STDOUT, "Author URL: %".$len02."s %s\n",':',$themeObj->getAuthorUrl());
            fprintf(STDOUT, "License: %".$len03."s %s\n",':',$themeObj->getLicenseName());
            fprintf(STDOUT, "License URL: %".$len04."s %s\n",':',$themeObj->getLicenseUrl());
            fprintf(STDOUT, "Theme Desription: \n%s\n",$themeObj->getDescription());
            $index++;
        }
    }
    /**
     * 
     * @param string $command
     * @param string $help
     */
    private static function printCommandInfo($command,$help){
        $dist = 30 - strlen($command);
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
            else if($commands[1] == '--view-themes'){
                self::viewThmes();
            }
            else{
                fprintf(STDERR,"Error: Command not supported or not implemented.");
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
