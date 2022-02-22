<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\helpers\CreateClassHelper;
/**
 * A helper class that works with the create command to create a middleware.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class CreateMiddleware extends CreateClassHelper {
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command);
        $this->setClassInfo(APP_DIR_NAME.'\\middleware', 'Middleware');
        
        $middlewareName = $this->_getMiddlewareName();
        $priority = $this->_getMiddlewareProprity();

        if ($this->confirm('Would you like to add the middleware to a group?', false)) {
            $groupsArr = $this->_getGroups();
        } else {
            $groupsArr = [];
        }
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
        $classTop[] = 'class '.$this->getWriter()->getName().' extends AbstractMiddleware {';
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

        $this->writeClass();
    }
    private function _getGroups() {
        $groupsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $groupName = $this->getInput('Enter group name:');

            if (strlen($groupName) > 0) {
                $groupsArr[] = $groupName;
            }
            $addToMore = $this->confirm('Would you like to add the middleware to another group?', false);
        }

        return $groupsArr;
    }
    private function _getMiddlewareName() {
        return $this->getInput('Enter a name for the middleware:', null, function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        });
    }
    private function _getMiddlewareProprity() {
        return $this->getInput('Enter middleware priority:', 0, function ($val)
        {
            if ($val >= 0) {
                return true;
            }

            return false;
        });
    }
    private function _writeConstructor($name, $priority,array $groups) {
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
