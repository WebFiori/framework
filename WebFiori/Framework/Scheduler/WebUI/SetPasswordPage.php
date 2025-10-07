<?php
namespace WebFiori\Framework\Scheduler\WebUI;

use WebFiori\Framework\Scheduler\TasksManager;
use WebFiori\Http\Response;

/**
 * A page which has inputs to set tasks scheduler protection password.
 *
 * @author Ibrahim
 */
class SetPasswordPage extends BaseTasksPage {
    public function __construct() {
        parent::__construct('Set Scheduler Password');

        if (TasksManager::getPassword() != 'NO_PASSWORD') {
            Response::addHeader('location', $this->getBase().'/scheduler/login');
        }

        $this->insert($this->include('templates/set-password-form.html'));
    }
}
