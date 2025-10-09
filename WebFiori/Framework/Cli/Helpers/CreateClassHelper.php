<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Cli\Command;
use WebFiori\Cli\InputValidator;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\ClassWriter;
/**
 * A wrapper class which helps in creating classes using CLI.
 *
 * @author Ibrahim
 */
class CreateClassHelper {
    /**
     *
     * @var ClassInfoReader
     */
    private $classInfoReader;
    /**
     *
     * @var ClassWriter
     */
    private $classWriter;
    /**
     *
     * @var CreateCommand
     */
    private $command;
    /**
     * Creates new instance.
     *
     * @param Command $command The command that will be used to read inputs
     * and send outputs to the terminal.
     *
     * @param ClassWriter $writer The writer that will hold class information.
     */
    public function __construct(Command $command, ?ClassWriter $writer = null) {
        $this->command = $command;
        $this->classWriter = $writer;
        $this->classInfoReader = new ClassInfoReader($this->command);
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
     * @param boolean|null $default Default answer to use if empty input is given.
     * It can be true for 'y' and false for 'n'. Default value is null which
     * means no default will be used.
     * 
     * @return boolean If the user choose 'y', the method will return true. If
     * he choose 'n', the method will return false.
     */
    public function confirm(string $confirmTxt, ?bool $default = null) {
        return $this->getCommand()->confirm($confirmTxt, $default);
    }
    /**
     * Display a message that represents an error.
     *
     * The message will be prefixed with the string 'Error:' in
     * red.
     *
     * @param string $message The message that will be shown.
     */
    public function error(string $message) {
        $this->getCommand()->error($message);
    }
    /**
     * Initiate the CLI process which is used to read class information.
     *
     * @param string $defaultNs Default namespace to use in case the user did not
     * provide one.
     *
     * @param string $suffix A string to append to the name of the class if it
     * was not in provided name.
     */
    public function getClassInfo(?string $defaultNs = null, ?string $suffix = null) {
        return $this->classInfoReader->readClassInfo($defaultNs, $suffix);
    }
    /**
     * Returns the command which is used to read inputs and show outputs.
     *
     * @return Command
     */
    public function getCommand() : Command {
        return $this->command;
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
     * @param InputValidator $validator A validator that can be used to validate user
     * input. If the value is valid, the callback must return true.
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
     */
    public function getInput(string $prompt, ?string $default = null, ?InputValidator $validator = null) {
        return $this->getCommand()->getInput($prompt, $default, $validator);
    }
    /**
     * Returns an instance of the class that is used to write the final output.
     *
     * @return ClassWriter An instance of the class that is used to write the final output.
     */
    public function getWriter() : ClassWriter {
        return $this->classWriter;
    }
    /**
     * Display a message that represents extra information.
     *
     * The message will be prefixed with the string 'Info:' in
     * blue.
     *
     * @param string $message The message that will be shown.
     *
     */
    public function info(string $message) {
        $this->getCommand()->info($message);
    }
    /**
     * Print out a string and terminates the current line by writing the
     * line separator string.
     *
     * This method will work like the function fprintf(). The difference is that
     * it will print out to the stream at which was specified by the method
     * Command::setOutputStream() and the text can have formatting
     * options. Note that support for output formatting depends on terminal support for
     * ANSI escape codes.
     *
     * @param string $str The string that will be printed.
     *
     * @param mixed $_ One or more extra arguments that can be supplied to the
     * method. The last argument can be an array that contains text formatting options.
     * for available options, check the method Command::formatOutput().
     */
    public function println($str = '', ...$_) {
        $this->getCommand()->println($str, $_);
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
    public function select($prompt, $choices, $defaultIndex = -1) {
        return $this->getCommand()->select($prompt, $choices, $defaultIndex);
    }
    /**
     * Initiate the CLI process which is used to read class information.
     *
     * @param string $ns Default namespace to use in case the user did not
     * provide one.
     *
     * @param string $suffix A string to append to the name of the class if it
     * was not in provided name.
     */
    public function setClassInfo(string $ns, string $suffix) {
        $classInfo = $this->getClassInfo($ns, $suffix);
        $this->setNamespace($classInfo['namespace']);

        if ($suffix != $classInfo['name']) {
            $this->setClassName($classInfo['name']);
        }
        $this->setPath($classInfo['path']);
    }
    /**
     * Sets the name of the class will be created on.
     *
     * @param string $name A string that represents class name.
     *
     * @return boolean If the name is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setClassName(string $name) : bool {
        return $this->getWriter()->setClassName($name);
    }
    /**
     * Sets the namespace of the class that will be created.
     *
     * @param string $ns The namespace.
     *
     * @return boolean If the namespace is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setNamespace($ns) : bool {
        return $this->getWriter()->setNamespace($ns);
    }
    /**
     * Sets the location at which the class will be created on.
     *
     * @param string $path A string that represents folder path.
     *
     * @return boolean If the path is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setPath(string $path): bool {
        return $this->getWriter()->setPath($path);
    }
    /**
     * Display a message that represents a success status.
     *
     * The message will be prefixed with the string "Success:" in green.
     *
     * @param string $message The message that will be displayed.
     *
     */
    public function success($message) {
        $this->getCommand()->success($message);
    }
    /**
     * Display a message that represents a warning.
     *
     * The message will be prefixed with the string 'Warning:' in
     * red.
     *
     * @param string $message The message that will be shown.
     *
     */
    public function warning(string $message) {
        $this->getCommand()->warning($message);
    }
    /**
     * Creates the class which is based on the writer.
     *
     * @param bool $showOutput If this is set to true, a message which
     * states that a new class was created at the location which was specified
     * by the writer.
     */
    public function writeClass(bool $showOutput = true) {
        $this->getWriter()->writeClass();

        if ($showOutput) {
            $this->info('New class was created at "'.$this->getWriter()->getPath().'".');
        }
    }
}
