<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Writers;

use WebFiori\File\File;
/**
 * A utility class which is used as a helper class to auto-generate PHP classes.
 * This class can be used to write .php classes.
 *
 * @author Ibrahim
 *
 * @version 1.0.1
 */
abstract class ClassWriter {
    /**
     * The generated class as string.
     *
     * @var string
     *
     * @since 1.0
     */
    private $classLines;
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
    private $suffix;
    private $useArr;
    /**
     * Creates new instance of the class.
     *
     * @param string $name The name of the class that will be created. If not provided, the
     * string 'NewClass' is used.
     *
     * @param string $path The location at which the class will be created on. If not
     * provided, the constant ROOT_PATH is used.
     *
     * @param string $ns The namespace that the class will belong to. If not provided,
     * the global namespace is used.
     *
     * @param array $classInfoArr An associative array that contains the information
     * of the class that will be created. The array must have the following indices:
     */
    public function __construct(string $name = 'NewClass', string $path = ROOT_PATH, string $namespace = '\\') {
        $this->suffix = '';

        $this->useArr = [];

        if (!$this->setClassName($name)) {
            $this->setClassName('NewClass');
        }

        if (!$this->setPath($path)) {
            $this->setPath(ROOT_PATH);
        }

        if (!$this->setNamespace($namespace)) {
            $this->setNamespace('\\');
        }
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
                if (!in_array($class, $this->useArr)) {
                    $this->useArr[] = trim($class,'\\');
                }
            }
        } else if (!in_array($classesToUse, $this->useArr)) {
            $this->useArr[] = trim($classesToUse,'\\');
        }
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
        if (gettype($strOrArr) != 'array') {
            $this->a($strOrArr, $tabsCount);

            return;
        }

        foreach ($strOrArr as $str) {
            $this->a($str, $tabsCount);
        }
    }
    /**
     * Adds method definition to the class.
     *
     * @param string $funcName The name of the method.
     *
     * @param array $argsArr An associative array of method arguments. The
     * indices of the array are parameters names and values are types of
     * parameters.
     *
     * @param string|null $returns An optional name of return type. This can be
     * a string such as 'int|null|Object'.
     *
     * @return string The method will create method definition string and return
     * it.
     */
    public function f($funcName, $argsArr = [], ?string $returns = null) {
        return $this->method($funcName, $argsArr, $returns);
    }
    /**
     * Adds method definition with full control over modifiers.
     *
     * @param string $funcName Method name
     * @param array $argsArr Arguments [name => type]
     * @param string|null $returns Return type
     * @param string $visibility Visibility: 'public', 'protected', 'private'
     * @param bool $isStatic Is static method
     * @param bool $isAbstract Is abstract method
     * @param bool $isFinal Is final method
     * 
     * @return string Method signature
     */
    public function method(
        string $funcName, 
        array $argsArr = [], 
        ?string $returns = null,
        string $visibility = 'public',
        bool $isStatic = false,
        bool $isAbstract = false,
        bool $isFinal = false
    ) : string {
        $modifiers = [];
        
        if ($isFinal) {
            $modifiers[] = 'final';
        }
        if ($isAbstract) {
            $modifiers[] = 'abstract';
        }
        
        $modifiers[] = $visibility;
        
        if ($isStatic) {
            $modifiers[] = 'static';
        }
        
        $signature = implode(' ', $modifiers) . ' function ' . $funcName;
        
        $argsPart = '(';
        foreach ($argsArr as $argName => $argType) {
            if (strlen($argsPart) != 1) {
                $argsPart .= ', ';
            }
            $argsPart .= $argType . ' $' . $argName;
        }
        $argsPart .= ')';
        
        if ($returns !== null) {
            $argsPart .= ' : ' . $returns;
        }
        
        return $signature . $argsPart . ($isAbstract ? ';' : ' {');
    }
    /**
     * Generate a property declaration.
     *
     * @param string $name Property name
     * @param string $visibility Visibility: 'public', 'protected', 'private'
     * @param string|null $type Property type
     * @param string|null $defaultValue Default value as string
     * @param bool $isStatic Is static property
     * @param bool $isReadonly Is readonly property (PHP 8.1+)
     * 
     * @return string Property declaration
     */
    public function property(
        string $name,
        string $visibility = 'private',
        ?string $type = null,
        ?string $defaultValue = null,
        bool $isStatic = false,
        bool $isReadonly = false
    ) : string {
        $modifiers = [$visibility];
        
        if ($isReadonly) {
            $modifiers[] = 'readonly';
        }
        if ($isStatic) {
            $modifiers[] = 'static';
        }
        
        $declaration = implode(' ', $modifiers);
        
        if ($type !== null) {
            $declaration .= ' ' . $type;
        }
        
        $declaration .= ' $' . $name;
        
        if ($defaultValue !== null) {
            $declaration .= ' = ' . $defaultValue;
        }
        
        return $declaration . ';';
    }
    /**
     * Generate a constant declaration.
     *
     * @param string $name Constant name
     * @param string $value Constant value as string
     * @param string $visibility Visibility: 'public', 'protected', 'private'
     * 
     * @return string Constant declaration
     */
    public function constant(
        string $name,
        string $value,
        string $visibility = 'public'
    ) : string {
        return $visibility . ' const ' . $name . ' = ' . $value . ';';
    }
    /**
     * Start building a docblock.
     *
     * @param string $description Main description
     * 
     * @return DocblockBuilder
     */
    public function docblock(string $description = '') : DocblockBuilder {
        return new DocblockBuilder($this, $description);
    }
    /**
    /**
     * Add an attribute for a class.
     *
     * @param string $name Attribute name (without #)
     * @param array $params Attribute parameters
     * @param int $indent Indentation level
     * 
     * @return $this For chaining
     */
    public function classAttribute(string $name, array $params = [], int $indent = 0) {
        $this->append($this->formatAttribute($name, $params), $indent);
        return $this;
    }
    /**
     * Add an attribute for a property.
     *
     * @param string $name Attribute name (without #)
     * @param array $params Attribute parameters
     * @param int $indent Indentation level
     * 
     * @return $this For chaining
     */
    public function propertyAttribute(string $name, array $params = [], int $indent = 1) {
        $this->append($this->formatAttribute($name, $params), $indent);
        return $this;
    }
    /**
     * Add an attribute for a method.
     *
     * @param string $name Attribute name (without #)
     * @param array $params Attribute parameters
     * @param int $indent Indentation level
     * 
     * @return $this For chaining
     */
    public function methodAttribute(string $name, array $params = [], int $indent = 1) {
        $this->append($this->formatAttribute($name, $params), $indent);
        return $this;
    }
    /**
     * Format an attribute string.
     *
     * @param string $name Attribute name
     * @param array $params Attribute parameters
     * 
     * @return string Formatted attribute
     */
    private function formatAttribute(string $name, array $params = []) : string {
        $attr = '#[' . $name;
        
        if (!empty($params)) {
            $args = [];
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $args[] = $this->formatAttributeValue($value);
                } else {
                    $args[] = $key . ': ' . $this->formatAttributeValue($value);
                }
            }
            $attr .= '(' . implode(', ', $args) . ')';
        }
        
        $attr .= ']';
        return $attr;
    }
    /**
     * Format a value for attribute parameters.
     *
     * @param mixed $value The value to format
     * 
     * @return string Formatted value
     */
    private function formatAttributeValue($value) : string {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            $items = array_map([$this, 'formatAttributeValue'], $value);
            return '[' . implode(', ', $items) . ']';
        }
        if (is_null($value)) {
            return 'null';
        }
        return (string)$value;
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
     * @param bool $withNs If this argument is set to true, the namespace of
     * the class will be pre-appended tp class name.
     *
     * @return string The name of the class that will be created. Default is
     * 'NewClass'
     *
     * @since 1.0
     */
    public function getName(bool $withNs = false) : string {
        $retVal = $this->className.$this->getSuffix();

        if ($withNs) {
            $ns = $this->getNamespace();
            
            if ($ns == '\\') {
                
                return '\\'.$retVal;
            }
            
            return $ns.'\\'.$retVal;
        }

        return $retVal;
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
     * default is the value of the contstant ROOT_PATH
     *
     * @since 1.0
     */
    public function getPath() : string {
        return $this->path;
    }
    /**
     * Returns the string that will be appended to the name of the class.
     *
     * @return string The string that will be appended to the name of the class.
     * Default is empty string.
     */
    public function getSuffix() : string {
        return $this->suffix !== null ? $this->suffix : '';
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
     * Checks if provided string represents a valid namespace or not.
     *
     * @param string $ns A string to be validated.
     *
     * @return bool If the provided string represents a valid namespace, the
     * method will return true. False if it does not represent a valid namespace.
     */
    public static function isValidNamespace(string $ns) {
        if ($ns == '\\') {
            return true;
        }
        $split = explode('\\', $ns);

        foreach ($split as $subNs) {
            $len = strlen($subNs);

            for ($x = 0 ; $x < $len ; $x++) {
                $char = $subNs[$x];

                if ($x == 0 && $char >= '0' && $char <= '9') {
                    return false;
                }

                if (!(($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9') || $char == '_')) {
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * Remove the class at which the writer represents.
     */
    public function removeClass() {
        $classFile = new File($this->getAbsolutePath());
        $classFile->remove();
    }
    /**
     * Removes a single use statement.
     *
     * @param string $classToRemove The name of the class including its namespace
     * (e.g. app/hello/HelloClass).
     */
    public function removeUseStatement(string $classToRemove) {
        $temp = [];

        foreach ($this->getUseStatements() as $stm) {
            if ($stm !== $classToRemove) {
                $temp[] = $stm;
            }
        }
        $this->useArr = $temp;
    }
    /**
     * Sets the name of the class will be created on.
     *
     * @param string $name A string that represents class name.
     *
     * @return boolean If the name is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setClassName(string $name) {
        $trimmed = trim($name);

        if (!self::isValidClassName($trimmed)) {
            throw new \InvalidArgumentException(
                "Invalid class name '$name'. Class names must start with a letter or underscore, " .
                "followed by letters, numbers, or underscores."
            );
        }

        $this->className = $this->fixClassName($trimmed);
        return $this;
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
        $trimmed = trim($namespace, ' ');

        if (!self::isValidNamespace($trimmed)) {
            throw new \InvalidArgumentException(
                "Invalid namespace '$namespace'. Namespaces must contain valid PHP identifiers " .
                "separated by backslashes."
            );
        }
        
        $this->ns = $trimmed[0] == '\\' ? substr($trimmed, 1) : $trimmed;
        return $this;
    }
    /**
     * Sets the location at which the class will be created on.
     *
     * @param string $path A string that represents folder path.
     *
     * @return boolean If the path is successfully set, the method will return true.
     * Other than that, false is returned.
     */
    public function setPath(string $path) {
        $trimmed = trim($path);

        if (strlen($trimmed) == 0) {
            throw new \InvalidArgumentException("Path cannot be empty.");
        }
        
        $this->path = str_replace('\\', DS, str_replace('/', DS, $trimmed));
        return $this;
    }
    /**
     * Sets a string as a suffix to the class name.
     *
     * @param string $classNameSuffix A string to append to class name such as 'Table' or
     * 'Service'. It must be a string which is considered as valid class name.
     *
     * @return bool If set, the method will return true. False otherises.
     */
    public function setSuffix(string $classNameSuffix) {
        if (!self::isValidClassName($classNameSuffix)) {
            throw new \InvalidArgumentException(
                "Invalid suffix '$classNameSuffix'. Suffix must be a valid class name."
            );
        }

        $this->suffix = $classNameSuffix;
        $this->className = $this->fixClassName($this->className);
        return $this;
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
        $classFile->setRawData($this->getCode());
        $classFile->write(false, true);
    }
    /**
     * Generate the class code without writing to disk.
     *
     * @return string The generated class code
     */
    public function getCode() : string {
        $this->classLines = [];
        $this->writeNsDeclaration();
        $this->writeUseStatements();
        $this->writeClassComment();
        $this->writeClassDeclaration();
        $this->writeClassBody();
        return implode("\n", $this->normalizeCode($this->classLines));
    }    public abstract function writeClassBody();
    /**
     * Writes the top section of the class that contains class comment.
     */
    public abstract function writeClassComment();
    public abstract function writeClassDeclaration();
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
    private function a($str, $tapsCount) {
        $tabStr = str_repeat('    ', $tapsCount);
        $this->classLines[] = $tabStr.$str;
    }
    private function normalizeCode(array $lines) : array {
        $normalized = [];
        $prevLineEmpty = false;
        
        foreach ($lines as $line) {
            $isEmpty = trim($line) === '';
            
            if ($isEmpty && $prevLineEmpty) {
                continue;
            }
            
            $normalized[] = $line;
            $prevLineEmpty = $isEmpty;
        }
        
        return $normalized;
    }    private function fixClassName($className) {
        $classSuffix = $this->getSuffix();

        if ($classSuffix == '') {
            return $className;
        }
        $subSuffix = substr($className.'', strlen($className.'') - strlen($classSuffix.''));

        if ($subSuffix == $classSuffix) {
            return substr($className, 0, -strlen($classSuffix));
        }

        return $className;
    }
}
