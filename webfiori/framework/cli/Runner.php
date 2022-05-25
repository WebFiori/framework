<?php
namespace webfiori\framework\cli;

use webfiori\framework\cli\CLICommand;
use webfiori\framework\ConfigController;
use webfiori\framework\WebFioriApp;
use webfiori\framework\cli\commands\AddCommand;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\commands\CronCommand;
use webfiori\framework\cli\commands\HelpCommand;
use webfiori\framework\cli\commands\ListCronCommand;
use webfiori\framework\cli\commands\ListRoutesCommand;
use webfiori\framework\cli\commands\ListThemesCommand;
use webfiori\framework\cli\commands\RunSQLQueryCommand;
use webfiori\framework\cli\commands\SettingsCommand;
use webfiori\framework\cli\commands\TestRouteCommand;
use webfiori\framework\cli\commands\UpdateSettingsCommand;
use webfiori\framework\cli\commands\UpdateTableCommand;
use webfiori\framework\cli\commands\VersionCommand;
/**
 * The core class which is used to manage command line related operations.
 *
 * @author Ibrahim
 */
class Runner {
    private $commandExitVal;
    /**
     * 
     * @var InputStream
     * 
     */
    private $inputStream;
    /**
     * The command that will be executed now.
     * 
     * @var CLICommand|null
     */
    private $activeCommand;
    /**
     * An associative array that contains supported commands. 
     * 
     * @var array
     * 
     */
    private $commands;
    /**
     * An attribute which is set to true if CLI is running in interactive mode 
     * or not.
     * 
     * @var boolean
     */
    private $isInteractive;
    /**
     * 
     * @var OutputStream
     */
    private $outputStream;
    private static $inst;
    /**
     * Returns an instance of the class.
     * 
     * @return Runner An instance of the class. Multiple calls to same method
     * will result in returning same instance.
     */
    public static function get() {
        if (self::$inst === null) {
            self::$inst = new Runner();
        }
        return self::$inst;
    }
    /**
     * Register new command.
     * 
     * @param CLICommand $cliCommand The command that will be registered.
     * 
     */
    public static function register(CLICommand $cliCommand) {
        self::get()->commands[$cliCommand->getName()] = $cliCommand;
    }
    private function __construct() {
        $this->commands = [];
        $this->isInteractive = false;
        $this->inputStream = new StdIn();
        $this->outputStream = new StdOut();
        if (self::isCLI()) {
            if (isset($_SERVER['argv'])) {
                foreach ($_SERVER['argv'] as $arg) {
                    $this->isInteractive = $arg == '-i' || $this->isInteractive;
                }
            }

            if (defined('CLI_HTTP_HOST')) {
                $host = CLI_HTTP_HOST;
            } else {
                $host = '127.0.0.1';
                define('CLI_HTTP_HOST', $host);
            }
            $_SERVER['HTTP_HOST'] = $host;
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['DOCUMENT_ROOT'] = ROOT_DIR;
            $_SERVER['REQUEST_URI'] = '/';
            putenv('HTTP_HOST='.$host);
            putenv('REQUEST_URI=/');

            if (defined('USE_HTTP') && USE_HTTP === true) {
                $_SERVER['HTTPS'] = 'no';
            } else {
                $_SERVER['HTTPS'] = 'yes';
            }
            
            WebFioriApp::autoRegister('commands', function ($instance)
            {
                Runner::register($instance);
            });
        }
        if (!class_exists(APP_DIR_NAME.'\ini\InitCliCommands')) {
            ConfigController::get()->createIniClass('InitCliCommands', 'Register user defined CLI commands.');
        }
    }
    /**
     * Reset input stream, output stream and, registered commands to default.
     */
    public static function reset() {
        self::get()->inputStream = new StdIn();
        self::get()->outputStream = new StdOut();
        self::get()->commands = [];
    }
    /**
     * Checks if CLI is running in interactive mode or not.
     * 
     * @return boolean If CLI is running in interactive mode, the method will 
     * return true. False otherwise.
     * 
     */
    public static function isIntaractive() : bool {
        return self::get()->isInteractive;
    }
    /**
     * Returns the stream at which the engine is using to get inputs.
     * 
     * @return InputStream The default input stream is 'StdIn'.
     */
    public static function getInputStream() : InputStream {
        return self::get()->inputStream;
    }
    /**
     * Returns the stream at which the engine is using to send outputs.
     * 
     * @return OutputStream The default input stream is 'StdOut'.
     */
    public static function getOutputStream() : OutputStream {
        return self::get()->outputStream;
    }
    /**
     * Returns an associative array of registered commands.
     * 
     * @return array The method will return an associative array. The keys of 
     * the array are the names of the commands and the value of the key is 
     * an object that holds command information.
     * 
     */
    public static function getCommands() : array {
        return self::get()->commands;
    }
    /**
     * Executes a command given as object.
     * 
     * @param CLICommand $c The command that will be executed.
     * 
     * @param array $args An optional array that can hold command arguments.
     * The keys of the array should be arguments names and the value of each index
     * is the value of the argument.
     * 
     * @return int The method will return an integer that represents exit status of
     * running the command. Usually, if the command exit with a number other than 0,
     * it means that there was an error in execution.
     */
    public static function runCommand(CLICommand $c = null, array $args = []) {
        
        if ($c === null) {
            if (count($args) === 0) {
                $args = $_SERVER['argv'];
            }
            $commandName = filter_var($args[1], FILTER_DEFAULT);

            if (isset(self::get()->commands[$commandName])) {
                $c = self::get()->commands[$commandName];

            } else {
                self::getOutputStream()->println("Error: The command '".$commandName."' is not supported.");

                return -1;
            }
        }
        
        foreach ($args as $argName => $argVal) {
            $c->setArgValue($argName, $argVal);
        }
        self::get()->commandExitVal = $c->excCommand();
        return self::get()->commandExitVal;
    }
    /**
     * Sets the stream at which the runner will be using to read inputs from.
     * 
     * @param InputStream $stream The new stream that will holds inputs.
     */
    public static function setInputStream(InputStream $stream) {
        self::get()->inputStream = $stream;
    }
    /**
     * Sets the stream at which the runner will be using to send outputs to.
     * 
     * @param OutputStream $stream The new stream that will holds inputs.
     */
    public static function setOutputStream(OutputStream $stream) {
        self::get()->outputStream = $stream;
    }
    private static function readInteractiv() {
        $input = self::getInputStream()->readLine();
        $args = explode(' ', $input);
        return $args;
    }
    /**
     * Register CLI commands.
     * 
     * This method will register the commands which are bundled with the 
     * framework first. Once it is finished, it will register any commands which 
     * are created by the developer using the method InitCliCommands::init(). This 
     * method should be only used during initialization stage. Calling it again 
     * will have no effect.
     * 
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
        self::register(new CreateCommand());
        self::register(new AddCommand());
        self::register(new UpdateTableCommand());
        self::register(new RunSQLQueryCommand());
        self::register(new UpdateSettingsCommand());
        //Call this method to register any user-defined commands.
        call_user_func(APP_DIR_NAME.'\ini\InitCliCommands::init');
    }
    /**
     * Start command line process.
     * 
     * @return int The method will return an integer that represents exit status of
     * the process. Usually, if the process exit with a number other than 0,
     * it means that there was an error in execution.
     */
    public static function start() : int {
        self::registerCommands();
        if (self::isIntaractive()) {
            self::getOutputStream()->println('Running CLI in interactive mode.');
            self::getOutputStream()->println('WF-CLI > Type commant name or "exit" to close.');
            self::getOutputStream()->prints('>>');
            $exit = false;

            while (!$exit) {
                $args = self::readInteractiv();
                $argsCount = count($args);

                if ($argsCount >= 2) {
                    $exit = $args[1] == 'exit';

                    if (!$exit) {
                        self::run($args);
                    }
                }
                self::getOutputStream()->prints('>>');
            }
        } else {
            return self::run($_SERVER['argv']);
        }
        return 0;
    }
    private static function run($args) {
        if (count($args) == 1) {
            $command = self::get()->commands['help'];

            if (!defined('__PHPUNIT_PHAR__')) {
                return self::runCommand($command);
            }
        } else {
            if (defined('__PHPUNIT_PHAR__')) {
                $command = self::get()->commands['help'];
                return self::runCommand($command);
            }
        }

        return self::runCommand();
    }
    /**
     * Returns an associative array of registered commands.
     * 
     * @return array The method will return an associative array. The keys of 
     * the array are the names of the commands and the value of the key is 
     * an object of type 'CLICommand'.
     * 
     */
    public static function getRegisteredCommands() : array {
        return self::get()->commands;
    }
    /**
     * Sets the command which is currently in execution stage.
     * 
     * This method is used internally by execution engine to set the command which
     * is being executed.
     * 
     * @param CLICommand $c The command which is in execution stage.
     */
    public static function setActiveCommand(CLICommand $c = null) {
        self::get()->activeCommand = $c;
    }
    /**
     * Returns the command which is being executed.
     * 
     * @return CLICommand|null If a command is requested and currently in execute 
     * stage, the method will return it as an object. If 
     * no command is active, the method will return null.
     * 
     */
    public static function getActiveCommand() {
        return self::get()->activeCommand;
    }
    /**
     * Checks if the framework is running through command line interface (CLI) or 
     * through a web server.
     * 
     * @return boolean If the framework is running through a command line, 
     * the method will return true. False if not.
     * 
     */
    public static function isCLI() : bool {
        //best way to check if app is runing through CLi
        // or in a web server.
        // Did a lot of reaseach on that.
        return http_response_code() === false;
    }
}
