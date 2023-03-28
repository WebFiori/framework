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
use webfiori\http\WebServicesManager;
use webfiori\json\Json;
/**
 * A web service which is used to force task execution using web interface.
 *
 * @author Ibrahim
 */
class ForceTaskExecutionService extends AbstractWebService {
    public function __construct() {
        parent::__construct('force-execution');
        $this->addRequestMethod('post');
        $this->addParameter(new RequestParameter('task-name'));
    }
    public function isAuthorized() {
        SessionsManager::start('cron-session');

        return SessionsManager::get('cron-login-status') === true
                || TasksManager::password() == 'NO_PASSWORD';
    }

    public function processRequest() {
        $taskName = urldecode($this->getParamVal('task-name'));
        $result = TasksManager::run('', $taskName, true);

        if (gettype($result) == 'array') {
            $infoJ = new Json([],true);
            $infoJ->add('tasks-count', $result['total-tasks']);
            $infoJ->add('executed-count', $result['executed-count']);
            $infoJ->add('successfully-completed', $result['successfully-completed']);
            $infoJ->add('failed', $result['failed']);
            $infoJ->addArray('log', TasksManager::getLogArray());
            $this->sendResponse('Task Successfully Executed.', 'info', 200, $infoJ);
        } else if ($result == 'TASK_NOT_FOUND') {
            $infoJ = new Json([
                'message' => 'No task was found which has the name "'.$taskName.'".',
                'type' => WebServicesManager::E
            ]);
            $this->send('application/json',$infoJ, 404);
        } else {
            $this->sendResponse($result, WebServicesManager::E, 404);
        }
    }
}
