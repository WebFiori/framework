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
use WebFiori\Http\AbstractWebService;
use WebFiori\Http\RequestParameter;
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

    public function processRequest() {
        $schedulerPass = TasksManager::getPassword();
        $inputHash = hash('sha256', $this->getInputs()['password']);

        if ($inputHash == $schedulerPass) {
            SessionsManager::set('scheduler-is-logged-in', true);
            $this->sendResponse('Success', self::I, 200, SessionsManager::getActiveSession());
        } else {
            $this->sendResponse('Incorrect password', self::E, 404);
        }
    }
}
