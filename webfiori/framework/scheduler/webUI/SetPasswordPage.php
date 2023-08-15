<?php
namespace webfiori\framework\scheduler\webUI;

use webfiori\framework\scheduler\TasksManager;
use webfiori\http\Response;

/**
 * A page which has inputs to set tasks scheduler protection password.
 *
 * @author Ibrahim
 */
class SetPasswordPage extends BaseTasksPage {
    public function __construct() {
        parent::__construct('Set Scheduler Password');

        if (TasksManager::password() != 'NO_PASSWORD') {
            Response::addHeader('location', $this->getBase().'/scheduler/login');
        }

        $this->insert($this->include('templates/set-password-form.html'));
    }
}
