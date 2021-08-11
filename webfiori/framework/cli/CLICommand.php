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

/**
 * An abstract class that can be used to create new CLI command.
 * The developer can extend this class and use it to create a custom CLI 
 * command. The class can be used to display output to terminal and also read 
 * user input. In addition, the output can be formatted using ANSI escape sequences.
 * @author Ibrahim
 * @version 1.0
 */
abstract class CLICommand {
    /**
     * An associative array that contains color codes and names.
     * @since 1.0
     */
    const COLORS = [
        'black' => 30,
        'red' => 31,
        'light-red' => 91,
        'green' => 32,
        'light-green' => 92,
        'yellow' => 33,
        'light-yellow' => 93,
        'white' => 97,
        'gray' => 37,
        'blue' => 34,
        'light-blue' => 94
    ];
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
     * Creates new instance of the class.
     * @param string $commandName A string that represents the name of the 
     * command such as '-v' or 'help'. If not provided, the 
     * value 'new-command' is used.
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
     * @param string $description A string that describes what does the job 
     * do. The description will appear when the command 'help' is executed.
     * @since 1.0
     */
    public function __construct($commandName, $args = [], $description = '') {
        if (!$this->setName($commandName)) {
            $this->setName('new-command');
        }
        $this->addArgs($args);

        if (!$this->setDescription($description)) {
            $this->setDescription('<NO DESCRIPTION>');
        }
    }
    /**
     * Add command argument.
     * An argument is a string that comes after the name of the command. The value 
     * of an argument can be set using equal sign. For example, if command name 
     * is 'do-it' and one argument has the name 'what-to-do', then the full 
     * CLI command would be "do-it what-to-do=say-hi". An argument can be 
     * also treated as an option.
     * @param string $name The name of the argument. It must be non-empty string 
     * and does not contain spaces. Note that if the argument is already added and 
     * the developer is trying to add it again, the new options array will override 
     * the existing options array.
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
     * @return boolean If the argument is added, the method will return true. 
     * Other than that, the method will return false.
     * @since 1.0
     */
    public function addArg($name, $options = []) {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0 && !strpos($trimmed, ' ')) {
            if (gettype($options) == 'array') {
                $this->commandArgs[$trimmed] = $this->_checkArgOptions($options);
            } else {
                $this->commandArgs[$trimmed] = [
                    'optional' => false,
                    'description' => '<NO DESCRIPTION>',
                    'values' => []
                ];
            }

            return true;
        }

