<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\ClassWriter;
use webfiori\framework\cli\helpers\ClassInfoReader;
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
    public function __construct(CreateCommand $command) {
        $this->command = $command;
        $this->classWriter = new ClassWriter();
        $this->classInfoReader = new ClassInfoReader($this->command);
    }
    /**
     * 
     * @return ClassWriter
     */
    public function getWriter() {
        return $this->classWriter;
    }
    public function setPath($path) {
        $this->getWriter()->setPath($path);
    }
    public function setNamespace($ns) {
        $this->getWriter()->setNamespace($ns);
    }
    public function setClassName($name) {
        $this->getWriter()->setClassName($name);
    }
    public function setClassInfo($ns, $suffix) {
        $classInfo = $this->getClassInfo($ns, $suffix);
        $this->setNamespace($classInfo['namespace']);
        $this->setClassName($classInfo['name']);
        $this->setPath($classInfo['path']);
    }
    public function appendTop() {
        $this->append([
            '<?php',
            "namespace ".$this->getWriter()->getNamespace().";\n"
        ], 0);
    }
    public function append($strArr, $tapsCount = 0) {
        if (gettype($strArr) == 'array') {
            foreach ($strArr as $str) {
                $this->getWriter()->append($str, $tapsCount);
            }
        } else {
            $this->getWriter()->append($strArr, $tapsCount);
        }
    }
    public function writeClass() {
        $this->getWriter()->writeClass();
        $this->info('New class was created at "'.$this->getWriter()->getPath().'".');
    }
    /**
     * 
     * @return CreateCommand
     */
    public function getCommand() {
        return $this->command;
    }
    public function error($message) {
        $this->getCommand()->error($message);
    }
    public function prints($str, ...$_) {
        $this->getCommand()->prints($str, $_);
    }
    public function warning($message) {
        $this->getCommand()->warning($message);
    }
    public function info($message) {
        $this->getCommand()->info($message);
    }
    public function success($message) {
        $this->getCommand()->success($message);
    }
    public function confirm($confirmTxt, $default = null) {
        return $this->getCommand()->confirm($confirmTxt, $default);
    }
    public function getInput($prompt, $default = null, $validator = null) {
        return $this->getCommand()->getInput($prompt, $default, $validator);
    }
    public function println($str = '', ...$_) {
        $this->getCommand()->println($str, $_);
    }
    public function select($prompt, $choices, $defaultIndex = null) {
        $this->getCommand()->select($prompt, $choices, $defaultIndex);
    }
    public function getClassInfo($defaultNs = null, $suffix = null) {
        return $this->classInfoReader->readClassInfo($defaultNs, $suffix);
    }
}
