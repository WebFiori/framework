<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh, phMysql library.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace webfiori\framework\cli\writers;

use webfiori\framework\File;
/**
 * A utility class which is used as a helper class with the command 'create'.
 * This class can be used to write .php classes.
 * 
 * @author Ibrahim
 * 
 * @version 1.0.1
 */
class ClassWriter {
    private $useArr;
    /**
     * The generated class as string.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $classAsStr;
    /**
     * The name of the class that will be created.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $className;
    /**
     * The namespace that the class will belong to.
     * 
     * @var string 
     */
    private $ns;
    /**
     * The location at which the entity class will be created on.
     * 
     * @since 1.0 
     */
    private $path;
    /**
     * Creates new instance of the class.
     * 
     * @param array $classInfoArr An associative array that contains the information 
     * of the class that will be created. The array must have the following indices: 
     * <ul>
     * <li><b>name</b>: The name of the class that will be created. If not provided, the 
     * string 'NewClass' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided, 
     * the namespace 'webfiori' is used.</li>
     * <li><b>path</b>: The location at which the class will be created on. If not 
     * provided, the constant ROOT_DIR is used. </li>
     * 
     * </ul>
     */
    public function __construct($classInfoArr = []) {
        if (isset($classInfoArr['namespace']) && strlen($classInfoArr['namespace']) != 0) {
            $this->ns = $classInfoArr['namespace'];
        } else {
            $this->ns = 'webfiori';
        }

        if (isset($classInfoArr['path'])) {
            $this->path = $classInfoArr['path'];
        } else {
            $this->path = ROOT_DIR;
        }

        if (isset($classInfoArr['name'])) {
            $this->className = $classInfoArr['name'];
        } else {
            $this->className = 'NewClass';
        }
        $this->useArr = [];
    }
    public function setNamespace($namespace) {
        $this->ns = $namespace;
    }
    public function setPath($path) {
        $this->path = $path;
    }
    public function setClassName($name) {
        $this->className = $name;
    }
    /**
     * Appends a string or array of strings to the string that represents the
     * body of the class.
     * 
     * @param string $strOrArr The string that will be appended. At the end of the string 
     * a new line character will be appended. This can also be an array of strings.
     * 
     * @param int $tabsCount The number of tabs that will be added to the string. 
     * A tab is represented as 4 spaces.
     * 
     * @since 1.0
     */
    public function append($strOrArr, $tabsCount = 0) {
        if (gettype($strOrArr) == 'array') {
            foreach ($strOrArr as $str) {
                $this->_a($str, $tabsCount);
            }
        } else {
            $this->_a($strOrArr, $tabsCount);
        }
    }
    public abstract function writeClassComment();
    public abstract function writeClassDeclaration();
    public abstract function writeClassBody();
    
    public function writeUseStatements() {
        $useArr = [];
        foreach ($this->useArr as $className) {
            $useArr[] = 'use '.$className.';';
        }
        $this->append($useArr);
    }
    public function getUseStatements() {
        return $this->useArr;
    }
    public function addUseStatement($classesToUse) {
        if (gettype($classesToUse) == 'array') {
            foreach ($classesToUse as $class) {
                $this->useArr[] = $class;
            }
        } else {
            $this->useArr[] = $classesToUse;
        }
    }
    /**
     * Appends the string that represents the start of PHP class.
     * 
     * The method will add the tag '&lt;php?' in addition to namespace declaration.
     */
    public function writeNsDeclaration() {
        $this->append([
            '<?php',
            'namespace '.$this->getNamespace().";\n",
            ''
        ]);
    }
    private function _a($str, $tapsCount) {
        $tabStr = str_repeat('    ', $tapsCount);
        $this->classAsStr .= $tabStr.$str."\n";
    }
    /**
     * Returns the absolute path of the class that will be created.
     * 
     * @return string The absolute path of the file that holds class information.
     * 
     * @since 1.0.1
     */
    public function getAbsolutePath() {
        return $this->getPath().DS.$this->className.'.php';
    }
    /**
     * Returns the name of the class that will be created.
     * 
     * @return string The name of the class that will be created.
     * 
     * @since 1.0
     */
    public function getName() {
        return $this->className;
    }
    /**
     * Returns the namespace at which the generated class will be added to.
     * 
     * @return string The namespace at which the generated class will be added to.
     * 
     * @since 1.0
     */
    public function getNamespace() {
        return $this->ns;
    }
    /**
     * Returns the location at which the class will be created on.
     * 
     * @return string The location at which the class will be created on.
     * 
     * @since 1.0
     */
    public function getPath() {
        return $this->path;
    }
    /**
     * Write the new class to a .php file.
     * 
     * Note that the method will remove the file if it was already created and create
     * new one.
     * 
     * @since 1.0
     */
    public function writeClass() {
        $classFile = new File($this->className.'.php', $this->path);
        $classFile->remove();
        $this->classAsStr = '';
        $this->addNsDeclaration();
        $this->writeUseStatements();
        $this->writeClassComment();
        $this->writeClassDeclaration();
        $this->writeClassBody();
        $classFile->setRawData($this->classAsStr);
        $classFile->write(false, true);
    }
}
