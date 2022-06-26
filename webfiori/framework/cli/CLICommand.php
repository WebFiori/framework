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

use webfiori\framework\cli\CommandArgument;
use webfiori\framework\cli\Runner;
/**
 * An abstract class that can be used to create new CLI command.
 * The developer can extend this class and use it to create a custom CLI 
 * command. The class can be used to display output to terminal and also read 
 * user input. In addition, the output can be formatted using ANSI escape sequences.
 * 
 * @author Ibrahim
 * 
 * @version 1.0.1
 */
abstract class CLICommand {
    /**
     * An associative array that contains extra options that can be added to 
     * the command.
     * @var array
     * @since 1.0 
     */
    private $commandArgs;
    /**
     * The name of the command such as 'help'.
     * @var string 
     * @since 1.0
     */
    private $commandName;
    /**
     * A description of how to use the command or what it does.
     * @var string
     * @since 1.0 
     */
    private $description;
    /**
     * 
     * @var InputStream
     * 
     * @since 1.0.1
     */
    private $inputStream;
    /**
     * 
     * @var OutputStream
     * 
     * @since 1.0.1
     */
    private $outputStream;
    /**
     * Creates new instance of the class.
     * 
     * @param string $commandName A string that represents the name of the 
     * command such as '-v' or 'help'. If invalid name provided, the 
     * value 'new-command' is used.
     * 
     * @param array $args An associative array of sub-associative arrays of arguments (or options) which can 
     * be supplied to the command when running it. The 
     * key of each sub array is argument name. Each 
     * sub-array can have the following indices as argument options:
     * <ul>
     * <li><b>optional</b>: A boolean. if set to true, it means that the argument 
     * is optional and can be ignored when running the command.</li>
     * <li><b>default</b>: An optional default value for the argument 
     * to use if it is not provided and is optional.</li>
     * <li><b>description</b>: A description of the argument which 
     * will be shown if the command 'help' is executed.</li>
     * <li><b>values</b>: A set of values that the argument can have. If provided, 
     * only the values on the list will be allowed. Note that if null or empty string 
     * is in the array, it will be ignored. Also, if boolean values are 
     * provided, true will be converted to the string 'y' and false will 
     * be converted to the string 'n'.</li>
     * </ul>
     * 
     * @param string $description A string that describes what does the job 
     * do. The description will appear when the command 'help' is executed.
     * @since 1.0
     */
    public function __construct(string $commandName, array $args = [], string $description = '') {
        if (!$this->setName($commandName)) {
            $this->setName('new-command');
        }
        $this->addArgs($args);

        if (!$this->setDescription($description)) {
            $this->setDescription('<NO DESCRIPTION>');
        }
        $this->setInputStream(Runner::getInputStream());
        $this->setOutputStream(Runner::getOutputStream());
    }
    /**
     * Add command argument.
     * 
     * An argument is a string that comes after the name of the command. The value 
     * of an argument can be set using equal sign. For example, if command name 
     * is 'do-it' and one argument has the name 'what-to-do', then the full 
     * CLI command would be "do-it what-to-do=say-hi". An argument can be 
     * also treated as an option.
     * 
     * @param string $name The name of the argument. It must be non-empty string 
     * and does not contain spaces. Note that if the argument is already added and 
     * the developer is trying to add it again, the new options array will override 
     * the existing options array.
     * 
     * @param array $options An optional array of options. Available options are:
     * <ul>
     * <li><b>optional</b>: A boolean. if set to true, it means that the argument 
     * is optional and can be ignored when running the command.</li>
     * <li><b>default</b>: An optional default value for the argument 
     * to use if it is not provided and is optional.</li>
     * <li><b>description</b>: A description of the argument which 
     * will be shown if the command 'help' is executed.</li>
     * <li><b>values</b>: A set of values that the argument can have. If provided, 
     * only the values on the list will be allowed. Note that if null or empty string 
     * is in the array, it will be ignored. Also, if boolean values are 
     * provided, true will be converted to the string 'y' and false will 
     * be converted to the string 'n'.</li>
     * </ul>
     * 
     * @return boolean If the argument is added, the method will return true. 
     * Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function addArg(string $name, array $options = []) : bool {
        $toAdd = $this->_checkArgOptions($name, $options);
        if ($toAdd === null) {
            return false;
        }
        return $this->addArgument($toAdd);
    }
    /**
     * Adds new command argument.
     * 
     * @param CommandArgument $arg The argument that will be added.
     * 
     * @return boolean If the argument is added, the method will return true.
     * If not, false is returned. The argument will not be added only if an argument
     * which has same name is added.
     */
    public function addArgument(CommandArgument $arg) : bool {
        if (!$this->hasArg($arg->getName())) {
            $this->commandArgs[] = $arg;
            return true;
        }
        return false;
    }
    /**
     * Adds multiple arguments to the command.
     * 
     * @param array $arr An associative array of sub associative arrays. The 
     * key of each sub array is argument name. Each 
     * sub-array can have the following indices:
     * <ul>
     * <li><b>optional</b>: A boolean. if set to true, it means that the argument 
     * is optional and can be ignored when running the command.</li>
     * <li><b>default</b>: An optional default value for the argument 
     * to use if it is not provided and is optional.</li>
     * <li><b>description</b>: A description of the argument which 
     * will be shown if the command 'help' is executed.</li>
     * <li><b>values</b>: A set of values that the argument can have. If provided, 
     * only the values on the list will be allowed. Note that if null or empty string 
     * is in the array, it will be ignored. Also, if boolean values are 
     * provided, true will be converted to the string 'y' and false will 
     * be converted to the string 'n'.</li>
     * </ul>
     */
    public function addArgs(array $arr) {
        $this->commandArgs = [];

        foreach ($arr as $optionName => $options) {
            $this->addArg($optionName, $options);
        }
    }
    /**
     * Clears the output before or after cursor position.
     * 
     * This method will replace the visible characters with spaces.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param int $numberOfCols Number of columns to clear. The columns that 
     * will be cleared are before and after cursor position. They don't include 
     * the character at which the cursor is currently pointing to.
     * @param boolean $beforeCursor If set to true, the characters which 
     * are before the cursor will be cleared. Default is true.
     * 
     * @since 1.0
     */
    public function clear(int $numberOfCols = 1, bool $beforeCursor = true) {

        if ($numberOfCols >= 1) {
            if ($beforeCursor) {
                for ($x = 0 ; $x < $numberOfCols ; $x++) {
                    $this->moveCursorLeft();
                    $this->prints(" ");
                    $this->moveCursorLeft();
                }
                $this->moveCursorRight($numberOfCols);
            } else {
                $this->moveCursorRight();

                for ($x = 0 ; $x < $numberOfCols ; $x++) {
                    $this->prints(" ");
                }
                $this->moveCursorLeft($numberOfCols + 1);
            }
        }
    }
    /**
     * Clears the whole content of the console.
     * 
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @since 1.0
     */
    public function clearConsole() {
        $this->prints("\ec");
    }
    /**
     * Clears the line at which the cursor is in and move it back to the start 
     * of the line.
     * 
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @since 1.0
     */
    public function clearLine() {
        $this->prints("\e[2K");
        $this->prints("\r");
    }
    /**
     * Asks the user to conform something.
     * 
     * This method will display the question and wait for the user to confirm the 
     * action by entering 'y' or 'n' in the terminal. If the user give something 
     * other than 'Y' or 'n', it will shows an error and ask him to confirm 
     * again. If a default answer is provided, it will appear in upper case in the 
     * terminal. For example, if default is set to true, at the end of the prompt, 
     * the string that shows the options would be like '(Y/n)'.
     * 
     * @param string $confirmTxt The text of the question which will be asked. 
     * 
     * @return boolean If the user choose 'y', the method will return true. If 
     * he choose 'n', the method will return false. 
     * 
     * @param boolean|null $default Default answer to use if empty input is given. 
     * It can be true for 'y' and false for 'n'. Default value is null which 
     * means no default will be used.
     * 
     * @since 1.0
     * 
     */
    public function confirm(string $confirmTxt, $default = null) {
        $answer = null;

        do {
            if ($default === true) {
                $optionsStr = '(Y/n)';
            } else if ($default === false) {
                $optionsStr = '(y/N)';
            } else {
                $optionsStr = '(y/n)';
            }
            $this->prints($confirmTxt, [
                'color' => 'gray',
                'bold' => true
            ]);
            $this->println($optionsStr, [
                'color' => 'light-blue'
            ]);

            $input = strtolower($this->readln());

            if ($input == 'n') {
                $answer = false;
            } else if ($input == 'y') {
                $answer = true;
            } else if (strlen($input) == 0 && $default !== null) {
                return $default === true;
            } else {
                $this->error('Invalid answer. Choose \'y\' or \'n\'.');
            }
        } while ($answer === null);

        return $answer;
    }
    /**
     * Display a message that represents an error.
     * 
     * The message will be prefixed with the string 'Error:' in 
     * red.
     * 
     * @param string $message The message that will be shown.
     * 
     * @since 1.0
     */
    public function error(string $message) {
        $this->prints('Error: ', [
            'color' => 'light-red',
            'bold' => true
        ]);
        $this->println($message);
    }
    /**
     * Execute the command.
     * 
     * This method should not be called manually by the developer.
     * 
     * @return int If the command is executed, the method will return 0. Other 
     * than that, it will return a number which depends on the return value of 
     * the method 'CLICommand::exec()'.
     * 
     * @since 1.0
     */
    public function excCommand() : int {
        Runner::setActiveCommand($this);
        $retVal = -1;
        $this->_parseArgs();

        if ($this->_checkIsArgsSet() && $this->_checkAllowedArgValues()) {
            $retVal = $this->exec();
        }
        Runner::setActiveCommand();
        return $retVal;
    }
    /**
     * Execute the command.
     * 
     * The implementation of this method should contain the code that will run 
     * when the command is executed.
     * 
     * @return int The developer should implement this method in a way it returns 0 
     * if the command is executed successfully and return -1 if the 
     * command did not execute successfully.
     * 
     * @since 1.0
     */
    public abstract function exec() : int;
    /**
     * Returns an associative array that contains command args.
     * 
     * @return array An associative array. The indices of the array are 
     * the names of the arguments and the values are sub-associative arrays. 
     * the sub arrays will have the following indices: 
     * <ul>
     * <li>optional</li>
     * <li>description</li>
     * <li>default</li>
     * <ul>
     * Note that the last index might not be set.
     * 
     * @since 1.0
     */
    public function getArgs() : array {
        return $this->commandArgs;
    }
    /**
     * Returns the value of command option from CLI given its name.
     * 
     * @param string $optionName The name of the option.
     * 
     * @return string|null If the value of the option is set, the method will 
     * return its value as string. If it is not set, the method will return null.
     * 
     * @since 1.0
     */
    public function getArgValue(string $optionName) {
        $trimmedOptName = trim($optionName);
        $arg = $this->getArg($trimmedOptName);
        
        if ($arg !== null) {
            if ($arg->getValue() !== null && !Runner::isIntaractive()) {
                return $arg->getValue();
            }
            foreach ($_SERVER['argv'] as $option) {
                $optionClean = filter_var($option, FILTER_DEFAULT);
                $optExpl = explode('=', $optionClean);
                $optionNameFromCLI = $optExpl[0];

                if ($optionNameFromCLI == $trimmedOptName) {


                    if (count($optExpl) == 2) {
                        $arg->setValue($optExpl[1]);
                    } else {
                        //If arg is provided, set its value empty string first
                        $arg->setValue('');
                    }

                    return $arg->getValue();
                }
            }
        }
    }
    /**
     * Returns an object that holds argument info if the command.
     * 
     * @param string $name The name of command argument.
     * 
     * @return CommandArgument|null If the command has an argument with the
     * given name, it will be returned. Other than that, null is returned.
     */
    public function getArg(string $name) {
        foreach ($this->getArgs() as $arg) {
            if ($arg->getName() == $name) {
                return $arg;
            }
        }
    }
    /**
     * Returns the description of the command.
     * 
     * The description of the command is a string that describes what does the 
     * command do and it will appear in CLI if the command 'help' is executed.
     * 
     * @return string The description of the command. Default return value 
     * is '&lt;NO DESCRIPTION&gt;'
     * 
     * @since 1.0
     */
    public function getDescription() : string {
        return $this->description;
    }
    /**
     * Take an input value from the user.
     * 
     * @param string $prompt The string that will be shown to the user. The 
     * string must be non-empty.
     * 
     * @param string $default An optional default value to use in case the user 
     * hit "Enter" without entering any value. If null is passed, no default 
     * value will be set.
     * 
     * @param callable $validator A callback that can be used to validate user 
     * input. The callback accepts one parameter which is the value that 
     * the user has given. If the value is valid, the callback must return true. 
     * If the callback returns anything else, it means the value which is given 
     * by the user is invalid and this method will ask the user to enter the 
     * value again.
     * 
     * @param array $validatorParams An optional array that can hold extra parameters
     * which can be passed to the validation callback.
     * 
     * @return string The method will return the value which was taken from the 
     * user.
     * 
     * @since 1.0
     */
    public function getInput(string $prompt, $default = null, callable $validator = null, array $validatorParams = []) {
        $trimidPrompt = trim($prompt);

        if (strlen($trimidPrompt) > 0) {
            do {
                $this->prints($trimidPrompt, [
                    'color' => 'gray',
                    'bold' => true
                ]);

                if ($default !== null) {
                    $this->prints(' Enter = "'.$default.'"', [
                        'color' => 'light-blue'
                    ]);
                }
                $this->println();
                $input = $this->readln();

                $check = $this->getInputHelper($input, $validator, $default, $validatorParams);

                if ($check['valid']) {
                    return $check['value'];
                }
            } while (true);
        }
    }
    /**
     * Reads a value as an integer.
     * 
     * @param string $prompt The string that will be shown to the user. The 
     * string must be non-empty.
     * 
     * @param int $default An optional default value to use in case the user 
     * hit "Enter" without entering any value. If null is passed, no default 
     * value will be set.
     * 
     * @return int
     */
    public function readInteger(string $prompt, int $default = null) : int {
        $isInt = false;
        do {
            $val = $this->getInput($prompt, $default);
            $isInt = $this->isInt($val);
            if (!$isInt) {
                $this->error('Provided value is not an integer!');
            }
        } while (!$isInt);
        return intval($val);
    }
    /**
     * Reads a value as float.
     * 
     * @param string $prompt The string that will be shown to the user. The 
     * string must be non-empty.
     * 
     * @param int $default An optional default value to use in case the user 
     * hit "Enter" without entering any value. If null is passed, no default 
     * value will be set.
     * 
     * @return float
     */
    public function readFloat(string $prompt, float $default = null) : float {
        return floatval($this->getInput($prompt, $default));
    }
    /**
     * Returns the stream at which the command is sing to read inputs.
     * 
     * @return null|InputStream If the stream is set, it will be returned as 
     * an object. Other than that, the method will return null.
     * 
     * @since 1.0.1
     */
    public function getInputStream() {
        return $this->inputStream;
    }
    /**
     * Returns the name of the command.
     * 
     * The name of the command is a string which is used to call the command 
     * from CLI.
     * 
     * @return string The name of the command (such as 'v' or 'help'). Default 
     * return value is 'new-command'.
     * 
     * @since 1.0
     */
    public function getName() : string {
        return $this->commandName;
    }
    /**
     * Returns the stream at which the command is using to send output.
     * 
     * @return null|OutputStream If the stream is set, it will be returned as 
     * an object. Other than that, the method will return null.
     * 
     * @since 1.0.1
     */
    public function getOutputStream() {
        return $this->outputStream;
    }
    /**
     * Checks if the command has a specific command line argument or not.
     * 
     * @param string $argName The name of the command line argument.
     * 
     * @return boolean If the argument is added to the command, the method will 
     * return true. If no argument which has the given name does exist, the method 
     * will return false.
     * 
     * @since 1.0
     */
    public function hasArg(string $argName) {
        foreach ($this->getArgs() as $arg) {
            if ($arg->getName() == $argName) {
                return true;
            }
        }
        return false;
    }
    /**
     * Display a message that represents extra information.
     * 
     * The message will be prefixed with the string 'Info:' in 
     * blue.
     * 
     * @param string $message The message that will be shown.
     * 
     * @since 1.0
     */
    public function info(string $message) {
        $this->prints('Info: ', [
            'color' => 'blue',
            'bold' => true
        ]);
        $this->println($message);
    }
    /**
     * Checks if an argument is provided in the CLI or not.
     * 
     * The method will not check if the argument has a value or not.
     * 
     * @param string $argName The name of the command line argument.
     * 
     * @return boolean If the argument is provided, the method will return 
     * true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function isArgProvided(string $argName) {
        $argObj = $this->getArg($argName);
        

        if ($argObj !== null) {
            $isNull = $argObj->getValue() === null;
            
            if (!$isNull && $argObj->getValue() == '') {
                return true;
            }
        }

        return false;
    }
    /**
     * Moves the cursor down by specific number of lines.
     * 
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param int $lines The number of lines the cursor will be moved. Default 
     * value is 1.
     * 
     * @since 1.0
     */
    public function moveCursorDown(int $lines = 1) {

        if ($lines >= 1) {
            $this->prints("\e[".$lines."B");
        }
    }
    /**
     * Moves the cursor to the left by specific number of columns.
     * 
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param int $numberOfCols The number of columns the cursor will be moved. Default 
     * value is 1.
     * 
     * @since 1.0
     */
    public function moveCursorLeft(int $numberOfCols = 1) {

        if ($numberOfCols >= 1) {
            $this->prints("\e[".$numberOfCols."D");
        }
    }
    /**
     * Moves the cursor to the right by specific number of columns.
     * 
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param int $numberOfCols The number of columns the cursor will be moved. Default 
     * value is 1.
     * 
     * @since 1.0
     */
    public function moveCursorRight(int $numberOfCols = 1) {

        if ($numberOfCols >= 1) {
            $this->prints("\e[".$numberOfCols."C");
        }
    }
    /**
     * Moves the cursor to specific position in the terminal.
     * 
     * If no arguments are supplied to the method, it will move the cursor 
     * to the upper-left corner of the screen (line 0, column 0).
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param int $line The number of line at which the cursor will be moved 
     * to. If not specified, 0 is used.
     * 
     * @param int $col The number of column at which the cursor will be moved 
     * to. If not specified, 0 is used.
     * 
     * @since 1.0
     */
    public function moveCursorTo(int $line = 0, int $col = 0) {

        if ($line > -1 && $col > -1) {
            $this->prints("\e[".$line.";".$col."H");
        }
    }
    /**
     * Moves the cursor up by specific number of lines.
     * 
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param int $lines The number of lines the cursor will be moved. Default 
     * value is 1.
     * 
     * @since 1.0
     */
    public function moveCursorUp(int $lines = 1) {

        if ($lines >= 1) {
            $this->prints("\e[".$lines."A");
        }
    }
    /**
     * Prints an array as a list of items.
     * 
     * This method is useful if the developer would like to print out a list 
     * of multiple items. Each item will be prefixed with a number that represents 
     * its index in the array.
     * 
     * @param array $array The array that will be printed.
     * 
     * @since 1.0
     */
    public function printList(array $array) {
        for ($x = 0 ; $x < count($array) ; $x++) {
            $this->prints("- ", [
                'color' => 'green'
            ]);
            $this->println($array[$x]);
        }
    }
    /**
     * Print out a string and terminates the current line by writing the 
     * line separator string.
     * 
     * This method will work like the function fprintf(). The difference is that 
     * it will print out to the stream at which was specified by the method 
     * CLICommand::setOutputStream() and the text can have formatting 
     * options. Note that support for output formatting depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param string $str The string that will be printed.
     * 
     * @param mixed $_ One or more extra arguments that can be supplied to the 
     * method. The last argument can be an array that contains text formatting options. 
     * for available options, check the method CLICommand::formatOutput().
     * @since 1.0
     */
    public function println(string $str = '', ...$_) {
        $argsCount = count($_);

        if ($argsCount != 0 && gettype($_[$argsCount - 1]) == 'array') {
            //Last index contains formatting options.
            $_[$argsCount - 1]['ansi'] = $this->isArgProvided('--ansi');
            $str = OutputFormatter::formatOutput($str, $_[$argsCount - 1]);
        }
        call_user_func_array([$this->getOutputStream(), 'println'], $this->_createPassArray($str, $_));
    }
    /**
     * Print out a string.
     * 
     * This method works exactly like the function 'fprintf()'. The only 
     * difference is that the method will print out the output to the stream 
     * that was specified using the method CLICommand::setOutputStream() and 
     * the method accepts formatting options as last argument to format the output. 
     * Note that support for output formatting depends on terminal support for 
     * ANSI escape codes.
     * 
     * @param string $str The string that will be printed.
     * 
     * @param mixed $_ One or more extra arguments that can be supplied to the 
     * method. The last argument can be an array that contains text formatting options. 
     * for available options, check the method CLICommand::formatOutput().
     * 
     * @since 1.0
     */
    public function prints(string $str, ...$_) {
        $str = $this->asString($str);

        $argCount = count($_);
        $formattingOptions = [];

        if ($argCount != 0 && gettype($_[$argCount - 1]) == 'array') {
            $formattingOptions = $_[$argCount - 1];
        }

        $formattingOptions['ansi'] = $this->isArgProvided('--ansi');

        $formattedStr = OutputFormatter::formatOutput($str, $formattingOptions);

        call_user_func_array([$this->getOutputStream(), 'prints'], $this->_createPassArray($formattedStr, $_));
    }

