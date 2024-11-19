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
 * A class that contains the implementation of the web service 'get-all-super-user-xs'.
 * This service has the following parameters:
 * <ul>
 * <li><b>page</b>: Data type: integer.</li>
 * <li><b>size</b>: Data type: integer.</li>
 * </ul>
 */
class GetAllSuperUserXsService extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('get-all-super-user-xs');
        $this->setDescription('');
        $this->setRequestMethods([
            RequestMethod::GET,
        ]);
        $this->addParameters([
            'page' => [
                ParamOption::TYPE => ParamType::INT,
                ParamOption::DEFAULT => 1,
                ParamOption::MIN => -9223372036854775808,
            ],
            'size' => [
                ParamOption::TYPE => ParamType::INT,
                ParamOption::DEFAULT => 10,
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
        // TODO: Check if the client is authorized to call the service 'get-all-super-user-xs'.
        // You can ignore this method or remove it.
        //$authHeader = $this->getAuthHeader();
        //$authType = $authHeader['type'];
        //$token = $authHeader['credentials'];
    }
    /**
     * Process the request.
     */
    public function processRequest() {
        $pageNumber = $this->getParamVal('page');
        $pageSize = $this->getParamVal('size');
        $recordsCount = SuperUserXDB::get()->getSuperUserXsCount();
        $data = SuperUserXDB::get()->getSuperUserXs($pageNumber, $pageSize);
        $this->send('application/json', new Json([
            'page' => new Json([
                'pages-count' => ceil($recordsCount/$pageSize),
                'size' => $pageSize,
                'page-number' => $pageNumber,
            ]),
            'data' => $data
        ]));
    }
}
