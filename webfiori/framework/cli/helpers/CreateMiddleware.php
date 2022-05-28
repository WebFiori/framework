<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\helpers\CreateClassHelper;
use webfiori\framework\cli\writers\MiddlewareClassWriter;
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
        parent::__construct($command, new MiddlewareClassWriter());
        $this->setClassInfo(APP_DIR_NAME.'\\middleware', 'Middleware');
        
        $middlewareName = $this->_getMiddlewareName();
        $priority = $this->_getMiddlewareProprity();

        if ($this->confirm('Would you like to add the middleware to a group?', false)) {
            $this->_getGroups();
        }
        
        $this->getWriter()->setMiddlewareName($middlewareName);
        $this->getWriter()->setMiddlewarePriority($priority);
        $this->writeClass();
    }
    private function _getGroups() {
        $groupsArr = [];
        $addToMore = true;

        while ($addToMore) {
            $groupName = $this->getInput('Enter group name:');

            if (strlen($groupName) > 0) {
                $this->getWriter()->addGroup($groupName);
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
}
