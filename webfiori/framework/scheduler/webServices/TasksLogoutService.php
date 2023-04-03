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

use webfiori\framework\session\SessionsManager;
use webfiori\http\AbstractWebService;
/**
 * A service which is used to log out user in scheduler web interface.
 *
 * @author Ibrahim
 */
class TasksLogoutService extends AbstractWebService {
    public function __construct() {
        parent::__construct('logout');
        $this->addRequestMethod('post');
    }
    public function isAuthorized() {
        return true;
    }

    public function processRequest() {
        SessionsManager::start('scheduler-session');
        SessionsManager::destroy();
        $this->sendResponse('Logged out.', 'info');
    }
}