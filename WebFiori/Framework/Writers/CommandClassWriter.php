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

/**
 * A class which is used to write CLI Commands classes.
 *
 * @author Ibrahim
 */
class CommandClassWriter extends ClassWriter {
    private $args;
    private $desc;
    private $name;
    /**
     * Creates new instance of the class.
     *
     * @param array $classInfoArr An associative array that contains the information
     * of the class that will be created. The array must have the following indices:
     * <ul>
     * <li><b>name</b>: The name of the class that will be created. If not provided, the
     * string 'NewClass' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided,
     * the namespace 'WebFiori' is used.</li>
     * <li><b>path</b>: The location at which the class will be created on. If not
     * provided, the constant ROOT_PATH is used. </li>
     *
     * </ul>
     * @param string $commandName A string that represents the name of the command.
     *
     * @param string $commandDesc A string that represents the description of the command.
     *
     * @param array $argsArr An associative array that holds the names of the argument
     * the command will have.
     */
    public function __construct() {
        parent::__construct('NewCommand', APP_PATH.'Commands', APP_DIR.'\\Commands');
        $this->name = 'new-command';
        $this->args = [];
        $this->desc = '';
        $this->setSuffix('Command');
        $this->setNamespace(APP_DIR.'\\Commands');
        $this->addUseStatement([
            'WebFiori\\Cli\\Command'
        ]);
    }
    /**
     * Returns an array that represents the arguments of the command.
     *
     * @return array An array that represents the arguments of the command.
     * Default is empty array.
     */
    public function getArgs() : array {
        return $this->args;
    }
    /**
     * Returns a string that represents the name of the command.
     *
     * @return string A string that represents the description of the command.
     * Default is 'new-command'.
     */
    public function getCommandName() : string {
        return $this->name;
    }
    /**
     * Returns a string that represents the description of the command.
     *
     * @return string A string that represents the description of the command.
     * Default is empty string.
     */
    public function getDescription() : string {
        return $this->desc;
    }
    /**
     * Sets the array that will represents the arguments of the command.
     *
     * @param array $argsArr
     */
    public function setArgs(array $argsArr) {
        $this->args = $argsArr;
    }
    /**
     * Sets the description of the command.
     *
     * @param string $desc
     */
    public function setCommandDescription(string $desc) {
        $trimmed = trim($desc);

        if (strlen($trimmed) == 0) {
            return;
        }
        $this->desc = $trimmed;
    }
    /**
     * Sets the name of the command.
     *
     * The name of the command is simply the string that will be used to
     * run it.
     *
     * @param string $name
     *
     * @return boolean If the name is successfully set, the method will return
     * true. Other than that, the method will return false.
     */
    public function setCommandName(string $name) : bool {
        $trimmed = trim($name);

        if (strlen($trimmed) == 0) {
            return false;
        }

        if (!strpos($trimmed, ' ')) {
            $this->name = $trimmed;

            return true;
        }

        return false;
    }

    public function writeClassBody() {
        $this->writeConstructor();

        $this->append([
            '/**',
            ' * Execute the command.',
            ' */'
        ], 1);
        $this->f('exec', [], 'int');
        $this->append([
            '//TODO: Write the logic of the command.',
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

            foreach ($this->args as $argObj) {
                $topArr[] = " * <li>".$argObj->getName()."</li>";
            }
            $topArr[] = ' * </ul>';
        }
        $topArr[] = ' */';
        $this->append($topArr);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends Command {');
    }
    protected function writeConstructor(array $params = [],
        $body = '',
        string $description = 'Creates new instance of the class.',
        int $indent = 1) {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */'
        ], 1);
        $this->f('__construct');

        if (count($this->args) > 0) {
            $this->append(["parent::__construct('$this->name', ["], 2);

            foreach ($this->args as $argObj) {
                $this->append("'".$argObj->getName()."' => [", 3);

                if (strlen($argObj->getDescription()) != 0) {
                    $this->append("'description' => '".str_replace("'", "\'", $argObj->getDescription())."',", 4);
                }
                $this->append("'optional' => ".($argObj->isOptional() ? 'true' : 'false').",", 4);

                if (count($argObj->getAllowedValues()) != 0) {
                    $this->append("'values' => [", 4);

                    foreach ($argObj->getAllowedValues() as $val) {
                        $this->append("'".str_replace("'", "\'", $val)."',", 5);
                    }
                    $this->append("]", 4);
                }
                $this->append("],", 3);
            }
            $this->append("], '".str_replace("'", "\'", $this->desc)."');", 2);
        } else {
            $this->append("parent::__construct('$this->name', [], '".str_replace("'", "\'", $this->desc)."');", 2);
        }

        $this->append('}', 1);
    }
}