    /**
     * Reads a string of bytes from input stream.
     * 
     * This method is used to read specific number of characters from input stream.
     * 
     * @return string The method will return the string which was given as input 
     * in the input stream.
     * 
     * @since 1.0
     */
    public function read(int $bytes = 1) {
        return $this->getInputStream()->read($bytes);
    }
    /**
     * Reads one line from input stream.
     * 
     * The method will continue to read from input stream till it finds end of 
     * line character "\n".
     * 
     * @return string The method will return the string which was taken from 
     * input stream without the end of line character.
     * 
     * @since 1.0
     */
    public function readln() : string {
        return $this->getInputStream()->readLine();
    }

    /**
     * Ask the user to select one of multiple values.
     * 
     * This method will display a prompt and wait for the user to select 
     * the a value from a set of values. If the user give something other than the listed values, 
     * it will shows an error and ask him to select again again. The 
     * user can select an answer by typing its text or its number which will appear 
     * in the terminal.
     * 
     * @param string $prompt The text that will be shown for the user.
     * 
     * @param array $choices An indexed array of values to select from.
     * 
     * @param int $defaultIndex The index of the default value in case no value 
     * is selected and the user hit enter.
     * 
     * @return string The method will return the value which is selected by 
     * the user.
     * 
     * @since 1.0
     */
    public function select(string $prompt, array $choices, $defaultIndex = null) {
        if (gettype($choices) == 'array' && count($choices) != 0) {
            do {
                $this->println($prompt, [
                    'color' => 'gray',
                    'bold' => true
                ]);

                $this->_printChoices($choices, $defaultIndex);
                $input = trim($this->readln());

                $check = $this->_checkSelectedChoice($choices, $defaultIndex, $input);

                if ($check !== null) {
                    return $check;
                }
            } while (true);
        }
    }
    /**
     * Sets the value of an argument.
     * 
     * This method is useful in writing test cases for the commands.
     * 
     * @param string $argName The name of the argument.
     * 
     * @param string $argValue The value to set.
     * 
     * @return boolean If the value of the argument is set, the method will return 
     * true. If not set, the method will return false. The value of the attribute 
     * will be not set in the following cases:
     * <ul>
     * <li>If the argument can have a specific set of values and the given 
     * value is not one of them.</li>
     * <li>The given value is empty string or null.</li>
     * </u>
     * 
     * @since 1.0
     */
    public function setArgValue(string $argName, $argValue = '') {
        $trimmedArgName = trim($argName);
        $argObj = $this->getArg($trimmedArgName);

        if ($argObj !== null) {
            return $argObj->setValue($argValue);
        }
        
        return false;
    }
    /**
     * Sets the description of the command.
     * 
     * The description of the command is a string that describes what does the 
     * command do and it will appear in CLI if the command 'help' is executed.
     * 
     * @param string $str A string that describes the command. It must be non-empty 
     * string.
     * 
     * @return boolean If the description of the command is set, the method will return 
     * true. Other than that, the method will return false.
     */
    public function setDescription(string $str) {
        $trimmed = trim($str);

        if (strlen($trimmed) > 0) {
            $this->description = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Sets the stream at which the command will read input from.
     * 
     * @param InputStream $stream An instance that implements an input stream.
     * 
     * @since 1.0.1
     */
    public function setInputStream(InputStream $stream) {
        $this->inputStream = $stream;
    }
    /**
     * Sets the name of the command.
     * 
     * The name of the command is a string which is used to call the command 
     * from CLI.
     * 
     * @param string $name The name of the command (such as 'v' or 'help'). 
     * It must be non-empty string and does not contain spaces.
     * 
     * @return boolean If the name of the command is set, the method will return 
     * true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function setName(string $name) {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0 && !strpos($trimmed, ' ')) {
            $this->commandName = $name;

            return true;
        }

        return false;
    }
    /**
     * Sets the stream at which the command will send output to.
     * 
     * @param OutputStream $stream An instance that implements output stream.
     * 
     * @since 1.0.1
     */
    public function setOutputStream(OutputStream $stream) {
        $this->outputStream = $stream;
    }
    /**
     * Display a message that represents a success status.
     * 
     * The message will be prefixed with the string "Success:" in green. 
     * 
     * @param string $message The message that will be displayed.
     * 
     * @since 1.0
     */
    public function success(string $message) {
        $this->prints("Success: ", [
            'color' => 'light-green',
            'bold' => true
        ]);
        $this->println($message);
    }
    /**
     * Display a message that represents a warning.
     * 
     * The message will be prefixed with the string 'Warning:' in 
     * red.
     * 
     * @param string $message The message that will be shown.
     * 
     * @since 1.0
     */
    public function warning(string $message) {
        $this->prints('Warning: ', [
            'color' => 'light-yellow',
            'bold' => true
        ]);
        $this->println($message);
    }
    private function _checkAllowedArgValues() {
        $invalidArgsVals = [];

        foreach ($this->commandArgs as $argObj) {
            
            $argVal = $argObj->getValue();
            $allowed = $argObj->getAllowedValues();
            if ($argVal !== null && count($allowed) != 0 && !in_array($argVal, $allowed)) {
                $invalidArgsVals[] = $argObj->getName();
            }
        }

        if (count($invalidArgsVals) != 0) {
            $invalidStr = 'The following argument(s) have invalid values: ';
            $comma = '';

            foreach ($invalidArgsVals as $argName) {
                $invalidStr .= $comma.'"'.$argName.'"';
                $comma = ', ';
            }
            $this->error($invalidStr);

            foreach ($invalidArgsVals as $argName) {
                $this->prints('Info:', [
                    'color' => 'light-yellow',
                    'ansi' => $this->isArgProvided('--ansi')
                ]);
                $this->println("Allowed values for the argument '$argName':");

                foreach ($this->commandArgs[$argName]['values'] as $val) {
                    $this->println($val);
                }
            }

            return false;
        }

        return true;
    }
    private function _checkArgOptions($name, $options) {
        if (strlen($name) == 0) {
            return null;
        }
        $arg = new CommandArgument($name);
        if ($arg->getName() == 'arg') {
            return null;
        }
        if (isset($options['optional'])) {
            $arg->setIsOptional($options['optional']);
        }
        $desc = isset($options['description']) ? trim($options['description']) : '<NO DESCRIPTION>';
        
        if (strlen($desc) != 0) {
            $arg->setDescription($desc);
        } else {
            $arg->setDescription('<NO DESCRIPTION>');
        }
        $allowedVals = isset($options['values']) ? $options['values'] : [];
        foreach ($allowedVals as $val) {
            $arg->addAllowedValue($val);
        }


        if (isset($options['default']) && gettype($options['default']) == 'string') {
            $arg->setDefault($options['default']);
        }

        return $arg;
    }
    private function _checkIsArgsSet() {
        $missingMandatury = [];

        foreach ($this->commandArgs as $argObj) {
            
            if (!$argObj->isOptional() && $argObj->getValue() === null) {
                if ($argObj->getDefault() != '') {
                    $argObj->setValue($argObj->getDefault());
                } else {
                    $missingMandatury[] = $argObj->getName();
                }
            }
        }

        if (count($missingMandatury) != 0) {
            $missingStr = 'The following required argument(s) are missing: ';
            $comma = '';

            foreach ($missingMandatury as $opt) {
                $missingStr .= $comma.'"'.$opt.'"';
                $comma = ', ';
            }
            $this->error($missingStr);

            return false;
        }

        return true;
    }
    private function _checkSelectedChoice($choices, $defaultIndex, $input) {
        
        if (in_array($input, $choices)) {
            //Given input is exactly same as one of choices
            return $input;
        } else if (strlen($input) == 0 && $defaultIndex !== null) {
            //Given input is empty string (enter hit). 
            //Return defult if specified.
            return $this->_getDefault($choices, $defaultIndex);
        } else if ($this->isInt($input)) {
            //Selected option is an index. Search for it and return its value.
            return $this->_getChoiceAtIndex($choices, $input);
        } else {
            $this->error('Invalid answer.');
        }
    }
    private function _getChoiceAtIndex(array $choices, $input) {
        $index = 0;
            
        foreach ($choices as $choice) {
            if ($index == $input) {
                return $choice;
            }
            $index++;
        }
    }
    private function _getDefault(array $choices, $defaultIndex) {
        $index = 0;
        foreach ($choices as $choice) {
            if ($index == $defaultIndex) {
                return $choice;
            }
            $index++;
        }
    }

    private function isInt(string $val) : bool {
        $len = strlen($val);
        if ($len == 0) {
            return false;
        }
        $isNum = true;
        for ($x = 0 ; $x < $len ; $x++) {
            $char = $val[$x];
            $isNum = $char >= '0' && $char <= '9';
        }
        return $isNum;
    }
    private function _createPassArray($string, array $args) {
        $retVal = [$string];

        foreach ($args as $arg) {
            if (gettype($arg) != 'array') {
                $retVal[] = $arg;
            }
        }

        return $retVal;
    }
    /**
     * Returns an array that contains the names of command arguments.
     * 
     * @return array An array of strings.
     */
    public function getArgsNames() : array {
        return array_map(function ($el) {
            return $el->getName();
        }, $this->getArgs());
    }
    private function _parseArgs() {
        $this->addArg('--ansi', [
            'optional' => true,
            'description' => 'Force the use of ANSI output.'
        ]);
        $options = $this->getArgsNames();

        foreach ($options as $optName) {
            $this->getArgValue($optName);
        }
    }
    private function _printChoices($choices, $default) {
        $index = 0;
        foreach ($choices as $choiceTxt) {
            if ($default !== null && $index == $default) {
                $this->prints($index.": ".$choiceTxt, [
                    'color' => 'light-blue',
                    'bold' => 'true'
                ]);
                $this->println(' <--');
            } else {
                $this->println($index.": ".$choiceTxt);
            }
            $index++;
        }
    }

    private function asString($var) {
        $type = gettype($var);

        if ($type == 'boolean') {
            return $var === true ? 'true' : 'false';
        } else {
            if ($type == 'null') {
                return 'null';
            }
        }

        return $var;
    }
    
    /**
     * Validate user input and show error message if user input is invalid.
     * @param type $input
     * @param type $validator
     * @param type $default
     * @return array The method will return an array with two indices, 'valid' and 
     * 'value'. The 'valid' index contains a boolean that is set to true if the 
     * value is valid. The index 'value' will contain the passed value.
     */
    private function getInputHelper($input, callable $validator = null, $default = null , array $callbackParams = []) {
        $retVal = [
            'valid' => true,
            'value' => $input
        ];

        if (strlen($input) == 0 && $default !== null) {
            $retVal['value'] = $default;
        } else if ($validator !== null) {
            $retVal['valid'] = call_user_func_array($validator, array_merge([$input], $callbackParams));

            if (!($retVal['valid'] === true)) {
                $this->error('Invalid input is given. Try again.');
            }
        }

        return $retVal;
    }
}
