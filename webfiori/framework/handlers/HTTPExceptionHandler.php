<?php
namespace webfiori\framework\handlers;

use webfiori\error\AbstractHandler;
use webfiori\framework\ui\ServerErrView;
/**
 * Description of HTTPExceptionHandler
 *
 * @author Ibrahim
 */
class HTTPExceptionHandler  extends AbstractHandler {
    
    public function handle() {
        $exceptionView = new ServerErrView($this);
        $exceptionView->show(500);
    }

    public function isActive(): bool {
        $routeUri = Router::getUriObjByURL(Util::getRequestedURL());

        if ($routeUri !== null) {
            $routeType = $routeUri->getType();
        } else {
            $routeType = Router::VIEW_ROUTE;
        }
        
        return !($routeType == Router::API_ROUTE || defined('API_CALL'));
    }

    public function isShutdownHandler(): bool {
        return true;
    }

}
