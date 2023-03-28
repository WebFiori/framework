<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\scheduler\webServices;

use webfiori\framework\scheduler\TasksManager;
use webfiori\framework\session\SessionsManager;
use webfiori\http\AbstractWebService;
use webfiori\http\RequestParameter;
/**
 * An API which is used to authenticate users to access scheduler web interface.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class TasksLoginService extends AbstractWebService {
    public function __construct() {
        parent::__construct('login');
        $this->addRequestMethod('post');
        $this->addParameter(new RequestParameter('password'));
    }
    public function isAuthorized() {
        return true;
    }

    public function processRequest() {
        $schedulerPass = TasksManager::password();

        if ($schedulerPass != 'NO_PASSWORD') {
            $inputHash = hash('sha256', $this->getInputs()['password']);

            if ($inputHash == $schedulerPass) {
                SessionsManager::start('scheduler-session');
                SessionsManager::set('scheduler-login-status', true);
                $this->sendResponse('Success', 'info');
            } else {
                $this->sendResponse('Incorrect password', 'error', 404);
            }
        } else {
            $this->sendResponse('Success', 'info');
        }
    }
}
