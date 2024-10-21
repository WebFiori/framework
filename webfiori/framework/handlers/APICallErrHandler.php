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
namespace webfiori\framework\handlers;

use webfiori\error\AbstractHandler;
use webfiori\framework\App;
use webfiori\framework\router\Router;
use webfiori\http\Request;
use webfiori\http\Response;
use webfiori\json\Json;
/**
 * Exceptions handler which is used to handle exceptions in case of API call.
 *
 * This handler is also used to handle startup exceptions. The priority of the handler
 * is set to 0 which indicates that it will be executed last.
 *
 * @author Ibrahim
 */
class APICallErrHandler extends AbstractHandler {
    /**
     * Creates new instance of the class.
     *
     * This method will set the name of the handler to 'API Call Errors Handler.
     */
    public function __construct() {
        parent::__construct();
        $this->setName('API Call Errors Handler');
    }
    /**
     * Handles the exception
     */
    public function handle() {
        if (defined('WF_VERBOSE') && WF_VERBOSE === true) {
            $j = new Json([
                'message' => '500 - Server Error: Uncaught Exception.',
                'type' => 'error',
                'exception-class' => get_class($this->getException()),
                'exception-message' => $this->getMessage(),
                'exception-code' => $this->getException()->getCode(),
                'at-class' => $this->getClass(),
                'line' => $this->getLine()
            ]);
            $stackTrace = new Json();
            $index = 0;

            foreach ($this->getTrace() as $traceEntry) {
                $stackTrace->add('#'.$index,$traceEntry->getClass().'::'.$traceEntry->getMethod().'() (Line '.$traceEntry->getLine().')');
                $index++;
            }
            $j->add('stack-trace',$stackTrace);
        } else {
            $j = new Json([
                'message' => '500 - General Server Error.',
                'details' => $this->getMessage(),
                'type' => 'error',
            ]);
        }

        if (!Response::isSent()) {
            Response::clear();
            Response::setCode(500);
            Response::addHeader('content-type', 'application/json');
            Response::write($j);
            Response::send();
        }
    }
    /**
     * Checks if the handler is active or not.
     *
     * The handler will be active in following cases:
     * <ul>
     * <li>Class WebfioriApp is in initialization stage.</li>
     * <li>Route type is Router::API_ROUTE.</li>
     * <li>The constant API_CALL is defined and set to true.</li>
     * </ul>
     *
     * @return bool True if active. false otherwise.
     */
    public function isActive(): bool {
        if (App::getClassStatus() == App::STATUS_INITIALIZING) {
            return true;
        }

        $routeUri = Router::getUriObjByURL(Request::getRequestedURI());

        if ($routeUri !== null) {
            $routeType = $routeUri->getType();
        } else {
            $routeType = Router::VIEW_ROUTE;
        }

        return $routeType == Router::API_ROUTE || (defined('API_CALL') && API_CALL === true);
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
