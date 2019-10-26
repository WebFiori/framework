<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\entity\cron;
use restEasy\APIAction;
use restEasy\RequestParameter;
use webfiori\WebFiori;
use webfiori\entity\ExtendedWebAPI;
use webfiori\entity\cron\Cron;
/**
 * Description of CronAPIs
 *
 * @author Ibrahim
 */
class CronAPIs extends ExtendedWebAPI{
    public function __construct() {
        parent::__construct();
        $a00 = new APIAction('login');
        $a00->addRequestMethod('post');
        $a00->addParameter(new RequestParameter('password'));
        $this->addAction($a00);
    }
    public function isAuthorized(){
        return WebFiori::getWebsiteFunctions()->getSessionVar('cron-login-status') === true;
    }

    public function processRequest() {
        $a = $this->getAction();
        if($a == 'login'){
            $this->_login();
        }
    }
    
    private function _login(){
        $cronPass = Cron::password();
        if($cronPass != 'NO_PASSWORD'){
            if(hash('sha256', $this->getInputs()['password'])){
                WebFiori::getWebsiteFunctions()->setSessionVar('cron-login-status', true);
                $this->sendResponse('Success', 'info', 200);
            }
            else{
                $this->sendResponse('Failed', 'error', 404);
            }
        }
        else{
            $this->sendResponse('Success', 'info', 200);
        }
    }

}
$service = new CronAPIs();
$service->process();
