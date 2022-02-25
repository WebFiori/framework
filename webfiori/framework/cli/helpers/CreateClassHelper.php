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
    public function __construct(CreateCommand $command, ClassWriter $writer = null) {
        $this->command = $command;
        $this->classWriter = $writer;
        $this->classInfoReader = new ClassInfoReader($this->command);
    }
    /**
     * Sets the writer to new one.
     * 
     * Note that if the writer was already set, the name of the class, path and
     * namespace will be copied from the one which was set before.
     * 
     * @param ClassWriter $writer
     */
    public function setWriter(ClassWriter $writer) {
        if ($writer !== null) {
            $current = $this->getWriter();
            $this->classWriter = $writer;
            $this->classWriter->setClassName($current->getName());
            $this->classWriter->setPath($current->getPath());
            $this->classWriter->setNamespace($current->getNamespace());
        }
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
