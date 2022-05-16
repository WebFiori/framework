<?php
namespace webfiori\framework\handlers;

use webfiori\error\AbstractHandler;
use webfiori\framework\ui\ServerErrView;
use webfiori\framework\Util;
use webfiori\framework\router\Router;
use webfiori\framework\i18n\Language;
use webfiori\http\Response;
/**
 * Description of HTTPExceptionHandler
 *
 * @author Ibrahim
 */
class HTTPErrHandler  extends AbstractHandler {
    public function __construct() {
        parent::__construct();
        $this->setName('HTTP Errors Handler');
    }
    public function handle() {
        $exceptionView = new ServerErrView($this);
        Response::clear();
        $exceptionView->render();
        
        if (!Response::isSent()) {
            Response::send();
        }
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
