<?php
namespace webfiori\framework\cron\webServices;

use webfiori\http\AbstractWebService;
use webfiori\json\Json;
use webfiori\framework\cron\Cron;
use webfiori\framework\session\SessionsManager;
/**
 * A web service which is used to fetch a list of all scheduled jobs.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class GetJobsService extends AbstractWebService {
    public function __construct() {
        parent::__construct('get-jobs');
        $this->addRequestMethod('get');
    }
    public function isAuthorized() {
        SessionsManager::start('cron-session');
        
        return SessionsManager::get('cron-login-status') === true
                || Cron::password() == 'NO_PASSWORD';
    }
    
    public function processRequest() {
        $json = new Json([
            'jobs' => Cron::jobsQueue()->toArray()
        ]);
        $this->send('application/json', $json);
    }

}
