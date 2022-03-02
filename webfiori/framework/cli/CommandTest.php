<?php
namespace webfiori\framework\cli;

use webfiori\framework\cli\FileInputStream;
use webfiori\framework\cli\FileOutputStream;
use PHPUnit\Framework\TestCase;
/**
 * A class which can be used to test custom made CLI commands.
 * 
 * The class uses files as input streams and output streams. What it does
 * is it executes a command and read inputs from file and send output to 
 * file. Then it can be used to compare final output with specific string
 * value to validate command execution result.
 *
 * @author Ibrahim
 */
class CommandTest {
    private $exitStatus;
    /**
     * Creates new instance.
     * 
     * @param string $inputFile The path to the file that will be used as input
     * stream.
     * 
     * @param string $outputFile The path to the file that will be used as
     * output stream.
     */
    public function __construct($inputFile, $outputFile) {
        CLI::setInputStream(new FileInputStream($inputFile));
        CLI::setOutputStream(new FileOutputStream($outputFile));
    }
    public function getOutputsArray() {
        return CLI::getOutputStream()->readOutput();
    }
    /**
     * Execute a command given as an object.
     * 
     * @param CLICommand $command The command that will be executed.
     * 
     * @param array $argsVals An optional array that contains the values
     * of command arguments (if the command supports args). The indices of
     * the array should hold arguments names and the value of each index is the
     * value of the argument.
     * 
     * @return int
     */
    public function runCommand(CLICommand $command, array $argsVals = []) {
        CLI::getOutputStream()->reset();
        foreach ($argsVals as $argName => $argVal) {
            $command->setArgValue($argName, $argVal);
        }
        $this->exitStatus = $command->excCommand();
        return $this->exitStatus;
    }
    /**
     * Checks if exis status of the command equals to specific value or not.
     * 
     * @param int $val The value to check with.
     * 
     * @return boolean If exit status is equals to the given value, the method
     * will return true. Other than that, the method will return false.
     */
    public function assertExitStatusEquals($val) {
        return $this->exitStatus == $val;
    }
    /**
     * Checks if the content of output stream match a given output as array.
     * 
     * @param array $outputsStr An array that contains strings which should
     * represents the outputs. At each index, a string that represents
     * one line of output.
     * 
     * @return boolean If the actual output match with the given value, the
     * method will return true. Other than that, the method will return
     * false.
     */
    public function assertOutputEquals(array $outputsStr, TestCase $case = null) {
        $actualOutputArr = $this->getOutputsArray();
        $isEqual = count($actualOutputArr) == count($outputsStr);
        
        if ($isEqual) {
            for ($x = 0 ; $x < count($actualOutputArr) ; $x++) {
                if ($case !== null) {
                    $case->assertEquals($outputsStr[$x], $actualOutputArr[$x]);
                }
                $isEqual = $actualOutputArr[$x] == $outputsStr[$x];
            }
        }
        return $isEqual;
    }
}
