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
use WebFiori\Http\AbstractWebService;
/**
 * A service which is used to log out user in scheduler web interface.
 *
 * @author Ibrahim
 */
class TasksLogoutService extends AbstractWebService {
    public function __construct() {
        parent::__construct('logout');
        $this->addRequestMethod('post');
        $this->addRequestMethod('get');
    }
    public function processRequest() {
        SessionsManager::destroy();
        $this->sendResponse('Logged out.', 'info');
    }
}
