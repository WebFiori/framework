<?php

/*
 * The MIT License
 *
 * Copyright 2020, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
                $this->sendResponse('Success', 'info', 200);
            } else {
                $this->sendResponse('Incorrect password', 'error', 404);
            }
        } else {
            $this->sendResponse('Success', 'info', 200);
        }
    }
}
