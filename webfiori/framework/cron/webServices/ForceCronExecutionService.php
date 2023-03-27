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
use webfiori\http\WebServicesManager;
use webfiori\json\Json;
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

        return SessionsManager::get('cron-login-status') === true
                || Cron::password() == 'NO_PASSWORD';
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
        } else if ($result == 'JOB_NOT_FOUND') {
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
