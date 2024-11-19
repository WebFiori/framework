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
 * A class that contains the implementation of the web service 'update-is-happy-of-super-user-x'.
 * This service has the following parameters:
 * <ul>
 * <li><b>is-happy</b>: Data type: boolean.</li>
 * </ul>
 */
class UpdateIsHappyOfSuperUserXService extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('update-is-happy-of-super-user-x');
        $this->setDescription('');
        $this->setRequestMethods([
            RequestMethod::POST,
        ]);
        $this->addParameters([
            'is-happy' => [
                ParamOption::TYPE => ParamType::BOOL,
            ],
        ]);
    }
    /**
     * Checks if the client is authorized to call a service or not.
     *
     * @return boolean If the client is authorized, the method will return true.
     */
    public function isAuthorized() {
        // TODO: Check if the client is authorized to call the service 'update-is-happy-of-super-user-x'.
        // You can ignore this method or remove it.
        //$authHeader = $this->getAuthHeader();
        //$authType = $authHeader['type'];
        //$token = $authHeader['credentials'];
    }
    /**
     * Process the request.
     */
    public function processRequest() {
        // TODO: process the request for the service 'update-is-happy-of-super-user-x'.
        $this->getManager()->serviceNotImplemented();
    }
}
