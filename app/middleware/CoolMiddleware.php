<?php
namespace app\middleware;

use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\http\Request;
use webfiori\http\Response;
/**
 * A middleware which is created using the command "create".
 *
 * The middleware will have the name 'Super Middleware' and 
 * Priority 100.
 * In addition, the middleware is added to the following groups:
 * <ul>
 * <li>one-group</li>
 * <li>two-group</li>
 * <li>global</li>
 * </ul>
 */
class CoolMiddleware extends AbstractMiddleware {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('Super Cool Middleware');
        $this->setPriority(100);
        $this->addToGroups([
            'one-group',
            'two-group',
        ]);
    }
    /**
     * Execute a set of instructions before accessing the application.
     */
    public function before(Request $request, Response $response) {
        //TODO: Implement the action to perform before processing the request.
    }
    /**
     * Execute a set of instructions after processing the request and before sending back the response.
     */
    public function after(Request $request, Response $response) {
        //TODO: Implement the action to perform after processing the request.
    }
    /**
     * Execute a set of instructions after sending the response.
     */
    public function afterSend(Request $request, Response $response) {
        //TODO: Implement the action to perform after sending the request.
    }
}
