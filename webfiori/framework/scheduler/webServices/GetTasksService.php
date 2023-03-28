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

use webfiori\framework\cron\TasksManager;
use webfiori\framework\session\SessionsManager;
use webfiori\http\AbstractWebService;
use webfiori\json\Json;
/**
 * A web service which is used to fetch a list of all scheduled jobs.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class GetTasksService extends AbstractWebService {
    public function __construct() {
        parent::__construct('get-jobs');
        $this->addRequestMethod('get');
    }
    public function isAuthorized() {
        SessionsManager::start('cron-session');

        return SessionsManager::get('cron-login-status') === true
                || TasksManager::password() == 'NO_PASSWORD';
    }

    public function processRequest() {
        $json = new Json([
            'jobs' => TasksManager::jobsQueue()->toArray()
        ]);
        $this->send('application/json', $json);
    }
}
