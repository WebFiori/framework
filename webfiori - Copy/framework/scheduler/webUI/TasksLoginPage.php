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
namespace webfiori\framework\scheduler\webUI;

use webfiori\framework\scheduler\TasksManager;
use WebFiori\Http\Response;
/**
 * A page which is used to show login form to enter login information to
 * access tasks management web interface.
 *
 * @author Ibrahim
 */
class TasksLoginPage extends BaseTasksPage {
    public function __construct() {
        parent::__construct('Tasks Scheduler Web Interface Login', 'Login to Scheduler Control panel.');

        if (TasksManager::getPassword() == 'NO_PASSWORD') {
            Response::addHeader('location', $this->getBase().'/scheduler/set-password');
            Response::send();
        }

        if ($this->isLoggedIn()) {
            Response::addHeader('location', $this->getBase().'/scheduler/tasks');
            Response::send();
        }

        $row = $this->insert('v-row');
        $row->setAttributes([
            'align' => 'center'
        ]);
        $row->addChild('v-col', [
            'cols' => 12,
            'md' => 4, 'sm' => 12
        ])->addChild($this->include('templates/login-form.html'));
    }
}
