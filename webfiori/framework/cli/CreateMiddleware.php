<?php
namespace webfiori\framework\cli;

/**
 * A helper class that works with the create command to create a middleware.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class CreateMiddleware {
    /**
     *
     * @var CLICommand 
     */
    private $command;
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->command = $command;
        $classInfo = $command->getClassInfo('app\\middleware', 'app'.DS.'middleware');
        $middlewareName = $this->_getMiddlewareName();
        $priority = $this->_getMiddlewareProprity();

        if ($command->confirm('Would you like to add the middleware to a group?', false)) {
            $groupsArr = $this->_getGroups();
        } else {
            $groupsArr = [];
        }
        $writer = new ClassWriter($classInfo);
        $writer->append('<?php');
        $writer->append("namespace ".$writer->getNamespace().";\n");
        $writer->append("use webfiori\\framework\\middleware\\AbstractMiddleware;");
        $writer->append("use webfiori\\framework\\SessionsManager;");
        $writer->append("use webfiori\\http\\Request;");
        $writer->append("use webfiori\\http\\Response;\n");
        $writer->append('/**');
        $writer->append(' * A middleware which is created using the command "create".');
        $writer->append(' *');
        $writer->append(" * The middleware will have the name '$middlewareName' and ");
        $writer->append(" * Priority $priority.");

        if (count($groupsArr) != 0) {
            $writer->append(' * In addition, the middleware is added to the following groups:');
            $writer->append(' * <ul>');

            foreach ($groupsArr as $gName) {
                $writer->append(" * <li>$gName</li>");
            }
            $writer->append(' * </ul>');
        }
        $writer->append(' */');
        $writer->append('class '.$writer->getName().' extends AbstractMiddleware {');

        $this->_writeConstructor($writer, $middlewareName, $priority, $groupsArr);

        $writer->append('/**', 1);
        $writer->append(' * Execute a set of instructions before accessing the application.', 1);
        $writer->append(' */', 1);
        $writer->append('public function before() {', 1);
        $writer->append('//TODO: Implement the action to perform before processing the request.', 2);
        $writer->append('}', 1);

        $writer->append('/**', 1);
        $writer->append(' * Execute a set of instructions after processing the request and before sending back the response.', 1);
        $writer->append(' */', 1);
        $writer->append('public function after() {', 1);
        $writer->append('//TODO: Implement the action to perform after processing the request.', 2);
        $writer->append('}', 1);

        $writer->append('/**', 1);
        $writer->append(' * Execute a set of instructions after sending the response.', 1);
        $writer->append(' */', 1);
        $writer->append('public function afterSend() {', 1);
        $writer->append('//TODO: Implement the action to perform after sending the request.', 2);
        $writer->append('}', 1);

        $writer->append("}");
        $writer->append("return __NAMESPACE__;");

        $writer->writeClass();
        $command->info('New middleware class was created at "'.$writer->getPath().'".');
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
    }
    private function _getGroups() {
        $groupsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $groupName = $this->_getCommand()->getInput('Enter group name:');

            if (strlen($groupName) > 0) {
                $groupsArr[] = $groupName;
            }
            $addToMore = $this->_getCommand()->confirm('Would you like to add the middleware to another group?', false);
        }

        return $groupsArr;
    }
    private function _getMiddlewareName() {
        return $this->_getCommand()->getInput('Enter a name for the middleware:', null, function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        });
    }
    private function _getMiddlewareProprity() {
        return $this->_getCommand()->getInput('Enter middleware priority:', 0, function ($val)
        {
            if ($val >= 0) {
                return true;
            }

            return false;
        });
    }
    /**
     * 
     * @param ClassWriter $writer
     * @param type $name
     * @param type $priority
     * @param array $groups
     */
    private function _writeConstructor($writer, $name, $priority,array $groups) {
        $writer->append('/**', 1);
        $writer->append(' * Creates new instance of the class.', 1);
        $writer->append(' */', 1);
        $writer->append('public function __construct(){', 1);
        $writer->append("parent::__construct('$name');", 2);
        $writer->append("\$this->setPriority($priority);", 2);

        if (count($groups) > 0) {
            $writer->append('$this->addToGroups([', 2);

            foreach ($groups as $gName) {
                $writer->append("'$gName',", 3);
            }
            $writer->append(']);', 2);
        }
        $writer->append('}', 1);
    }
}
