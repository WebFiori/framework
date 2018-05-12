<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once '../root.php';
/**
 * An API used to get or information about the system.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class SysAPIs extends API{
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0.1');
        $a1 = new APIAction();
        $a1->addRequestMethod('GET');
        $a1->setName('get-site-info');
        $this->addAction($a1,TRUE);
        
        $a3 = new APIAction();
        $a3->addRequestMethod('GET');
        $a3->setName('get-sys-info');
        $this->addAction($a3,TRUE);
        
        $a4 = new APIAction();
        $a4->setName('get-main-session');
        $a4->addRequestMethod('get');
        $this->addAction($a4,TRUE);
        
        $a5 = new APIAction();
        $a5->setName('update-database-attributes');
        $a5->addRequestMethod('post');
        $a5->addParameter(new RequestParameter('host', 'string'));
        $a5->addParameter(new RequestParameter('database-username', 'string'));
        $a5->addParameter(new RequestParameter('database-password', 'string'));
        $a5->addParameter(new RequestParameter('database-name', 'string'));
        $this->addAction($a5, TRUE);
        
        $a6 = new APIAction();
        $a6->setName('get-email-accounts');
        $a6->addRequestMethod('get');
        $this->addAction($a6, TRUE);
        
        $a7 = new APIAction();
        $a7->setName('create-first-account');
        $a7->addRequestMethod('post');
        $a7->addParameter(new RequestParameter('username', 'string'));
        $a7->addParameter(new RequestParameter('password', 'string'));
        $a7->addParameter(new RequestParameter('email', 'string'));
        $this->addAction($a7);
        
        $a8 = new APIAction();
        $a8->setName('update-site-info');
        $a8->addRequestMethod('post');
        $a8->addParameter(new RequestParameter('site-name', 'string'));
        $a8->addParameter(new RequestParameter('description', 'string'));
        $this->addAction($a8, TRUE);
    } 
    
    public function processRequest(){
        $action = $this->getAction();
        if($action == 'get-sys-info'){
            $json = new JsonX();
            $json->add('system-info', SystemFunctions::get()->getConfigVars());
            $this->sendResponse('Software Information', FALSE, 200, '"info":'.$json);
        }
        else if($action == 'get-site-info'){
            $json = new JsonX();
            $json->add('site-info', SystemFunctions::get()->getSiteConfigVars());
            $this->sendResponse('Website Information', FALSE, 200, '"info":'.$json);
        }
        else if($action == 'get-main-session'){
            $this->sendResponse('Main Session Info', FALSE, 200, '"session":'.SystemFunctions::get()->getMainSession()->toJSON());
        }
        else if($action == 'update-site-info'){
            $this->actionNotImpl();
        }
        else if($action == 'create-first-account'){
            $this->actionNotImpl();
        }
        else if($action == 'update-database-attributes'){
            $this->updateDBAttrs();
        }
        else if($action == 'get-email-accounts'){
            $j = new JsonX();
            $accountNum = 0;
            $accountsKeys = array_keys(MailConfig::get()->getAccounts());
            foreach ($accountsKeys as $key){
                $account = MailConfig::get()->getAccount($key);
                $jAcc = new JsonX();
                $jAcc->add('address', $account->getAddress());
                $jAcc->add('name', $account->getName());
                $j->add('account-'+$accountNum, $jAcc);
                $accountNum++;
            }
            $this->sendResponse('Email Accounts', FALSE, 200, '"accounts":'.$j);
        }
    }
    /**
     * A function that is called to update database attributes
     * @since 1.0
     */
    private function updateDBAttrs() {
        $i = $this->getInputs();
        if(isset($i['host'])){
            if(isset($i['database-username'])){
                if(isset($i['database-password'])){
                    if(isset($i['database-name'])){
                        $r = SystemFunctions::get()->updateDBAttributes($i['host'], $i['database-username'], $i['database-password'], $i['database-name']);
                        if($r === TRUE){
                            $this->sendResponse('Database Updated.');
                        }
                        else{
                            $this->sendResponse(SessionManager::DB_CONNECTION_ERR, TRUE, 404, '"details":'.
                            SystemFunctions::get()->getMainSession()->getDBLink()->toJSON());
                        }
                    }
                    else{
                        $this->missingParam('database-name');
                    }
                }
                else{
                    $this->missingParam('database-password');
                }
            }
            else{
                $this->missingParam('database-username');
            }
        }
        else{
            $this->missingParam('host');
        }
    }
    
    public function isAuthorized() {
        $a = $this->getAction();
        if($a == 'update-database-attributes'){
            if(class_exists('Config')){
                return !Config::get()->isConfig() || 
                    SystemFunctions::get()->getAccessLevel() == 0;
            }
            else{
                return TRUE;
            }
        }
        else if($a == 'create-first-account'){
            
        }
        else if($a == 'get-site-info' || $a == 'get-sys-info'){
            return SystemFunctions::get()->getAccessLevel() == 0;
        }
        else{
            return SystemFunctions::get()->getAccessLevel() == 0;
        }
    }
}
$SysAPIs = new SysAPIs();
$SysAPIs->process();
