<?php
namespace app\apis\super;

use webfiori\http\AbstractWebService;
use webfiori\http\ParamType;
use webfiori\http\ParamOption;
use webfiori\http\RequestMethod;
use app\database\super\SuperUserDB;
use app\entity\super\SuperUser;
use webfiori\json\Json;


/**
 * A class that contains the implementation of the web service 'get-super-user'.
 * This service has the following parameters:
 * <ul>
 * <li><b>id</b>: Data type: integer.</li>
 * </ul>
 */
class GetSuperUserService extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('get-super-user');
        $this->setDescription('');
        $this->setRequestMethods([
            RequestMethod::GET,
        ]);
        $this->addParameters([
            'id' => [
                ParamOption::TYPE => ParamType::INT,
                ParamOption::MIN => -9223372036854775808,
            ],
        ]);
    }
    /**
     * Checks if the client is authorized to call a service or not.
     *
     * @return boolean If the client is authorized, the method will return true.
     */
    public function isAuthorized() {
        // TODO: Check if the client is authorized to call the service 'get-super-user'.
        // You can ignore this method or remove it.
        //$authHeader = $this->getAuthHeader();
        //$authType = $authHeader['type'];
        //$token = $authHeader['credentials'];
    }
    /**
     * Process the request.
     */
    public function processRequest() {
        $entity = SuperUserDB::get()->getSuperUser($this->getParamVal('id'));
        $this->send('application/json', new Json([
            'data' => $entity
        ]));
    }
}
