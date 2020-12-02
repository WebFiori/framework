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

use webfiori\http\AbstractWebService;
use webfiori\framework\cron\Cron;
use webfiori\http\RequestParameter;
use webfiori\framework\session\SessionsManager;
use webfiori\json\Json;
use webfiori\http\WebServicesManager;
/**
 * A web service which is used to force job execution using web interface.
 *
 * @author Ibrahim
 */
class ForceCronExecutionService extends AbstractWebService {
    public function __construct() {
        parent::__construct('force-execution');
        $this->addRequestMethod('post');
        $this->addParameter(new RequestParameter('job-name'));
    }
    public function isAuthorized() {
        SessionsManager::start('cron-session');
        return SessionsManager::get('cron-login-status') === true;
    }

    public function processRequest() {
        $jobName = urldecode($this->getParamVal('job-name'));
        $result = Cron::run('', $jobName, true);

        if (gettype($result) == 'array') {
            $infoJ = new Json([],true);
            $infoJ->add('jobs-count', $result['total-jobs']);
            $infoJ->add('executed-count', $result['executed-count']);
            $infoJ->add('successfully-completed', $result['successfully-completed']);
            $infoJ->add('failed', $result['failed']);
            $infoJ->addArray('log', Cron::getLogArray());
            $this->sendResponse('Job Successfully Executed.', 'info', 200, $infoJ);
        } else if ($result == 'JOB_NOT_FOUND'){
            $infoJ = new Json([
                'message' => 'No job was found which has the name "'.$jobName.'".',
                'type' => WebServicesManager::E
            ]);
            $this->send('application/json',$infoJ, 404);
        } else {
            $this->sendResponse($result, WebServicesManager::E, 404);
        }
    }

}
