<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\cron;

use jsonx\JsonX;
use restEasy\APIAction;
use restEasy\RequestParameter;
use webfiori\entity\ExtendedWebServices;
use webfiori\WebFiori;
/**
 * A set of web services which is used to control basic 
 * cron functions.
 *
 * @author Ibrahim
 */
class CronAPIs extends ExtendedWebServices {
    public function __construct() {
        parent::__construct();
        $loginService = new APIAction('login');
        $loginService->addRequestMethod('post');
        $loginService->addParameter(new RequestParameter('password'));
        $this->addAction($loginService);

        $forceExecService = new APIAction('force-execution');
        $forceExecService->addRequestMethod('post');
        $forceExecService->addParameter(new RequestParameter('job-name'));
        $this->addAction($forceExecService,true);

        $logoutService = new APIAction('logout');
        $logoutService->addRequestMethod('post');
        $logoutService->addRequestMethod('get');
        $this->addAction($logoutService);
    }
    public function isAuthorized() {
        return WebFiori::getWebsiteController()->getSession()->get('cron-login-status') === true;
    }

    public function processRequest() {
        $calledServiceName = $this->getAction();

        if ($calledServiceName == 'login') {
            $this->_login();
        } else if ($calledServiceName == 'logout') {
            WebFiori::getWebsiteController()->getSession()->set('cron-login-status',false);
            $this->sendResponse('Logged out.', 'info');
        } else if ($calledServiceName == 'force-execution') {
            $this->_forceExecution();
        }
    }
    private function _forceExecution() {
        $jobName = urldecode($this->getInputs()['job-name']);
        $result = Cron::run('', $jobName, true);

        if (gettype($result) == 'array') {
            $infoJ = new JsonX([],true);
            $infoJ->add('jobs-count', $result['total-jobs']);
            $infoJ->add('executed-count', $result['executed-count']);
            $infoJ->add('successfully-completed', $result['successfully-completed']);
            $infoJ->add('failed', $result['failed']);
            $infoJ->addArray('log', Cron::getLogArray());
            $this->sendResponse('Job Successfully Executed.', 'info', 200, $infoJ);
        } else if ($result == 'JOB_NOT_FOUND'){
            $infoJ = new JsonX([
                'message' => 'No job was found which has the name "'.$jobName.'".',
                'type' => self::E
            ]);
            $this->send('application/json',$infoJ, 404);
        } else {
            $this->sendResponse($result, self::E, 404);
        }
    }
    private function _login() {
        $cronPass = Cron::password();

        if ($cronPass != 'NO_PASSWORD') {
            $inputHash = hash('sha256', $this->getInputs()['password']);

            if ($inputHash == $cronPass) {
                WebFiori::getWebsiteController()->getSession()->set('cron-login-status', true);
                $this->sendResponse('Success', 'info', 200);
            } else {
                $this->sendResponse('Incorrect password', 'error', 404);
            }
        } else {
            $this->sendResponse('Success', 'info', 200);
        }
    }
}

return __NAMESPACE__;
