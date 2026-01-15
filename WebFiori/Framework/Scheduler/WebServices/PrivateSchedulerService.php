<?php
namespace WebFiori\Framework\Scheduler\WebServices;

use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\AbstractWebService;

/**
 * A base service which has a check for authorization
 *
 * @author Ibrahim
 */
abstract class PrivateSchedulerService extends AbstractWebService {
    /**
     * Checks if the client is authorized to call the service or not.
     *
     * The method will check if session variable 'scheduler-is-logged-in' is
     * set to true or not. The variable is set when the client successfully
     * logged in.
     *
     * @return bool
     */
    public function isAuthorized(): bool {
        return SessionsManager::getActiveSession()->get('scheduler-is-logged-in') === true;
    }
}
