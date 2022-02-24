<?php
namespace webfiori\framework\cli\writers;

use webfiori\framework\cli\writers\ClassWriter;
/**
 * A class which is used to write CLI Commands classes.
 *
 * @author Ibrahim
 */
class CLICommandClassWriter extends ClassWriter {
    private $name;
    private $desc;
    private $args;
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
     * @param string $commandName A string that represents the name of the command.
     * 
     * @param string $commandDesc A string that represents the description of the command.
     * 
     * @param array $argsArr An associative array that holds the names of the argument
     * the command will have.
     */
    public function __construct($classInfoArr, $commandName, $commandDesc, $argsArr = []) {
        parent::__construct($classInfoArr);
        $this->name = $commandName;
        $this->args = $argsArr;
        $this->desc = $commandDesc;
        
        $this->addUseStatement([
            'webfiori\\framework\\cli\\CLICommand'
        ]);
    }
    
    private function _writeConstructor() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){'
        ], 1);

        if (count($this->args) > 0) {
            $this->append(["parent::__construct('$this->name', ["], 2);

            foreach ($this->args as $argArr) {
                $this->append("'".$argArr['name']."' => [", 3);

                if (strlen($argArr['description']) != 0) {
                    $this->append("'description' => '".str_replace("'", "\'", $argArr['description'])."',", 4);
                }
                $this->append("'optional' => ".($argArr['optional'] === true ? 'true' : 'false').",", 4);

                if (count($argArr['values']) != 0) {
                    $writer->append("'values' => [", 4);

                    foreach ($argArr['values'] as $val) {
                        $this->append("'".str_replace("'", "\'", $val)."',", 5);
                    }
                    $this->append("]", 4);
                }
                $this->append("],", 3);
            }
            $this->append("], '".str_replace("'", "\'", $this->desc)."');", 2);
        } else {
            $this->append("parent::__construct('$this->name', '".str_replace("'", "\'", $this->desc)."');", 2);
        }

        $this->append('}', 1);
    }

    public function writeClassBody() {
        $this->_writeConstructor();
        
        $this->append([
            '/**',
            ' * Execute the command.',
            ' */',
            'public function exec() {',
        ], 1);
        $this->append([
            '//TODO: Write the code that represents the command.',
            'return 0;',
        ], 2);
        $this->append('}', 1);

        $this->append("}");
        $this->append("return __NAMESPACE__;");
    }

    public function writeClassComment() {
        $topArr = [
            '/**',
            ' * A CLI command  which was created using the command "create".',
            ' *',
            " * The command will have the name '$this->name'."
        ];

        if (count($this->args) != 0) {
            $topArr[] = ' * In addition, the command have the following args:';
            $topArr[] = ' * <ul>';

            foreach ($this->args as $argArr) {
                $topArr[] = " * <li>".$argArr['name']."</li>";
            }
            $topArr[] = ' * </ul>';
        }
        $topArr[] = ' */';
        $this->append($topArr);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends CLICommand {');
    }

}
