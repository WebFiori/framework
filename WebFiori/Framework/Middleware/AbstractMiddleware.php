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
namespace WebFiori\Framework\Middleware;

use WebFiori\Collections\Comparable;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * An abstract class that can be used to implement custom middleware.
 *
 * Every middleware the developer write must be placed in the folder 'app/middleware'
 * of the framework in order for the framework to auto-register the middleware.
 * If the middleware is placed in another place, then the developer must register
 * it manually using the method MiddlewareManager::register() before adding
 * the middleware to any route.
 *
 * @author Ibrahim
 *
 * @version 1.0
 *
 * @since 2.0.0
 */
abstract class AbstractMiddleware implements Comparable {
    /**
     * An array that contains the names of the groups that the
     * middleware belongs to.
     *
     * @var array
     *
     * @since 1.0
     */
    private $groups;
    /**
     *
     * @var string
     *
     * @since 1.0
     */
    private $name;
    /**
     *
     * @var int
     *
     * @since 1.0
     */
    private $priority;
    /**
     * Creates new instance of the class.
     *
     * @param string $name A unique name for the middleware. The name will be
     * used later to assign the middleware to specific routes.
     *
     * @since 1.0
     */
    public function __construct(string $name) {
        $this->groups = [];

        if (!$this->setName($name)) {
            $this->setName('middleware');
        }
        $this->setPriority(1);
    }
    /**
     * Adds the middleware to specific group.
     *
     * Group name can be used to apply multiple middlewares to specific
     * route.
     *
     * @param string $groupName The name of the group.
     *
     * @since 1.0
     */
    public function addToGroup(string $groupName) {
        $trimmed = trim($groupName);

        if (strlen($trimmed) > 0 && !in_array($trimmed, $this->getGroups())) {
            $this->groups[] = $trimmed;
        }
    }
    /**
     * Adds the middleware to more than one group.
     *
     * @param array $groupsArr An array that contains the names of the groups.
     *
     * @since 1.0
     */
    public function addToGroups(array $groupsArr) {
        foreach ($groupsArr as $groupName) {
            $this->addToGroup($groupName);
        }
    }
    /**
     * Perform an action after accessing application level and before sending
     * the request.
     *
     * This method can be used to add extra payload to the response or even
     * change it totally before sending back the response.
     *
     * @param Request $request An object that represents the request that
     * will be received.
     *
     * @param Response $response An object that represents the response
     * that will be sent back.
     *
     * @since 1.0
     */
    public abstract function after(Request $request, Response $response);
    /**
     * Perform an action after sending the response and before terminating the
     * application.
     *
     * @param Request $request An object that represents the request that
     * will be received.
     *
     * @param Response $response An object that represents the response
     * that will be sent back.
     *
     * @since 1.0
     */
    public abstract function afterSend(Request $request, Response $response);
    /**
     * Perform an action before accessing application level.
     *
     * This method will get executed before routing happens. One use case of
     * this method is to use it to check if the user is authorized to access
     * the system or not. If he is not, then send back a redirect header that
     * takes the user to login screen or just send a 401 response code with
     * a message.
     *
     * @param Request $request An object that represents the request that
     * will be received.
     *
     * @param Response $response An object that represents the response
     * that will be sent back.
     *
     * @since 1.0
     */
    public abstract function before(Request $request, Response $response);

    /**
     * Compare the priority of the middleware with another one.
     *
     * The main aim of this method is to prioritize which middleware will be reached
     * first. First the method checks the priority of the middleware. If the
     * two have same priority, it will use the name of the middleware.
     *
     * @param AbstractMiddleware $other Another middleware to compare with.
     *
     * @return int
     *
     * @since 1.0
     */
    public function compare($other) : int {
        if ($this->priority == $other->priority) {
            return strcmp($other->getName(), $this->getName());
        }

        return $this->getPriority() - $other->getPriority();
    }
    /**
     * Returns an array that holds the names of the groups that the middleware
     * belongs to.
     *
     * @return array An array that holds the names of the groups that the middleware
     * belongs to.
     *
     * @since 1.0
     */
    public function getGroups() : array {
        return $this->groups;
    }
    /**
     * Returns the name of the middleware.
     *
     * @return string the name of the middleware.
     *
     * @since 1.0
     */
    public function getName() : string {
        return $this->name;
    }
    /**
     * Returns the priority of the middleware.
     *
     * @return int Priority of the middleware.
     *
     * @since 1.0
     */
    public function getPriority() : int {
        return $this->priority;
    }
    /**
     * Sets the name of the middleware.
     *
     * The name of the middleware is used to assign it to a route. For this reason,
     * each middleware must have a unique name.
     *
     * @param string $name The name of the middleware.
     *
     * @return boolean If the name is set, the method will return true. If not
     * set, the method will return false.
     *
     * @since 1.0
     */
    public function setName(string $name): bool {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0) {
            $this->name = $name;

            return true;
        }

        return false;
    }
    /**
     * Sets the priority of the middleware.
     *
     * Priority of middleware is used to specify which middleware will be reached
     * first. The higher the priority, the sooner the middleware will be reached.
     * For example, a middleware with priority 100 will be reached before a
     * middleware with priority 99.
     *
     * @param int $priority Middleware priority.
     *
     * @since 1.0
     */
    public function setPriority(int $priority) {
        $this->priority = $priority;
    }
}
