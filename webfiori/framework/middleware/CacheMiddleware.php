<?php

namespace webfiori\framework\middleware;

use webfiori\cache\Cache;
use webfiori\framework\router\Router;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\http\Response;


class CacheMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('cache');
        $this->setPriority(50);
        $this->addToGroups(['web', 'api']);
    }
    public function after(Request $request, Response $response) {
        $uriObj = Router::getRouteUri();
        
        if ($uriObj !== null) {
            $request->getRequestedURI();
            
            //Following steps are used to make cached response unique per user.
            $session = SessionsManager::getActiveSession();
            if ($session !== null) {
                $key .= $session->getId();
            } 
            $authHeader = $request->getAuthHeader();
            $key .= $authHeader['scheme'].$authHeader['credentials'];
            //End
            
            Cache::get($key, function (Response $response) {
                return [
                    'headers' => $response->getHeaders(),
                    'http-code' => $response->getCode(),
                    'body' => $response->getBody()
                ];
            }, $uriObj->getCacheDuration(), [$response]);
        }
    }

    public function afterSend(Request $request, Response $response) {
        
    }

    public function before(Request $request, Response $response) {
        
    }
}
