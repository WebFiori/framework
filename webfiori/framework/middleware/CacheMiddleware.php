<?php

namespace webfiori\framework\middleware;

use webfiori\cache\Cache;
use webfiori\framework\router\Router;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\http\Response;


class CacheMiddleware extends AbstractMiddleware {
    private $fromCache;
    /**
     * Creates new instance of the class.
     * 
     * By default, the middleware is part of the group 'web' and 'api'.
     * The priority of the middleware is 50.
     */
    public function __construct() {
        parent::__construct('cache');
        $this->setPriority(50);
        $this->addToGroups(['web']);
        $this->fromCache = false;
    }
    /**
     * Checks if the response is loaded from the cache or caching must be performed.
     *
     * @param Request $request An object that represents the request that
     * will be received.
     *
     * @param Response $response An object that represents the response
     * that will be sent back.
     *
     */
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
    /**
     * This method will do nothing.
     *
     * @param Request $request An object that represents the request that
     * will be received.
     *
     * @param Response $response An object that represents the response
     * that will be sent back.
     */
    public function afterSend(Request $request, Response $response) {
        
    }
    /**
     * Attempt to load an item from the cache and send the response back if
     * cached.
     *
     * @param Request $request An object that represents the request that
     * will be received.
     *
     * @param Response $response An object that represents the response
     * that will be sent back.
     */
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
    /**
     * Creates the key of cache item.
     * 
     * This method will attempt to use 3 items to create a unique key. The items include:
     * <ul>
     * <li>Requested URI</li>
     * <li>Session ID (if applicable)</li>
     * <li>Authorization header (if applicable)</li>
     * </ul>
     * 
     * @return string
     */
    public function getKey() : string {
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
