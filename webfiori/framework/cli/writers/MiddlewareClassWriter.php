<?php
namespace webfiori\framework\cli\writers;

/**
 * A class which is used to write middleware classes.
 *
 * @author Ibrahim
 */
class MiddlewareClassWriter extends ClassWriter {
    private $priority;
    private $name;
    private $groups;
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
     * 
     * @param string $middlewareName The name of the middleware that will be created.
     * 
     * @param int $priority Priority of the middleware. Lower number means higher
     * priority.
     * 
     * @param array $groupsArr An array that holds groups at which the middleware
     * will be added to.
     */
    public function __construct(array $classInfoArr, $middlewareName, $priority, array $groupsArr = []) {
        parent::__construct($classInfoArr);
        $this->addUseStatement([
            "webfiori\\framework\\middleware\\AbstractMiddleware",
            "webfiori\\framework\\SessionsManager",
            "webfiori\\http\\Request",
            "webfiori\\http\\Response",
        ]);
        $this->priority = $priority;
        $this->name = $middlewareName;
        $this->groups = $groupsArr;
    }
    private function _writeConstructor() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){',
            
        ], 1);
        $this->append("parent::__construct('$this->name');", 2);
        $this->append("\$this->setPriority($this->priority);", 2);

        if (count($this->groups) > 0) {
            $this->append('$this->addToGroups([', 2);

            foreach ($this->groups as $gName) {
                $this->append("'$gName',", 3);
            }
            $this->append(']);', 2);
        }
        $this->append('}', 1);
    }

    public function writeClassBody() {
        $this->_writeConstructor();
        $this->append([
            '/**',
            ' * Execute a set of instructions before accessing the application.',
            ' */',
            'public function before() {',
            
        ], 1);
        $this->append('//TODO: Implement the action to perform before processing the request.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after processing the request and before sending back the response.',
            ' */',
            'public function after() {'
        ], 1);
        $this->append('//TODO: Implement the action to perform after processing the request.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after sending the response.',
            ' */',
            'public function afterSend() {'
        ], 1);
        $this->append('//TODO: Implement the action to perform after sending the request.', 2);
        $this->append('}', 1);

        $this->append("}");
    }

    public function writeClassComment() {
        $classTop = [
            '/**',
            ' * A middleware which is created using the command "create".',
            ' *',
            " * The middleware will have the name '$this->name' and ",
            " * Priority $this->priority."
        ];

        if (count($this->groups) != 0) {
            $classTop[] = ' * In addition, the middleware is added to the following groups:';
            $classTop[] = ' * <ul>';

            foreach ($this->groups as $gName) {
                $classTop[] = " * <li>$gName</li>";
            }
            $classTop[] = ' * </ul>';
        }
        $classTop[] = ' */';
        $this->append($classTop);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractMiddleware {');
    }

}
