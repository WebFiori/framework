<?php
namespace webfiori\framework\cli\writers;

/**
 * A class which is used to write middleware classes.
 *
 * @author Ibrahim
 */
class MiddlewareClassWriter extends ClassWriter {
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
        
        $this->appendTop();
        $classTop = [
            "use webfiori\\framework\\middleware\\AbstractMiddleware;",
            "use webfiori\\framework\\SessionsManager;",
            "use webfiori\\http\\Request;",
            "use webfiori\\http\\Response;\n",
            '/**',
            ' * A middleware which is created using the command "create".',
            ' *',
            " * The middleware will have the name '$middlewareName' and ",
            " * Priority $priority."
        ];

        if (count($groupsArr) != 0) {
            $classTop[] = ' * In addition, the middleware is added to the following groups:';
            $classTop[] = ' * <ul>';

            foreach ($groupsArr as $gName) {
                $classTop[] = " * <li>$gName</li>";
            }
            $classTop[] = ' * </ul>';
        }
        $classTop[] = ' */';
        $classTop[] = 'class '.$this->getName().' extends AbstractMiddleware {';
        $this->append($classTop);

        $this->_writeConstructor($middlewareName, $priority, $groupsArr);

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
    private function _writeConstructor($name, $priority, array $groups) {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){',
            
        ], 1);
        $this->append("parent::__construct('$name');", 2);
        $this->append("\$this->setPriority($priority);", 2);

        if (count($groups) > 0) {
            $this->append('$this->addToGroups([', 2);

            foreach ($groups as $gName) {
                $this->append("'$gName',", 3);
            }
            $this->append(']);', 2);
        }
        $this->append('}', 1);
    }
}
