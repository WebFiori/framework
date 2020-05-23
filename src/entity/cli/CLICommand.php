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

/**
 * An abstract class that can be used to create new CLI command.
 * The developer can extend this class and use it to create a custom CLI 
 * command.
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
     * The name of the command such as '--help'.
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
     * command such as '-v' or '--help'. If not provided, the 
     * value '--new-command' is used.
     * @param array $args An indexed array of sub-associative arrays of arguments (or options) which can 
     * be supplied to the command when running it. The 
     * key of each sub array is argument name. Each 
     * sub-array can have the following indices:
     * <ul>
     * <li><b>optional</b>: A boolean. if set to true, it means that the argument 
     * is optional and can be ignored when running the command.</li>
     * <li><b>default</b>: An optional default value for the argument 
     * to use if it is not provided and is optional.</li>
     * <li><b>description</b>: A description of the argument which 
     * will be shown if the command '--help' is executed.</li>
     * <li><b>values</b>: A set of values that the argument can have. If provided, 
     * only the values on the list will be allowed. Note that if null or empty string 
     * is in the array, it will be ignored. Also, if boolean values are 
     * provided, true will be converted to the string 'y' and false will 
     * be converted to the string 'n'.</li>
     * </ul>
     * @param string $description A string that describes what does the job 
     * do. The description will appear when the command '--help' is executed.
     * @since 1.0
     */
    public function __construct($commandName, $args = [], $description = '') {
        if (!$this->setName($commandName)) {
            $this->setName('--new-command');
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
     * is '--do-it' and one argument has the name 'what-to-do', then the full 
     * CLI command would be "--do-it what-to-do=say-hi". An argument can be 
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
     * will be shown if the command '--help' is executed.</li>
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
     * will be shown if the command '--help' is executed.</li>
     * <li><b>values</b>: A set of values that the argument can have. If provided, 
     * only the values on the list will be allowed. Note that if null or empty string 
     * is in the array, it will be ignored. Also, if boolean values are 
     * provided, true will be converted to the string 'y' and false will 
     * be converted to the string 'n'.</li>
     * </ul>
     */
    public function addArgs($arr) {
        $this->commandArgs = [];

        if (gettype($arr) == 'array') {
            foreach ($arr as $optionName => $options) {
                $this->addArg($optionName, $options);
            }
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
                    fprintf(STDOUT, " ");
                    $this->moveCursorLeft();
                }
                $this->moveCursorRight($asInt);
            } else {
                $this->moveCursorRight();

                for ($x = 0 ; $x < $numberOfCols ; $x++) {
                    fprintf(STDOUT, " ");
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
        fprintf(STDOUT, "\ec");
    }
    /**
     * Clears the line at which the cursor is in and move it back to the start 
     * of the line.
     * Note that support for this operation depends on terminal support for 
     * ANSI escape codes.
     * @since 1.0
     */
    public function clearLine() {
        fprintf(STDOUT, "\e[2K");
        fprintf(STDOUT, "\r");
    }
    /**
     * Display a message that represents an error.
     * The message will be prefixed with the string 'Error:' in 
     * red. The output will be sent to STDERR.
     * @param string $message The message that will be shown.
     * @since 1.0
     */
    public function error($message) {
        fprintf(STDERR, self::formatOutput('Error:', [
            'color' => 'light-red',
            'force-styling' => $this->isArgProvided('force-styling')
        ]).' '.$message);
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
     * command do and it will appear in CLI if the command '--help' is executed.
     * @return string The description of the command. Default return value 
     * is '&lt;NO DESCRIPTION&gt;'
     * @since 1.0
     */
    public function getDescription() {
        return $this->description;
    }
    /**
     * Returns the name of the command.
     * The name of the command is a string which is used to call the command 
     * from CLI.
     * @return string The name of the command (such as '-v' or '--help'). Default 
     * return value is '--new-command'.
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
            fprintf(STDOUT, "\e[".$asInt."B");
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
            fprintf(STDOUT, "\e[".$asInt."D");
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
            fprintf(STDOUT, "\e[".$asInt."C");
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
            fprintf(STDOUT, "\e[".$lineAsInt.";".$colAsInt."H");
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
            fprintf(STDOUT, "\e[".$asInt."A");
        }
    }
    /**
     * Sets the description of the command.
     * The description of the command is a string that describes what does the 
     * command do and it will appear in CLI if the command '--help' is executed.
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
     * @param string $name The name of the command (such as '-v' or '--help'). 
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
    private function _checkArgOptions($options) {
        $optinsArr = [];

        if (isset($options['optional'])) {
            $optinsArr['optional'] = $options['optional'] === true;
        } else {
            $optinsArr['optional'] = false;
        }

        if (isset($options['description'])) {
            $trimmedDesc = trim($options['description']);

            if (strlen($trimmedDesc) > 0) {
                $optinsArr['description'] = $trimmedDesc;
            } else {
                $optinsArr['description'] = '<NO DESCRIPTION>';
            }
        } else {
            $optinsArr['description'] = '<NO DESCRIPTION>';
        }
        
        if (isset($options['values']) && gettype($options['values']) == 'array') {
            $vals = [];
            foreach ($options['values'] as $val) {
                $type = gettype($val);
                if ($type == 'boolean') {
                    if ($val === true) {
                        $vals[] = 'y';
                    } else {
                        $vals[] = 'n';
                    }
                } else {
                    if ($type != 'object' && $val !== null && strlen($val) != 0) {
                        $vals[] = $val.'';
                    }
                }
            }
            $optinsArr['values'] = $vals;
        } else {
            $optinsArr['values'] = [];
        }
        
        if (isset($options['default']) && gettype($options['default']) == 'string') {
            $optinsArr['default'] = $options['default'];
        }
        
        return $optinsArr;
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
            $invalidStr = 'The following required argument(s) have invalid values: ';
            $comma = '';

            foreach ($invalidArgsVals as $argName) {
                $invalidStr .= $comma.'"'.$argName.'"';
                $comma = ', ';
            }
            $this->error($invalidStr."\n");
            foreach ($invalidArgsVals as $argName) {
                fprintf(STDOUT, $this->formatOutput('Info:', [
                    'color' => 'light-yellow',
                    'force-styling' => $this->isArgProvided('force-styling')
                ]). " Allowed values for the argument '$argName':\n");
                foreach ($this->commandArgs[$argName]['values'] as $val) {
                    fprintf(STDOUT, "$val\n");
                }
            }
            return false;
        }

        return true;
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
            $this->error($missingStr."\n");

            return false;
        }

        return true;
    }
    private static function _getFormattedOutput($outputString, $formatOptions) {
        $outputManner = self::getCharsManner($formatOptions);

        if (strlen($outputManner) != 0) {
            return "\e[".$outputManner."m$outputString \e[0m";
        }

        return $outputString;
    }
    private function _parseArgs() {
        $this->addArg('force-styling', [
            'optional' => true,
            'description' => 'If this argument is set, output will be forced to '
            . 'appear with colors and styles using ANSI escape sequences.'
        ]);
        $options = array_keys($this->commandArgs);

        foreach ($options as $optName) {
            $this->commandArgs[$optName]['val'] = $this->getArgValue($optName);
        }
    }
    private static function _validateOutputOptions($formatArr) {
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
                $formatArr['color'] = 'white';
            }

            if (!isset($formatArr['bg-color'])) {
                $formatArr['bg-color'] = 'black';
            }

            return $formatArr;
        }

        return [
            'bold' => false,
            'underline' => false,
            'reverse' => false,
            'blink' => false,
            'color' => 'white', 
            'bg-color' => 'black'
        ];
    }
    private static function addManner($str, $code) {
        if (strlen($str) > 0) {
            return $str.';'.$code;
        }

        return $str.$code;
    }
    private static function getCharsManner($options) {
        $mannerStr = '';
        if (isset($options['force-styling'])) {
            $forceStyling = $options['force-styling'] === true;
        } else {
            $forceStyling = false;
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
        if (defined('NO_COLOR') || isset($_SERVER['NO_COLOR']) || getenv('NO_COLOR') !== false || !$forceStyling) {
            //See https://no-color.org/ for more info.
            return $mannerStr;
        }
        $mannerStr2 = self::addManner($mannerStr, self::COLORS[$options['color']]);

        return self::addManner($mannerStr2, self::COLORS[$options['bg-color']] + 10);
    }
}
