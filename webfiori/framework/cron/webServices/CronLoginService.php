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
namespace webfiori\framework\cron\webServices;

use webfiori\framework\cron\Cron;
use webfiori\framework\session\SessionsManager;
use webfiori\http\AbstractWebService;
use webfiori\http\RequestParameter;
/**
 * An API which is used to authenticate users to access CRON web interface.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class CronLoginService extends AbstractWebService {
    public function __construct() {
        parent::__construct('login');
        $this->addRequestMethod('post');
        $this->addParameter(new RequestParameter('password'));
    }
    public function isAuthorized() {
        return true;
    }

    public function processRequest() {
        $cronPass = Cron::password();

        if ($cronPass != 'NO_PASSWORD') {
            $inputHash = hash('sha256', $this->getInputs()['password']);

            if ($inputHash == $cronPass) {
                SessionsManager::start('cron-session');
                SessionsManager::set('cron-login-status', true);
                $this->sendResponse('Success', 'info');
            } else {
                $this->sendResponse('Incorrect password', 'error', 404);
            }
        } else {
            $this->sendResponse('Success', 'info');
        }
    }
}
