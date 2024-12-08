<?php

namespace webfiori\framework\middleware;

use Error;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\http\Response;

class StartSessionMiddleware extends AbstractMiddleware {

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
