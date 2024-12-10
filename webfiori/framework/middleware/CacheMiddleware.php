<?php

namespace webfiori\framework\middleware;

use webfiori\cache\Cache;
use webfiori\framework\router\Router;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\http\Response;


class CacheMiddleware extends AbstractMiddleware {
    private $fromCache;
    public function __construct() {
        parent::__construct('cache');
        $this->setPriority(50);
        $this->addToGroups(['web', 'api']);
        $this->fromCache = false;
    }
    public function after(Request $request, Response $response) {
        
        if (!$this->fromCache) {
            $uriObj = Router::getRouteUri();
        
            if ($uriObj !== null) {
                $key = $this->getKey();
                $data = [
                    'headers' => $response->getHeaders(),
                    'http-code' => $response->getCode(),
                    'body' => $response->getBody()
                ];
                Cache::set($key, $data, $uriObj->getCacheDuration());
            }
        }
    }

    public function afterSend(Request $request, Response $response) {
        
    }

    public function before(Request $request, Response $response) {
        $key = $this->getKey();
        $data = Cache::get($key);
        
        if ($data !== null) {
            $this->fromCache = true;
            $response->write($data['body']);
            $response->setCode($data['http-code']);
            foreach ($data['headers'] as $headerObj) {
                $response->addHeader($headerObj->getName(), $headerObj->getValue());
            }
            $response->send();
        }
    }
    private function getKey() {
        $key = Request::getUri()->getUri(true, true);
            
        //Following steps are used to make cached response unique per user.
        $session = SessionsManager::getActiveSession();
        if ($session !== null) {
            $key .= $session->getId();
        } 
        $authHeader = Request::getAuthHeader();
        if ($authHeader !== null) {
            $key .= $authHeader->getScheme().$authHeader->getCredentials();
        }
        //End
        return $key;
    }
}
