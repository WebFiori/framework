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
namespace webfiori\framework\writers;

use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\framework\session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
/**
 * A class which is used to write middleware classes.
 *
 * @author Ibrahim
 */
class MiddlewareClassWriter extends ClassWriter {
    private $groups;
    private $name;
    private $priority;
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
     * provided, the constant ROOT_PATH is used. </li>
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
    public function __construct($middlewareName = '', $priority = 0, array $groupsArr = []) {
        parent::__construct('NewMiddleware', APP_PATH.'middleware', APP_DIR.'\\middleware');
        $this->setSuffix('Middleware');
        $this->addUseStatement([
                AbstractMiddleware::class,
                SessionsManager::class,
                Request::class,
                Response::class,
        ]);
        $this->priority = $priority;

        if (!$this->setMiddlewareName($middlewareName)) {
            $this->setMiddlewareName('New Middleware');
        }
        $this->groups = $groupsArr;
    }
    /**
     * Adds the middleware to a group.
     *
     * @param string $gname The name of the group that the middleware will
     * be added to.
     */
    public function addGroup(string $gname) {
        $trimmed = trim($gname);

        if (strlen($trimmed) > 0) {
            $this->groups[] = $gname;
        }
    }
    /**
     * Returns an array that contains the names of all groups at which
     * the middleware is added to.
     *
     * @return array
     */
    public function getGroups() : array {
        return $this->groups;
    }
    /**
     * Returns a string that represents the name of the middleware.
     *
     * @return string A string that represents the name of the middleware.
     * Default return value is 'New Middleware'.
     */
    public function getMiddlewareName() : string {
        return $this->name;
    }
    /**
     * Returns a number that represents the priority of the middleware.
     *
     * @return int A number that represents the priority of the middleware.
     */
    public function getMiddlewarePriority() : int {
        return $this->priority;
    }
    /**
     * Sets the name of the middleware.
     *
     * @param string $mdName
     *
     * @return bool If set, the method will return true. False otherwise.
     */
    public function setMiddlewareName(string $mdName) : bool {
        $trimmed = trim($mdName);

        if (strlen($trimmed) > 0) {
            $this->name = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Sets the priority of the middleware.
     *
     * @param int $pr An integer that represents the priority.
     */
    public function setMiddlewarePriority(int $pr) {
        $this->priority = $pr;
    }

    public function writeClassBody() {
        $this->writeConstructor();
        $this->append([
            '/**',
            ' * Execute a set of instructions before accessing the application.',
            ' */',
            $this->f('before', ['request' => 'Request', 'response' => 'Response']),

        ], 1);
        $this->append('//TODO: Implement the action to perform before processing the request.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after processing the request and before sending back the response.',
            ' */',
            $this->f('after', ['request' => 'Request', 'response' => 'Response']),
        ], 1);
        $this->append('//TODO: Implement the action to perform after processing the request.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after sending the response.',
            ' */',
            $this->f('afterSend', ['request' => 'Request', 'response' => 'Response']),
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
    private function writeConstructor() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            $this->f('__construct'),

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
}
