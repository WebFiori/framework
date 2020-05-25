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
namespace webfiori\entity\cli;

use webfiori\entity\Util;
use webfiori\ini\InitCliCommands;

/**
 * A class which adds basic support for running the framework through 
 * command line interface (CLI).
 * In addition to adding support for CLI, this class is used to register any 
 * custom commands which are created by developers. Also, it initialize some of 
 * the attributes of the framework in order to use it in CLI environment.
 * @author Ibrahim
 * @version 1.0.2
 */
class CLI {
    /**
     * The command that will be executed now.
     * @var CLICommand|null
     * @since 1.0.2 
     */
    private $activeCommand;
    /**
     *
     * @var An associative array that contains supported commands. 
     * @since 1.0.2
     */
    private $commands;
    /**
     *
     * @var CLI 
     */
    private static $inst;
    private function __construct() {
        $this->commands = [];
        $isCli = self::isCLI();

        if ($isCli === true) {
            if (defined('CLI_HTTP_HOST')) {
                $host = CLI_HTTP_HOST;
            } else {
                $host = '127.0.0.1';
            }
            $_SERVER['HTTP_HOST'] = $host;
            $_SERVER['REMOTE_ADDR'] = $host;
            $_SERVER['DOCUMENT_ROOT'] = trim(filter_var($_SERVER['argv'][0], FILTER_SANITIZE_STRING),'WebFiori.php');
            $_SERVER['REQUEST_URI'] = '/';
            putenv('HTTP_HOST='.$host);
            putenv('REQUEST_URI=/');

            if (defined('USE_HTTP') && USE_HTTP === true) {
                $_SERVER['HTTPS'] = 'no';
            } else {
                $_SERVER['HTTPS'] = 'yes';
            }
        }
    }
    /**
     * Display PHP error information in CLI.
     * @param int $errno Error number
     * @param string $errstr The error as string.
     * @param string $errfile The file at which the error has accrued in.
     * @param int $errline Line number at which the error has accrued in.
     * @since 1.0.2
     */
    public static function displayErr($errno, $errstr, $errfile, $errline) {
        fprintf(STDERR, CLICommand::formatOutput("<".Util::ERR_TYPES[$errno]['type'].">\n", [
            'color' => 'red',
            'bold' => true,
            'blink' => true
        ]));
        fprintf(STDERR, "Error Message    %5s %s\n",":",$errstr);
        fprintf(STDERR, "Error Number     %5s %s\n",":",$errno);
        fprintf(STDERR, "Error Description%5s %s\n",":",Util::ERR_TYPES[$errno]['description']);
        fprintf(STDERR, "Error File       %5s %s\n",":",$errfile);
        fprintf(STDERR, "Error Line:      %5s %s\n",":",$errline);
        exit(-1);
    }
    /**
     * Display exception information in terminal.
     * @param Exception $ex An exception which is thrown any time during 
     * program execution.
     * @since 1.0.2
     */
    public static function displayException($ex) {
        fprintf(STDERR, CLICommand::formatOutput("Uncaught Exception\n", [
            'color' => 'red',
            'bold' => true,
            'blink' => true
        ]));
        fprintf(STDERR, CLICommand::formatOutput('Exception Message: ', [
            'color' => 'yellow',
            'bold' => true,
        ]));
        fprintf(STDERR, $ex->getMessage()."\n");
        fprintf(STDERR, "Exception Class: %s\n", get_class($ex));
        fprintf(STDERR, "Exception Code: %s\n",$ex->getCode());
        fprintf(STDERR, "File: %s\n",$ex->getFile());
        fprintf(STDERR, "Line: %s\n",$ex->getLine());
        fprintf(STDERR, "Stack Trace:\n");
        fprintf(STDERR, $ex->getTraceAsString());
    }
    /**
     * 
     * @return CLICommand|null
     */
    public static function getActiveCommand() {
        return self::get()->activeCommand;
    }
    /**
     * Returns an associative array of registered commands.
     * @return array The method will return an associative array. The keys of 
     * the array are the names of the commands and the value of the key is 
     * an object of type 'CLICommand'.
     * @since 1.0.2
     */
    public static function getRegisteredCommands() {
        return self::get()->commands;
    }
    /**
     * Initialize CLI.
     * @since 1.0
     */
    public static function init() {
        self::get();
    }
    /**
     * Checks if the framework is running through command line interface (CLI) or 
     * through a web server.
     * @return boolean If the framework is running through a command line, 
     * the method will return true. False if not.
     * @since 1.0
     */
    public static function isCLI() {
        //best way to check if app is runing through CLi
        // or in a web server.
        // Did a lot of reaseach on that.
        return http_response_code() === false;
    }
    /**
     * Register new command.
     * @param CLICommand $cliCommand The command that will be registered.
     * @since 1.0.2
     */
    public static function register($cliCommand) {
        if ($cliCommand instanceof CLICommand) {
            self::get()->_regCommand($cliCommand);
        }
    }
    /**
     * Register CLI commands.
     * This method will register the commands which are bundled with the 
     * framework first. Once it is finished, it will register any commands which 
     * are created by the developer using the method InitCliCommands::init(). This 
     * method should be only used during initialization stage. Calling it again 
     * will have no effect.
     * @since 1.0
     */
    public static function registerCommands() {
        //Register default framework cli commands.
        self::register(new HelpCommand());
        self::register(new VersionCommand());
        self::register(new SettingsCommand());
        self::register(new ListThemesCommand());
        self::register(new ListCronCommand());
        self::register(new ListRoutesCommand());
        self::register(new CronCommand());
        self::register(new TestRouteCommand());
        //Call this method to register any user-defined commands.
        InitCliCommands::init();
    }
    /**
     * Run the provided CLI command.
     * @return int If the CLI is completed without any errors, the method will 
     * return 0. 
     */
    public static function runCLI() {
        if ($_SERVER['argc'] == 1) {
            $command = self::get()->commands['help'];
            self::get()->activeCommand = $command;
            return $command->excCommand();
        } else if (defined('__PHPUNIT_PHAR__')) {
            return 0;
        }

        return self::get()->_runCommand();
    }
    private function _regCommand($command) {
        $this->commands[$command->getName()] = $command;
    }
    private function _runCommand() {
        $args = $_SERVER['argv'];
        $commandName = filter_var($args[1], FILTER_SANITIZE_STRING);

        if (isset($this->commands[$commandName])) {
            $command = self::get()->commands[$commandName];
            $this->activeCommand = $command;
            return $command->excCommand();
        } else {
            fprintf(STDERR,"Error: The command '".$commandName."' is not supported.");

            return -1;
        }
    }
    /**
     * 
     * @return CLI
     */
    private static function get() {
        if (self::$inst == null) {
            self::$inst = new CLI();
        }

        return self::$inst;
    }
}
