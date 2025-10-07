<?php

namespace webfiori\framework\middleware;

use Error;
use webfiori\framework\session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
/**
 * A middleware which is used to initialize sessions.
 */
class StartSessionMiddleware extends AbstractMiddleware {
    /**
     * Creates new instance of the class.
     * 
     * By default, the middleware is part of the group 'web'.
     * The priority of the middleware is PHP_INT_MAX.
     */
    public function __construct() {
        parent::__construct('start-session');
        $this->setPriority(PHP_INT_MAX);
        $this->addToGroup('web');
    }
    public function after(Request $request, Response $response) {
        try {
            $sessionsCookiesHeaders = SessionsManager::getCookiesHeaders();

            foreach ($sessionsCookiesHeaders as $headerVal) {
                Response::addHeader('set-cookie', $headerVal);
            }
        } catch (Error $exc) {
        }
    }

    public function afterSend(Request $request, Response $response) {
        SessionsManager::validateStorage();
    }

    public function before(Request $request, Response $response) {
        SessionsManager::start('wf-session');
    }
}
