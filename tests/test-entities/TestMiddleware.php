<?php

use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\http\Request;
use webfiori\http\Response;

/**
 * Description of TestMiddleware
 *
 * @author Ibrahim
 */
class TestMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('Super MD');
        $this->addToGroup('global');
    }
    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}
