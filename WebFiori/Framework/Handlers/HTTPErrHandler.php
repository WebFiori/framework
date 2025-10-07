<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2022 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Handlers;

use WebFiori\Error\AbstractHandler;
use WebFiori\Framework\App;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\UI\ServerErrPage\ServerErrPage;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
/**
 * Errors and exceptions handler which is used to handle errors in case of
 * HTTP request.
 *
 * The priority of the handler
 * is set to 0 which indicates that it will be executed last.
 *
 * @author Ibrahim
 */
class HTTPErrHandler extends AbstractHandler {
    /**
     * Creates new instance of the class.
     *
     * This method will set the name of the handler to 'HTTP Errors Handler'.
     */
    public function __construct() {
        parent::__construct();
        $this->setName('HTTP Errors Handler');
    }
    /**
     * Handles the exception.
     *
     * The handler will simply show a server error page with error details
     * if the constant WF_VERBOSE is set to true. If not, it will show
     * general server error message.
     */
    public function handle() {
        $exceptionView = new ServerErrPage($this);
        Response::clear();
        $exceptionView->render();

        if (!Response::isSent()) {
            Response::send();
        }
    }
    /**
     * Checks if the handler is active or not.
     *
     * The handler will be active only if route type is Router::VIEW_ROUTE.
     *
     * @return bool True if active. false otherwise.
     */
    public function isActive(): bool {
        if (App::getClassStatus() == App::STATUS_INITIALIZING) {
            //We already have the API error handler active. So, disable it.
            return false;
        }
        $routeUri = Router::getUriObjByURL(Request::getRequestedURI());

        if ($routeUri !== null) {
            $routeType = $routeUri->getType();
        } else {
            $routeType = Router::VIEW_ROUTE;
        }

        return !($routeType == Router::API_ROUTE || defined('API_CALL'));
    }
    /**
     * Checks if the handler is a shutdown handler or not.
     *
     * @return bool The method will always return true.
     */
    public function isShutdownHandler(): bool {
        return true;
    }
}
