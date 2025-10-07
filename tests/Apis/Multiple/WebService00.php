<?php
namespace Tests\Apis\Multiple;

use WebFiori\Http\AbstractWebService;
use WebFiori\Http\ParamType;
use WebFiori\Http\ParamOption;
use WebFiori\Http\RequestMethod;


/**
 * A class that contains the implementation of the web service 'say-hi-service'.
 * This service has the following parameters:
 * <ul>
 * <li><b>first-name</b>: Data type: string.</li>
 * <li><b>last-name</b>: Data type: string.</li>
 * <li><b>age</b>: Data type: integer.</li>
 * </ul>
 */
class WebService00 extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('say-hi-service');
        $this->setDescription('');
        $this->setRequestMethods([
                RequestMethod::GET,
                RequestMethod::POST,
                RequestMethod::PATCH,
                RequestMethod::HEAD
        ]);
        $this->addParameters([
            'first-name' => [
                ParamOption::TYPE => ParamType::STRING,
            ],
            'last-name' => [
                ParamOption::TYPE => ParamType::STRING,
            ],
            'age' => [
                ParamOption::TYPE => ParamType::INT,
                ParamOption::OPTIONAL => true,
            ],
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
