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
use WebFiori\Json\Json;
/**
 * A web service which is used to fetch a list of all scheduled tasks.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class GetTasksService extends PrivateSchedulerService {
    public function __construct() {
        parent::__construct('get-tasks');
        $this->addRequestMethod('get');
    }

    public function processRequest() {
        $json = new Json([
            'tasks' => TasksManager::tasksQueue()->toArray()
        ]);
        $this->send('application/json', $json);
    }
}
