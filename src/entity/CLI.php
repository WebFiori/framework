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
use webfiori\entity\cron\Cron;
/**
 * A class which adds basic support for running the framework through 
 * command line interface (CLI).
 *
 * @author Ibrahim
 * @version 1.0
 */
class CLI {
    /**
     * 
     */
    private static function showVersionInfo() {
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
        self::printCommandInfo('-h', "Show this help. Similar command: --help.");
        self::printCommandInfo('--hello', "Show 'Hello world!' Message.");
        self::printCommandInfo('--route <url>', "Test the result of a route.");
        self::printCommandInfo('--view-conf', "Display system configuration settings.");
        self::printCommandInfo('--list-cron-jobs <cron-pass>', "Display a list of cron jobs. If cron password is set, it must be provided.");
        self::printCommandInfo('--force-cron <job-name> <cron-pass>', "Force a CRON job to run.");
        self::printCommandInfo('--check-cron <cron-pass>', "Execute a command to check all jobs and execute them if its time to run the job.");
        //self::printCommandInfo('--view-privileges', "Display all created privileges groups and all privileges inside each group.");
        self::printCommandInfo('--list-routes', "Display all available routes.");
        self::printCommandInfo('--list-themes', "Display a list of available themes.");
        exit(0);
    }
    private static function forceCronJob($jobName,$cPass) {
        if($jobName === null){
            fprintf(STDERR,"Error: Job name is missing.");
        }
        else{
            if($cPass === null && Cron::password() != 'NO_PASSWORD'){
                fprintf(STDERR,"Error: CRON password is missing.");
            }
            else{
                $result = Cron::run($cPass,$jobName.'',true);
                if($result == 'INV_PASS'){
                    fprintf(STDERR,"Error: Provided password is incorrect.");
                }
                else if($result == 'JOB_NOT_FOUND'){
                    fprintf(STDERR,"Error: No job was found which has the name '".$jobName."'");
                }
                else{
                    fprintf(STDOUT,"Total number of jobs: ".$result['total-jobs']."\n");
                    fprintf(STDOUT,"Executed Jobs: ".$result['executed-count']."\n");
                    fprintf(STDOUT,"Successfully finished jobs:\n");
                    $sJobs = $result['successfully-completed'];
                    if(count($sJobs) == 0){
                        fprintf(STDOUT,"    <NONE>\n");
                    }
                    else{
                        foreach ($sJobs as $jobName){
                            fprintf(STDOUT,"    ".$jobName."\n");
                        }
                    }
                    fprintf(STDOUT,"Failed jobs:\n");
                    $fJobs = $result['failed'];
                    if(count($fJobs) == 0){
                        fprintf(STDOUT,"    <NONE>\n");
                    }
                    else{
                        foreach ($fJobs as $jobName){
                            fprintf(STDOUT,"    ".$jobName."\n");
                        }
                    }
                }
            }
        }
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
    /**
     * Checks if the framework is running through command line interface (CLI) or 
     * through a web server.
     * @return boolean If the framework is running through a command line, 
     * the method will return true. False if not.
     */
    public static function isCLI() {
        //best way to check if app is runing through CLi
        // or in a web server.
        // Did a lot of reaseach on that.
        $isCli = http_response_code() === false;
        return $isCli;
    }
    /**
     * Initialize CLI.
     */
    public static function init() {
        $isCli = self::isCLI();
        if($isCli === true){
            if(defined('CLI_HTTP_HOST')){
                $host = CLI_HTTP_HOST;
            }
            else{
                $host = '127.0.0.1';
            }
            $_SERVER['HTTP_HOST'] = $host;
            $_SERVER['REMOTE_ADDR'] = $host;
            $_SERVER['DOCUMENT_ROOT'] = trim($_SERVER['argv'][0],'WebFiori.php');
            $_SERVER['REQUEST_URI'] = '/';
            putenv('HTTP_HOST='.$host);
            putenv('REQUEST_URI=/');
            if(defined('USE_HTTP') && USE_HTTP === true){
                
            }
            else{
                $_SERVER['HTTPS'] = 'yes';
            }
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
        set_error_handler(function($errno, $errstr, $errfile, $errline){
            fprintf(STDERR, "Error Number: $errno\n");
            fprintf(STDERR, "Error String: $errstr\n");
            fprintf(STDERR, "Error File: $errfile\n");
            fprintf(STDERR, "Error Line: $errline\n");
            exit(-1);
        });
        set_exception_handler(function($ex){
            fprintf(STDERR, "Uncaught Exception.\n");
            fprintf(STDERR, "Exception Message: %s\n",$ex->getMessage());
            fprintf(STDERR, "Exception Code: %s\n",$ex->getCode());
            fprintf(STDERR, "File: %s\n",$ex->getFile());
            fprintf(STDERR, "Line: %s\n",$ex->getLine());
            fprintf(STDERR, "Stack Trace:\n");
            fprintf(STDERR, $ex->getTraceAsString());
        });
        if($_SERVER['argc'] == 1){
            self::showHelp();
        }
        else{
            if(defined('__PHPUNIT_PHAR__')){
                return;
            }
            $commands = $_SERVER['argv'];
            if($commands[1] == "--hello"){
                echo "Hello World!";
            }
            else if($commands[1] == "--route"){
                self::routeCommand();
            }
            else if($commands[1] == "-h" || $commands[1] == "--help"){
                self::showHelp();
            }
            else if($commands[1] == '--list-routes'){
                self::displayRoutes();
            }
            else if($commands[1] == '--list-themes'){
                self::viewThmes();
            }
            else if($commands[1] == '--view-conf'){
                self::showConfig();
            }
            else if($commands[1] == '--check-cron'){
                $cPass = isset($commands[2]) ? $commands[2] : null;
                self::checkCron($cPass);
            }
            else if($commands[1] == '--force-cron'){
                $jobName = isset($commands[2]) ? $commands[2] : null;
                $cPass = isset($commands[3]) ? $commands[3] : null;
                self::forceCronJob($jobName,$cPass);
            }
            else if($commands[1] == '--list-cron-jobs'){
                $pass = Cron::password();
                if($pass == 'NO_PASSWORD'){
                    self::listCron();
                }
                else{
                    $cPass = isset($commands[2]) ? $commands[2] : null;
                    if(hash('sha256',$cPass) == $pass){
                        self::listCron();
                    }
                    else if($cPass === null){
                        fprintf(STDERR, "Error: Cron password is missing.\n");
                    }
                    else{
                        fprintf(STDERR, "Error: Given password is incorrect.\n");
                    }
                }
            }
            else{
                fprintf(STDERR,"Error: The command '".$commands[1]."' is not supported or not implemented.");
            }
            //exit(0);
        }
    }
    private static function checkCron($pass=''){
        $result = Cron::run($pass);
        if($result == 'INV_PASS'){
            fprintf(STDERR,"Error: Provided password is incorrect.");
        }
        else{
            fprintf(STDOUT,"Total number of jobs: ".$result['total-jobs']."\n");
            fprintf(STDOUT,"Executed Jobs: ".$result['executed-count']."\n");
            fprintf(STDOUT,"Successfully finished jobs:\n");
            $sJobs = $result['successfully-completed'];
            if(count($sJobs) == 0){
                fprintf(STDOUT,"    <NONE>\n");
            }
            else{
                foreach ($sJobs as $jobName){
                    fprintf(STDOUT,"    ".$jobName."\n");
                }
            }
            fprintf(STDOUT,"Failed jobs:\n");
            $fJobs = $result['failed'];
            if(count($fJobs) == 0){
                fprintf(STDOUT,"    <NONE>\n");
            }
            else{
                foreach ($fJobs as $jobName){
                    fprintf(STDOUT,"    ".$jobName."\n");
                }
            }
        }
    }
    /**
     * 
     */
    private static function listCron(){
        $jobs = Cron::jobsQueue();
        $i = 1;
        fprintf(STDOUT, "Number Of Jobs: ".$jobs->size()."\n");
        while($job = $jobs->dequeue()){
            if($i < 10){
                fprintf(STDOUT, "--------- Job #0$i ---------\n");
            }
            else{
                fprintf(STDOUT, "--------- Job #$i ---------\n");
            }
            fprintf(STDOUT, "Job Name %".(18 - strlen('Job Name'))."s %s\n",":",$job->getJobName());
            fprintf(STDOUT, "Cron Expression %".(18 - strlen('Cron Expression'))."s %s\n",":",$job->getExpression());
            $i++;
        }
    }
    
    /**
     * 
     */
    private static function showConfig() {
        $spaces = 25;
        $C = WebFiori::getConfig();
        fprintf(STDOUT, "Config.php Settings:\n");
        fprintf(STDOUT, "    Framework Version %".($spaces - strlen('Framework Version'))."s %s\n",':',$C->getVersion());
        fprintf(STDOUT, "    Version Type %".($spaces - strlen('Version Type'))."s %s\n",':',$C->getVersionType());
        fprintf(STDOUT, "    Release Date %".($spaces - strlen('Release Date'))."s %s\n",':',$C->getReleaseDate());
        fprintf(STDOUT, "    Config Version %".($spaces - strlen('Config Version'))."s %s\n",':',$C->getConfigVersion());
        $isConfigured = $C->isConfig() === true ? 'Yes' : 'No';
        fprintf(STDOUT, "    Is System Configured %".($spaces - strlen('Is System Configured'))."s %s\n",':',$isConfigured);
        fprintf(STDOUT, "SiteConfig.php Settings:\n");
        $SC = WebFiori::getSiteConfig();
        fprintf(STDOUT, "    Base URL %".($spaces - strlen('Base URL'))."s %s\n",':',$SC->getBaseURL());
        fprintf(STDOUT, "    Admin Theme %".($spaces - strlen('Admin Theme'))."s %s\n",':',$SC->getAdminThemeName());
        fprintf(STDOUT, "    Base Theme %".($spaces - strlen('Base Theme'))."s %s\n",':',$SC->getBaseThemeName());
        fprintf(STDOUT, "    Title Separator %".($spaces - strlen('Title Separator'))."s %s\n",':',$SC->getTitleSep());
        fprintf(STDOUT, "    Home Page %".($spaces - strlen('Home Page'))."s %s\n",':',$SC->getHomePage());
        fprintf(STDOUT, "    Config Version %".($spaces - strlen('Config Version'))."s %s\n",':',$SC->getConfigVersion());
        fprintf(STDOUT, "    Website Names:\n",':');
        $names = $SC->getWebsiteNames();
        foreach ($names as $langCode => $name){
            fprintf(STDOUT,"        $langCode => $name\n");
        }
        fprintf(STDOUT, "    Website Descriptions:\n",':');
        foreach ($SC->getDescriptions() as $langCode => $desc){
            fprintf(STDOUT,"        $langCode => $desc\n");
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
