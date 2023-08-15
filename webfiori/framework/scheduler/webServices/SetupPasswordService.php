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

use webfiori\framework\App;
use webfiori\framework\scheduler\TasksManager;
use webfiori\http\AbstractWebService;
use webfiori\http\RequestParameter;
/**
 * An API which is used to update scheduler password for first use.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class SetupPasswordService extends AbstractWebService {
    public function __construct() {
        parent::__construct('set-password');
        $this->addRequestMethod('post');
        $this->addParameter(new RequestParameter('password'));
    }
    public function isAuthorized() {
        return TasksManager::getPassword() == 'NO_PASSWORD';
    }

    public function processRequest() {
        App::getConfig()->setSchedulerPassword($this->getParamVal('password'));
        $this->sendResponse('Password updated.', self::I, 200);
    }
}