        return false;
    }
    /**
     * Adds multiple arguments to the command
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
     * This method will replace the visible characters with spaces.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @param int $numberOfCols Number of columns to clear. The columns that 
     * will be cleared are before and after cursor position. They don't include 
     * the character at which the cursor is currently pointing to.
     * @param boolean $beforeCursor If set to true, the characters which 
     * are before the cursor will be cleared. Default is true.
     * @since 1.0
     */
    public function clear($numberOfCols = 1, $beforeCursor = true) {
        $asInt = intval($numberOfCols);

        if ($asInt >= 1) {
            if ($beforeCursor) {
                for ($x = 0 ; $x < $numberOfCols ; $x++) {
                    $this->moveCursorLeft();
                    $this->prints(" ");
                    $this->moveCursorLeft();
                }
                $this->moveCursorRight($asInt);
            } else {
                $this->moveCursorRight();

                for ($x = 0 ; $x < $numberOfCols ; $x++) {
                    $this->prints(" ");
                }
                $this->moveCursorLeft($asInt + 1);
            }
        }
    }
    /**
     * Clears the whole content of the console.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @since 1.0
     */
    public function clearConsole() {
        $this->prints("\ec");
    }
    /**
     * Clears the line at which the cursor is in and move it back to the start 
     * of the line.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @since 1.0
     */
    public function clearLine() {
        $this->prints(STDOUT, "\e[2K");
        $this->prints(STDOUT, "\r");
    }
    /**
     * Asks the user to conform something.
     * This method will display the question and wait for the user to confirm the 
     * action by entering 'y' or 'n' in the terminal. If the user give something 
     * other than 'Y' or 'n', it will shows an error and ask him to confirm 
     * again. If a default answer is provided, it will appear in upper case in the 
     * terminal. For example, if default is set to true, at the end of the prompt, 
     * the string that shows the options would be like '(Y/n)'.
     * @param string $confirmTxt The text of the question which will be asked. 
     * @return boolean If the user choose 'y', the method will return true. If 
     * he choose 'n', the method will return false. 
     * @param boolean|null $default Default answer to use if empty input is given. 
     * It can be true for 'y' and false for 'n'. Default value is null which 
     * means no default will be used.
     * @since 1.0
     * 
     */
    public function confirm($confirmTxt, $default = null) {
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
     * The message will be prefixed with the string 'Error:' in 
     * red. The output will be sent to STDOUT.
     * @param string $message The message that will be shown.
     * @since 1.0
     */
    public function error($message) {
        $this->prints('Error: ', [
            'color' => 'light-red',
            'bold' => true
        ]);
        $this->println($message);
    }
    /**
     * Execute the command.
     * This method should not be called manually by the developer.
     * @return int If the command is executed, the method will return 0. Other 
     * than that, it will return a number which depends on the return value of 
     * the method 'CLICommand::exec()'.
     * @since 1.0
     */
    public function excCommand() {
        $this->_parseArgs();

        if ($this->_checkIsArgsSet() && $this->_checkAllowedArgValues()) {
            $execResult = $this->exec();

            if ($execResult === null) {
                return 0;
            }

            return intval($execResult);
        }

        return -1;
    }
    /**
     * Execute the command.
     * The implementation of this method should contain the code that will run 
     * when the command is executed.
     * @return int The developer should implement this method in a way it returns 0 
     * or null if the command is executed successfully and return -1 if the 
     * command did not execute successfully.
     * @since 1.0
     */
    public abstract function exec();
    /**
     * Formats an output string.
     * This method is used to add colors to the output string or 
     * make it bold or underlined. The returned value of this 
     * method can be sent to STDOUT using the method 'fprintf()'. 
     * Note that the support for colors 
     * and formatting will depend on the terminal configuration. In addition, 
     * if the constant NO_COLOR is defined or is set in the environment, the 
     * returned string will be returned as is.
     * @param string $string The string that will be formatted.
     * @param array $formatOptions An associative array of formatting 
     * options. Supported options are:
     * <ul>
     * <li><b>color</b>: The foreground color of the output text. Supported colors 
     * are: 
     * <ul>
     * <li>white</li>
     * <li>black</li>
     * <li>red</li>
     * <li>light-red</li>
     * <li>green</li>
     * <li>light-green</li>
     * <li>yellow</li>
     * <li>light-yellow</li>
     * <li>gray</li>
     * <li>blue</li>
     * <li>light-blue</li>
     * </ul>
     * </li>
     * <li><b>bg-color</b>: The background color of the output text. Supported colors 
     * are the same as the supported colors by the 'color' option.</li>
     * <li><b>bold</b>: A boolean. If set to true, the text will 
     * be bold.</li>
     * <li><b>underline</b>: A boolean. If set to true, the text will 
     * be underlined.</li>
     * <li><b>reverse</b>: A boolean. If set to true, the foreground 
     * color and background color will be reversed (invert the foreground and background colors).</li>
     * <li><b>blink</b>: A boolean. If set to true, the text will 
     * blink.</li>
     * </ul>
     * @return string The string after applying the formatting to it.
     * @since 1.0
     */
    public static function formatOutput($string, $formatOptions) {
        $validatedOptions = self::_validateOutputOptions($formatOptions);

        return self::_getFormattedOutput($string, $validatedOptions);
    }
    /**
     * Returns an associative array that contains one argument information.
     * @param string $argName The name of the argument.
     * @return array If the argument exist, the method will return an associative 
     * array. The returned array will possibly have the following indices:
     * <ul>
     * <li><b>optional</b>: A booleean which is set to true if the argument is optional.</li>
     * <li><b>description</b>: The description of the argument. Appears when help command 
     * is executed.</li>
     * <li><b>default</b>: A default value for the argument. It will be not set if no default 
     * value for the argument is provided.</li>
     * <li><b>values</b>: A set of values at which the argument can have.</li>
     * <li><b>provided</b>: Set to true if the argument is provided in command line 
     * interface.</li>
     * <li><b>val</b>: The value of the argument taken from the command line interface.</li>
     * </ul>
     * If the argument does not exist, the returned array will be empty.
     * @since 1.0
     */
    public function getArgInfo($argName) {
        if ($this->hasArg($argName)) {
            return $this->commandArgs[$argName];
        }

        return [];
    }
    /**
     * Returns an associative array that contains command args.
     * @return array An associative array. The indices of the array are 
     * the names of the arguments and the values are sub-associative arrays. 
     * the sub arrays will have the following indices: 
     * <ul>
     * <li>optional</li>
     * <li>description</li>
     * <li>default</li>
     * <ul>
     * Note that the last index might not be set.
     * @since 1.0
     */
    public function getArgs() {
        return $this->commandArgs;
    }
    /**
     * Returns the value of command option from CLI given its name.
     * @param string $optionName The name of the option.
     * @return string|null If the value of the option is set, the method will 
     * return its value as string. If it is not set, the method will return null.
     * @since 1.0
     */
    public function getArgValue($optionName) {
        $trimmedOptName = trim($optionName);

        if (isset($this->commandArgs[$trimmedOptName]['val'])) {
            return $this->commandArgs[$trimmedOptName]['val'];
        }

        foreach ($_SERVER['argv'] as $option) {
            $optionClean = filter_var($option, FILTER_SANITIZE_STRING);
            $optExpl = explode('=', $optionClean);
            $optionNameFromCLI = $optExpl[0];

            if ($optionNameFromCLI == $trimmedOptName) {
                $this->commandArgs[$trimmedOptName]['provided'] = true;

                if (count($optExpl) == 2) {
                    return $optExpl[1];
                }

                return null;
            } else {
                $this->commandArgs[$trimmedOptName]['provided'] = false;
            }
        }
    }
    /**
     * Returns the description of the command.
     * The description of the command is a string that describes what does the 
     * command do and it will appear in CLI if the command 'help' is executed.
     * @return string The description of the command. Default return value 
     * is '&lt;NO DESCRIPTION&gt;'
     * @since 1.0
     */
    public function getDescription() {
        return $this->description;
    }
    /**
     * Take an input value from the user.
     * This method will read the input from STDIN.
     * @param string $prompt The string that will be shown to the user. The 
     * string must be non-empty.
     * @param string $default An optional default value to use in case the user 
     * hit "Enter" without entering any value. If null is passed, no default 
     * value will be set.
     * @param callable $validator A callback that can be used to validate user 
     * input. The callback accepts one parameter which is the value that 
     * the user has given. If the value is valid, the callback must return true. 
     * If the callback returns anything else, it means the value which is given 
     * by the user is invalid and this method will ask the user to enter the 
     * value again.
     * @return string The method will return the value which was taken from the 
     * user.
     * @since 1.0
     */
    public function getInput($prompt, $default = null, $validator = null) {
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

                $check = $this->getInputHelper($input, $validator, $default);

                if ($check['valid']) {
                    return $check['value'];
                }
            } while (true);
        }
    }
    /**
     * Returns the name of the command.
     * The name of the command is a string which is used to call the command 
     * from CLI.
     * @return string The name of the command (such as 'v' or 'help'). Default 
     * return value is 'new-command'.
     * @since 1.0
     */
    public function getName() {
        return $this->commandName;
    }
    /**
     * Checks if the command has a specific command line argument or not.
     * @param string $argName The name of the command line argument.
     * @return boolean If the argument is added to the command, the method will 
     * return true. If no argument which has the given name does exist, the method 
     * will return false.
     * @since 1.0
     */
    public function hasArg($argName) {
        return isset($this->getArgs()[trim($argName)]);
    }
    /**
     * Display a message that represents extra information.
     * The message will be prefixed with the string 'Info:' in 
     * blue. The output will be sent to STDOUT.
     * @param string $message The message that will be shown.
     * @since 1.0
     */
    public function info($message) {
        $this->prints('Info: ', [
            'color' => 'blue',
            'bold' => true
        ]);
        $this->println($message);
    }
    /**
     * Checks if an argument is provided in the CLI or not.
     * The method will not check if the argument has a value or not.
     * @param string $argName The name of the command line argument.
     * @return boolean If the argument is provided, the method will return 
     * true. Other than that, the method will return false.
     * @since 1.0
     */
    public function isArgProvided($argName) {
        if ($this->hasArg($argName)) {
            $trimmed = trim($argName);

            if (isset($this->getArgs()[$trimmed]['provided'])) {
                return $this->getArgs()[$trimmed]['provided'];
            }
        }

        return false;
    }
    /**
     * Moves the cursor down by specific number of lines.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @param int $lines The number of lines the cursor will be moved. Default 
     * value is 1.
     * @since 1.0
     */
    public function moveCursorDown($lines = 1) {
        $asInt = intval($lines);

        if ($asInt >= 1) {
            $this->prints("\e[".$asInt."B");
        }
    }
    /**
     * Moves the cursor to the left by specific number of columns.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @param int $numberOfCols The number of columns the cursor will be moved. Default 
     * value is 1.
     * @since 1.0
     */
    public function moveCursorLeft($numberOfCols = 1) {
        $asInt = intval($numberOfCols);

        if ($asInt >= 1) {
            $this->prints("\e[".$asInt."D");
        }
    }
    /**
     * Moves the cursor to the right by specific number of columns.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @param int $numberOfCols The number of columns the cursor will be moved. Default 
     * value is 1.
     * @since 1.0
     */
    public function moveCursorRight($numberOfCols = 1) {
        $asInt = intval($numberOfCols);

        if ($asInt >= 1) {
            $this->prints("\e[".$asInt."C");
        }
    }
    /**
     * Moves the cursor to specific position in the terminal.
     * If no arguments are supplied to the method, it will move the cursor 
     * to the upper-left corner of the screen (line 0, column 0).
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @param int $line The number of line at which the cursor will be moved 
     * to. If not specified, 0 is used.
     * @param int $col The number of column at which the cursor will be moved 
     * to. If not specified, 0 is used.
     * @since 1.0
     */
    public function moveCursorTo($line = 0, $col = 0) {
        $lineAsInt = intval($line);
        $colAsInt = intval($col);

        if ($lineAsInt > -1 && $colAsInt > -1) {
            $this->prints("\e[".$lineAsInt.";".$colAsInt."H");
        }
    }
    /**
     * Moves the cursor up by specific number of lines.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @param int $lines The number of lines the cursor will be moved. Default 
     * value is 1.
     * @since 1.0
     */
    public function moveCursorUp($lines = 1) {
        $asInt = intval($lines);

        if ($asInt >= 1) {
            $this->prints("\e[".$asInt."A");
        }
    }
    /**
     * Prints an array as a list of items.
     * This method is useful if the developer would like to print out a list 
     * of multiple items. Each item will be prefixed with a number that represents 
     * its index in the array.
     * @param array $array The array that will be printed.
     * @since 1.0
     */
    public function printList($array) {
        if (gettype($array) == 'array') {
            for ($x = 0 ; $x < count($array) ; $x++) {
                $this->prints("- ", [
                    'color' => 'green'
                ]);
                $this->println($array[$x]);
            }
        }
    }
    /**
     * Print out a string and terminates the current line by writing the 
     * line separator string.
     * This method will work like the function fprintf(). The difference is that 
     * it will print out directly to STDOUT and the text can have formatting 
     * options. Note that support for output formatting depends on terminal support for 
     * ANSI escape codes.
     * @param string $str The string that will be printed to STDOUT.
     * @param mixed $_ One or more extra arguments that can be supplied to the 
     * method. The last argument can be an array that contains text formatting options. 
     * for available options, check the method CLICommand::formatOutput().
     * @since 1.0
     */
    public function println($str = '', ...$_) {
        $toPass = [
            $this->asString($str)."\e[0m\e[k\n"
        ];

        foreach ($_ as $val) {
            $toPass[] = $val;
        }
        call_user_func_array([$this, 'prints'], $toPass);
    }
    /**
     * Print out a string.
     * This method works exactly like the function 'fprintf()'. The only 
     * difference is that the method will print out the output to STDOUT and 
     * the method accepts formatting options as last argument to format the output. 
     * Note that support for output formatting depends on terminal support for 
     * ANSI escape codes.
     * @param string $str The string that will be printed to STDOUT.
     * @param mixed $_ One or more extra arguments that can be supplied to the 
     * method. The last argument can be an array that contains text formatting options. 
     * for available options, check the method CLICommand::formatOutput().
     * @since 1.0
     */
    public function prints($str, ...$_) {
        $str = $this->asString($str);
        $argCount = count($_);
        $formattingOptions = [];

        if ($argCount != 0 && gettype($_[$argCount - 1]) == 'array') {
            $formattingOptions = $_[$argCount - 1];
        }

        $formattingOptions['force-styling'] = $this->isArgProvided('force-styling');
        $formattingOptions['no-ansi'] = $this->isArgProvided('--no-ansi');
        $arrayToPass = [
            STDOUT,
            $this->formatOutput($str, $formattingOptions)
        ];

        foreach ($_ as $val) {
            $type = gettype($val);

            if ($type != 'array') {
                $arrayToPass[] = $val;
            }
        }
        call_user_func_array('fprintf', $arrayToPass);
    }
    /**
     * Reads a string from STDIN stream.
     * This method is limit to read 1024 bytes at once from STDIN.
     * @return string The method will return the string which was given as input 
     * in STDIN.
     * @since 1.0
     */
    public function read() {
        return trim(fread(STDIN, 1024));
    }
    /**
     * Reads one line from STDIN.
     * The method will continue to read from STDIN till it finds end of 
     * line character "\n".
     * @return string The method will return the string which was taken from 
     * STDIN without the end of line character.
     * @since 1.0
     */
    public function readln() {
        $retVal = '';
        $char = '';

        while ($char != "\n") {
            $char = fread(STDIN, 1);
            $retVal .= $char;
        }

        return trim($retVal);
    }
    /**
     * Ask the user to select one of multiple values.
     * This method will display a prompt and wait for the user to select 
     * the a value from a set of values. If the user give something other than the listed values, 
     * it will shows an error and ask him to select again again. The 
     * user can select an answer by typing its text or its number which will appear 
     * in the terminal.
     * @param string $prompt The text that will be shown for the user.
     * @param array $choices An indexed array of values to select from.
     * @param int $defaultIndex The index of the default value in case no value 
     * is selected and the user hit enter.
     * @return string The method will return the value which is selected by 
     * the user.
     * @since 1.0
     */
    public function select($prompt, $choices, $defaultIndex = null) {
        if (gettype($choices) == 'array' && count($choices) != 0) {
            do {
                $this->println($prompt, [
                    'color' => 'gray',
                    'bold' => true
                ]);
                $default = null;

                if ($defaultIndex !== null && gettype($defaultIndex) == 'integer') {
                    $default = isset($choices[$defaultIndex]) ? $choices[$defaultIndex] : null;
                }
                $this->_printChoices($choices, $default);
                $input = trim($this->readln());

                $check = $this->_checkSelectedChoice($choices, $default, $input);

                if ($check !== null) {
                    return $check;
                }
            } while (true);
        }
    }
    /**
     * Sets the value of an argument.
     * This method is useful in writing test cases for the commands.
     * @param string $argName The name of the argument.
     * @param string $argValue The value to set.
     * @return boolean If the value of the argument is set, the method will return 
     * true. If not set, the method will return false. The value of the attribute 
     * will be not set in the following cases:
     * <ul>
     * <li>If the argument can have a specific set of values and the given 
     * value is not one of them.</li>
     * <li>The given value is empty string or null.</li>
     * </u>
     * @since 1.0
     */
    public function setArgValue($argName, $argValue) {
        $trimmedArgName = trim($argName);
        $trimmedArgVal = trim($argValue);
        $retVal = false;

        if (isset($this->commandArgs[$trimmedArgName]) && strlen($trimmedArgVal) != 0) {
            $allowedVals = $this->commandArgs[$trimmedArgName]['values'];

            if (count($allowedVals) != 0) {
                if (in_array($argValue, $allowedVals)) {
                    $retVal = true;
                }
            } else {
                $retVal = true;
            }
        }

        if ($retVal) {
            $this->commandArgs[$trimmedArgName]['val'] = $argValue;
        }

        return $retVal;
    }
    /**
     * Sets the description of the command.
     * The description of the command is a string that describes what does the 
     * command do and it will appear in CLI if the command 'help' is executed.
     * @param string $str A string that describes the command. It must be non-empty 
     * string.
     * @return boolean If the description of the command is set, the method will return 
     * true. Other than that, the method will return false.
     */
    public function setDescription($str) {
        $trimmed = trim($str);

        if (strlen($trimmed) > 0) {
            $this->description = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Sets the name of the command.
     * The name of the command is a string which is used to call the command 
     * from CLI.
     * @param string $name The name of the command (such as 'v' or 'help'). 
     * It must be non-empty string and does not contain spaces.
     * @return boolean If the name of the command is set, the method will return 
     * true. Other than that, the method will return false.
     * @since 1.0
     */
    public function setName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0 && !strpos($trimmed, ' ')) {
            $this->commandName = $name;

            return true;
        }

        return false;
    }
    /**
     * Display a message that represents a success status.
     * The message will be prefixed with the string "Success:" in green. 
     * The output will be sent to STDOUT.
     * @param string $message The message that will be displayed.
     * @since 1.0
     */
    public function success($message) {
        $this->prints("Success: ", [
            'color' => 'light-green',
            'bold' => true
        ]);
        $this->println($message);
    }
    /**
     * Display a message that represents a warning.
     * The message will be prefixed with the string 'Warning:' in 
     * red. The output will be sent to STDOUT.
     * @param string $message The message that will be shown.
     * @since 1.0
     */
    public function warning($message) {
        $this->prints('Warning: ', [
            'color' => 'light-yellow',
            'bold' => true
        ]);
        $this->println($message);
    }
    private function _checkAllowedArgValues() {
        $invalidArgsVals = [];

        foreach ($this->commandArgs as $argName => $argArray) {
            if ($this->isArgProvided($argName) && count($argArray['values']) != 0) {
                $argValue = $argArray['val'];

                if (!in_array($argValue, $argArray['values'])) {
                    $invalidArgsVals[] = $argName;
                }
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
                    'force-styling' => $this->isArgProvided('force-styling')
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
    private function _checkArgOptions(&$options) {
        $optinsArr = [];

        if (isset($options['optional'])) {
            $optinsArr['optional'] = $options['optional'] === true;
        } else {
            $optinsArr['optional'] = false;
        }
        $optinsArr['description'] = isset($options['description']) ? $options['description'] : null;
        $this->_checkDescIndex($optinsArr);
        $optinsArr['values'] = isset($options['values']) ? $options['values'] : [];
        $this->_checkValuesIndex($optinsArr);


        if (isset($options['default']) && gettype($options['default']) == 'string') {
            $optinsArr['default'] = $options['default'];
        }

        return $optinsArr;
    }
    private function _checkDescIndex(&$options) {
        if (isset($options['description'])) {
            $trimmedDesc = trim($options['description']);

            if (strlen($trimmedDesc) > 0) {
                $options['description'] = $trimmedDesc;
            } else {
                $options['description'] = '<NO DESCRIPTION>';
            }
        } else {
            $options['description'] = '<NO DESCRIPTION>';
        }
    }
    private function _checkIsArgsSet() {
        $missingMandatury = [];

        foreach ($this->commandArgs as $attrName => $attrArray) {
            if (!$attrArray['optional'] && $attrArray['val'] === null) {
                if (isset($attrArray['default'])) {
                    $this->commandArgs[$attrName]['val'] = $attrArray['default'];
                } else {
                    $missingMandatury[] = $attrName;
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
    private function _checkSelectedChoice($choices, $default, $input) {
        if (in_array($input, $choices)) {
            return $input;
        } else if (isset($choices[$input])) {
            return $choices[$input];
        } else if (strlen($input) == 0 && $default !== null) {
            return $default;
        } else {
            $this->error('Invalid answer.');
        }
    }
    private function _checkValuesIndex(&$options) {
        if (isset($options['values']) && gettype($options['values']) == 'array') {
            $vals = [];

            foreach ($options['values'] as $val) {
                $type = gettype($val);

                if ($type == 'boolean') {
                    if ($val === true) {
                        $vals[] = 'y';
                        $vals[] = 'Y';
                    } else {
                        $vals[] = 'n';
                        $vals[] = 'N';
                    }
                } else if ($type != 'object' && $val !== null && strlen($val) != 0) {
                    $vals[] = $val.'';
                }
            }
            $options['values'] = $vals;
        } else {
            $options['values'] = [];
        }
    }
    private static function _getFormattedOutput($outputString, $formatOptions) {
        $outputManner = self::getCharsManner($formatOptions);

        if (strlen($outputManner) != 0) {
            return "\e[".$outputManner."m$outputString\e[0m";
        }

        return $outputString;
    }
    private function _parseArgs() {
        $this->addArg('--ansi', [
            'optional' => true,
            'description' => 'Force the use of ANSI output.'
        ]);
        $this->addArg('--no-ansi', [
            'optional' => true,
            'description' => 'Force the output to not use ANSI.'
        ]);
        $options = array_keys($this->commandArgs);

        foreach ($options as $optName) {
            $this->commandArgs[$optName]['val'] = $this->getArgValue($optName);
        }
    }
    private function _printChoices($choices, $default) {
        foreach ($choices as $choiceIndex => $choiceTxt) {
            if ($choiceTxt == $default) {
                $this->println($choiceIndex.": ".$choiceTxt, [
                    'color' => 'light-blue',
                    'bold' => 'true'
                ]);
            } else {
                $this->println($choiceIndex.": ".$choiceTxt);
            }
        }
    }
    private static function _validateOutputOptions($formatArr) {
        $noColor = 'NO_COLOR';

        if (gettype($formatArr) == 'array' && count($formatArr) !== 0) {
            if (!isset($formatArr['bold'])) {
                $formatArr['bold'] = false;
            }

            if (!isset($formatArr['underline'])) {
                $formatArr['underline'] = false;
            }

            if (!isset($formatArr['blink'])) {
                $formatArr['blink'] = false;
            }

            if (!isset($formatArr['reverse'])) {
                $formatArr['reverse'] = false;
            }

            if (!isset($formatArr['color'])) {
                $formatArr['color'] = $noColor;
            }

            if (!isset($formatArr['bg-color'])) {
                $formatArr['bg-color'] = $noColor;
            }

            return $formatArr;
        }

        return [
            'bold' => false,
            'underline' => false,
            'reverse' => false,
            'blink' => false,
            'color' => $noColor, 
            'bg-color' => $noColor
        ];
    }
    private static function addManner($str, $code) {
        if (strlen($str) > 0) {
            return $str.';'.$code;
        }

        return $str.$code;
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
    private static function getCharsManner($options) {
        $mannerStr = '';

        if (isset($options['force-styling'])) {
            $forceStyling = $options['force-styling'] === true;
        } else {
            $forceStyling = false;
        }

        if (isset($options['no-ansi']) && $options['no-ansi'] === true) {
            return $mannerStr;
        }

        if (!$forceStyling) {
            $os = php_uname('s');
            $notSupported = [
                'Windows NT'
            ];

            if (in_array($os, $notSupported)) {
                return $mannerStr;
            }
        }

        if ($options['bold']) {
            $mannerStr = self::addManner($mannerStr, 1);
        }

        if ($options['underline']) {
            $mannerStr = self::addManner($mannerStr, 4);
        }

        if ($options['blink']) {
            $mannerStr = self::addManner($mannerStr, 5);
        }

        if ($options['reverse']) {
            $mannerStr = self::addManner($mannerStr, 7);
        }

        if (defined('NO_COLOR') || isset($_SERVER['NO_COLOR']) || getenv('NO_COLOR') !== false) {
            //See https://no-color.org/ for more info.
            return $mannerStr;
        }

        if ($options['color'] != 'NO_COLOR') {
            $mannerStr = self::addManner($mannerStr, self::COLORS[$options['color']]);
        }

        if ($options['bg-color'] != 'NO_COLOR') {
            $mannerStr = self::addManner($mannerStr, self::COLORS[$options['bg-color']] + 10);
        }

        return $mannerStr;
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
    private function getInputHelper($input, $validator, $default) {
        $retVal = [
            'valid' => true,
            'value' => $input
        ];

        if (strlen($input) == 0 && $default !== null) {
            $retVal['value'] = $default;
        } else {
            if (is_callable($validator)) {
                $retVal['valid'] = call_user_func_array($validator, [$input]);

                if (!($retVal['valid'] === true)) {
                    $this->error('Invalid input is given. Try again.');
                }
            }
        }

        return $retVal;
    }
}
