<?php
namespace app\apis\super;

use webfiori\http\AbstractWebService;
use webfiori\http\ParamType;
use webfiori\http\ParamOption;
use webfiori\http\RequestMethod;
use app\database\super\SuperUserXDB;
use app\entity\super\SuperUserX;
use webfiori\json\Json;


/**
 * A class that contains the implementation of the web service 'delete-super-user-x'.
 */
class DeleteSuperUserXService extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('delete-super-user-x');
        $this->setDescription('');
        $this->setRequestMethods([
            RequestMethod::DELETE,
        ]);
    }
    /**
     * Checks if the client is authorized to call a service or not.
     *
     * @return boolean If the client is authorized, the method will return true.
     */
    public function isAuthorized() {
        // TODO: Check if the client is authorized to call the service 'delete-super-user-x'.
        // You can ignore this method or remove it.
        //$authHeader = $this->getAuthHeader();
        //$authType = $authHeader['type'];
        //$token = $authHeader['credentials'];
    }
    /**
     * Process the request.
     */
    public function processRequest() {
        // TODO: process the request for the service 'delete-super-user-x'.
        $this->getManager()->serviceNotImplemented();
    }
}
