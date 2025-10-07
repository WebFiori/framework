<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\cli\helpers;

use WebFiori\Cli\InputValidator;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\MiddlewareClassWriter;
/**
 * A helper class that works with the create command to create a middleware.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class CreateMiddleware extends CreateClassHelper {
    /**
     * @var MiddlewareClassWriter
     */
    private $mdWriter;
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new MiddlewareClassWriter());
        $this->mdWriter = $this->getWriter();
    }
    public function readClassInfo() {
        $this->setClassInfo(APP_DIR.'\\middleware', 'Middleware');

        $middlewareName = $this->getMiddlewareName();
        $priority = $this->getCommand()->readInteger('Enter middleware priority:', 0);

        if ($this->confirm('Would you like to add the middleware to a group?', false)) {
            $this->getGroups();
        }

        $this->mdWriter->setMiddlewareName($middlewareName);
        $this->mdWriter->setMiddlewarePriority($priority);
        $this->writeClass();
    }
    private function getGroups() {
        $addToMore = true;

        while ($addToMore) {
            $groupName = $this->getInput('Enter group name:');

            if (strlen($groupName) > 0) {
                $this->mdWriter->addGroup($groupName);
            }
            $addToMore = $this->confirm('Would you like to add the middleware to another group?', false);
        }
    }
    private function getMiddlewareName() : string {
        return $this->getInput('Enter a name for the middleware:', null, new InputValidator(function ($val)
        {
            if (strlen(trim($val)) > 0) {
                return true;
            }

            return false;
        }));
    }
}
