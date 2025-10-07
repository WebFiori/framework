<?php
namespace Tests\Apis\Multiple;

use WebFiori\Http\AbstractWebService;
use WebFiori\Http\RequestMethod;


/**
 * A class that contains the implementation of the web service 'say-hi-service-2'.
 */
class WebService01 extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('say-hi-service-2');
        $this->setDescription('');
        $this->setRequestMethods([
                RequestMethod::HEAD,
        ]);
        
    }
    /**
     * Checks if the client is authorized to call a service or not.
     *
     * @return boolean If the client is authorized, the method will return true.
     */
    public function isAuthorized() {
        // TODO: Check if the client is authorized to call the service 'say-hi-service'.
        // You can ignore this method or remove it.
        //$authHeader = $this->getAuthHeader();
        //$authType = $authHeader['type'];
        //$token = $authHeader['credentials'];
    }
    /**
     * Process the request.
     */
    public function processRequest() {
        // TODO: process the request for the service 'say-hi-service'.
        $this->getManager()->serviceNotImplemented();
    }
}
