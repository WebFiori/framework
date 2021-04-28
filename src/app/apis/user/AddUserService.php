<?php

namespace app\apis;

use webfiori\http\AbstractWebService;
use app\database\MainDatabase;
use webfiori\framework\User;
/**
 * A class that contains the implementation of the web service 'add-user'.
 * This service has the following parameters:
 * <ul>
 * <li><b>username</b>: Data type: string.</li>
 * <li><b>email</b>: Data type: email.</li>
 * <li><b>password</b>: Data type: string.</li>
 * </ul>
 */
class AddUserService extends AbstractWebService {
    /**
     * Creates new instance of the class.
     */
    public function __construct(){
        parent::__construct('add-user');
        $this->addRequestMethod('POST');
        $this->addParameter([
            'name' => 'username',
            'type' => 'string',
        ]);
        $this->addParameter([
            'name' => 'email',
            'type' => 'email',
        ]);
        $this->addParameter([
            'name' => 'password',
            'type' => 'string',
        ]);
    }
    /**
     * Checks if the client is authorized to call a service or not.
     *
     * @return boolean If the client is authorized, the method will return true.
     */
    public function isAuthorized() {
        // TODO: Check if the client is authorized to call the service 'add-user'.
        // You can ignore this method or remove it.
    }
    /**
     * Process the request.
     */
    public function processRequest() {
        $user = new User();
        $user->setEmail($this->getParamVal('email'));
        $user->setUserName($this->getParamVal('username'));
        $user->setPassword($this->getParamVal('password'));
        $op = new MainDatabase();
        $op->addUser($user);
        $this->sendResponse('added');
    }
}
