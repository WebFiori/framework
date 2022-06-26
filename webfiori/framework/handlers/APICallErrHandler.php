<?php
namespace webfiori\framework\handlers;

use webfiori\error\AbstractHandler;
use webfiori\json\Json;
use webfiori\http\Response;
use webfiori\framework\router\Router;
use webfiori\framework\Util;
use webfiori\framework\WebFioriApp;
/**
 * Exceptions handler which is used to handle exceptions in case of API call.
 * 
 * This handler is also used to handle startup exceptions.
 *
 * @author Ibrahim
 */
class APICallErrHandler extends AbstractHandler {
    public function __construct() {
        parent::__construct();
        $this->setName('API Call Errors Handler');
    }
    /**
     * Handles the exception
     */
    public function handle() {
        $j = new Json([
            'message' => '500 - Server Error: Uncaught Exception.',
            'type' => 'error',
            'exception-class' => get_class($this->getException()),
            'exception-message' => $this->getMessage(),
            'exception-code' => $this->getException()->getCode(),
            'line' => $this->getLine()
        ]);
        $stackTrace = new Json();
        $index = 0;
        
        foreach ($this->getTrace() as $traceEntry) {
            $stackTrace->add('#'.$index,$traceEntry->getClass().' (Line '.$traceEntry->getLine().')');
            $index++;
        }
        $j->add('stack-trace',$stackTrace);
        if (!Response::isSent()) {
            Response::clear();
            Response::setCode(500);
            Response::addHeader('content-type', 'application/json');
            Response::write($j);
            Response::send();
        }
    }

    public function isActive(): bool {
        if (WebFioriApp::getClassStatus() == WebFioriApp::STATUS_INITIALIZING) {
            return true;
        }
        $routeUri = Router::getUriObjByURL(Util::getRequestedURL());

        if ($routeUri !== null) {
            $routeType = $routeUri->getType();
        } else {
            $routeType = Router::VIEW_ROUTE;
        }
        
        return $routeType == Router::API_ROUTE || defined('API_CALL');
    }

    public function isShutdownHandler(): bool {
        return true;
    }

}
