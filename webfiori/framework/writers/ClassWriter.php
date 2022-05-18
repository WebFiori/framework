<?php
namespace webfiori\framework\writers;

use webfiori\framework\File;
/**
 * A utility class which is used as a helper class to auto-generate PHP classes.
 * This class can be used to write .php classes.
 * 
 * @author Ibrahim
 * 
 * @version 1.0.1
 */
abstract class ClassWriter {
    private $suffix;
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
     * @param string $name The name of the class that will be created. If not provided, the 
     * string 'NewClass' is used.
     * 
     * @param string $path The location at which the class will be created on. If not 
     * provided, the constant ROOT_DIR is used.
     * 
     * @param string $ns The namespace that the class will belong to. If not provided, 
     * the global namespace is used.
     * 
     * @param array $classInfoArr An associative array that contains the information 
     * of the class that will be created. The array must have the following indices: 
     */
    public function __construct(string $name, string $path, string $namespace) {
        $this->suffix = '';
        $this->useArr = [];
        if (!$this->setClassName($name)) {
            $this->setClassName('NewClass');
        }

        if (!$this->setPath($path)) {
            $this->setPath(ROOT_DIR);
        }

        if (!$this->setNamespace($namespace)) {
            $this->setNamespace('\\');
        }
        
    }
    /**
     * Sets a string as a suffix to the class name.
     * 
     * @param string $suffix A string to append to class name such as 'Table' or
     * 'Service'. It must be a string which is considered as valid class name.
     * 
     * @return bool If set, the method will return true. False otherises.
     */
    public function setSuffix(string $suffix) : bool {
        if (self::isValidClassName($suffix)) {
            $this->suffix = $suffix;
            $this->className = $this->fixClassName($this->className);
            return true;
        }
        return false;
    }
    private function fixClassName($className) {
        $suffix = $this->getSuffix();
        if ($suffix != '') {
            $subSuffix = substr($className, strlen($className) - strlen($suffix));

            if ($subSuffix != $suffix) {
                return $className;
            } else {
                return substr($className, 0, -strlen($suffix));
            }
        } else {
            return $className;
        }
    }

    /**
     * Sets the namespace of the class that will be created.
     * 
     * @param string $namespace
     * 
     * @return boolean If the namespace is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setNamespace(string $namespace) {
        $trimmed = trim($namespace);
        
        if (strlen($trimmed) == 0) {
            return false;
        }
        $this->ns = $trimmed;
    }
    /**
     * Sets the location at which the class will be created on.
     * 
     * @param string $path A string that represents folder path.
     * 
     * @return boolean If the path is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setPath(string $path) : bool {
        $trimmed = trim($path);
        
        if (strlen($trimmed) == 0) {
            return false;
        }
        $this->path = $path;
        return true;
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
        $trimmed = trim($name);
        if (self::isValidClassName($trimmed)) {
            $this->className = $this->fixClassName($trimmed);
            
            return true;
        }

        return false;
    }
    /**
     * Returns the string that will be appended to the name of the class.
     * 
     * @return string The string that will be appended to the name of the class.
     * Default is empty string.
     */
    public function getSuffix() : string {
        return $this->suffix;
    }
    /**
     * Checks if a given string represents a valid class name or not.
     * 
     * @param string $name A string to check such as 'My_Super_Class'.
     * 
     * @return bool If the given string is a valid class name, the method
     * will return true. False otherwise.
     */
    public static function isValidClassName(string $name) : bool {
        $len = strlen($name);

        if ($len > 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $name[$x];

                if ($x == 0 && $char >= '0' && $char <= '9') {
                    return false;
                }

                if (!(($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9') || $char == '_')) {
                    return false;
                }
            }

            return true;
        }

        return false;
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
    /**
     * Writes the top section of the class that contains class comment.
     */
    public abstract function writeClassComment();
    public abstract function writeClassDeclaration();
    public abstract function writeClassBody();
    /**
     * Writes the section of the class that contains the 'use' classes.
     */
    public function writeUseStatements() {
        $useClassesArr = [];
        foreach ($this->useArr as $className) {
            $useClassesArr[] = 'use '.$className.';';
        }
        $this->append($useClassesArr);
    }
    /**
     * Returns an array that contains all classes which will be included
     * in the 'use' part of the class.
     * 
     * @return array An array of strings.
     */
    public function getUseStatements() : array {
        return $this->useArr;
    }
    /**
     * Adds a single or multiple classes to be included in the 'use' section of the
     * class.
     * 
     * @param string|array $classesToUse A string or array of strings that
     * contains the names of the classes with namespace.
     */
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
        $nsStr = $this->getNamespace() != '\\' ? 'namespace '.$this->getNamespace().";" : '';
        $this->append([
            '<?php',
            $nsStr,
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
    public function getAbsolutePath() : string {
        return $this->getPath().DS.$this->getName().'.php';
    }
    /**
     * Returns the name of the class that will be created.
     * 
     * Note that the suffix will be appended to the name of the class
     * if it is set.
     * 
     * @return string The name of the class that will be created. Default is
     * 'NewClass'
     * 
     * @since 1.0
     */
    public function getName() : string {
        
        return $this->className.$this->getSuffix();
    }
    /**
     * Returns the namespace at which the generated class will be added to.
     * 
     * @return string The namespace at which the generated class will be added to.
     * default is '\' which is the global namespace.
     * 
     * @since 1.0
     */
    public function getNamespace() : string {
        return $this->ns;
    }
    /**
     * Returns the location at which the class will be created on.
     * 
     * @return string The location at which the class will be created on.
     * default is the value of the contstant ROOT_DIR
     * 
     * @since 1.0
     */
    public function getPath() : string {
        return $this->path;
    }
    /**
     * Remove the class at which the writer represents.
     */
    public function removeClass() {
        $classFile = new File($this->getAbsolutePath());
        $classFile->remove();
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
        $classFile = new File($this->getName().'.php', $this->getPath());
        $classFile->remove();
        $this->classAsStr = '';
        $this->writeNsDeclaration();
        $this->writeUseStatements();
        $this->writeClassComment();
        $this->writeClassDeclaration();
        $this->writeClassBody();
        $classFile->setRawData($this->classAsStr);
        $classFile->create();
        $classFile->write();
    }
}
