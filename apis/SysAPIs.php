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
if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
define('SETUP_MODE', '');
/**
 * An API used to get or information about the system.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class SysAPIs extends API{
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0.1');
        $this->setDescription('System and system configuratin APIs.');

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
        $a7->addParameter(new RequestParameter('email', 'email'));
        $this->addAction($a7);
        
        $this->setVersion('1.1.0');
        $a9 = new APIAction('update-send-email-account');
        $a9->setDescription('Updates the email account (SMTP Account) that is used to send system messages.');
        $a9->addRequestMethod('post');
        $a9->addParameter(new RequestParameter('server-address', 'string'));
        $a9->addParameter(new RequestParameter('server-port', 'integer'));
        $a9->getParameterByName('server-port')->setMinVal(0);
        $a9->getParameterByName('server-port')->setMaxVal(5000);
        $a9->addParameter(new RequestParameter('email-address', 'email'));
        $a9->addParameter(new RequestParameter('username', 'string'));
        $a9->addParameter(new RequestParameter('password', 'string'));
        $a9->addParameter(new RequestParameter('name', 'string'));
        $this->addAction($a9, TRUE);
        
        $this->setVersion('1.1.1');
        
        $a10 = new APIAction('get-installed-themes');
        $a10->setDescription('Gets all the themes in the directory \'publish/themes\'.');
        $a10->addRequestMethod('get');
        $this->addAction($a10, TRUE);
        $a11 = new APIAction('update-notifications-email');
        $a11->setDescription('Updates the email address that is used to send notifications to '
                . 'about system events.');
        $a11->addRequestMethod('post');
        $a11->addParameter(new RequestParameter('email-address', 'email'));
        $this->addAction($a11, TRUE);
        
        $a12 = new APIAction('confirm-notifications-email');
        $a12->setDescription('Confirms the updated email address that is used to send notifications to '
                . 'about system events.');
        $a12->addRequestMethod('post');
        $a12->addRequestMethod('get');
        $a12->addParameter(new RequestParameter('confirmation-token', 'string'));
        $a12->getParameterByName('confirmation-token')->setDescription('The token that was sent to the email address.');
        $this->addAction($a12, TRUE);
        
        $this->setVersion('1.1.2');
        $a13 = new APIAction('get-language');
        $a13->addRequestMethod('get');
        $a13->addParameter(new RequestParameter('language-code', 'string'));
        $this->addAction($a13, TRUE);
    } 
    
    public function processRequest(){
        $action = $this->getAction();
        if($action == 'get-sys-info'){
            $json = new JsonX();
            $json->add('system-info', SystemFunctions::get()->getConfigVars());
            $this->send('application/json', $json);
        }
        else if($action == 'update-notifications-email'){
            $this->actionNotImpl();
        }
        else if($action == 'confirm-notifications-email'){
            $this->actionNotImpl();
        }
        else if($action == 'get-installed-themes'){
            $themes = Page::get()->getAvailableThemes();
            $json = new JsonX();
            $index = 0;
            foreach ($themes as $theme){
                $themeJson = new JsonX();
                foreach ($theme['META'] as $k => $v){
                    $themeJson->add($k, $v);
                }
                $json->add('theme-'.$index, $themeJson);
                $index++;
            }
            $this->send('application/json', $json);
        }
        else if($action == 'get-main-session'){
            $this->send('application/json', SystemFunctions::get()->getMainSession()->toJSON());
        }
        else if($action == 'create-first-account'){
            $i = $this->getInputs();
            $user = new User();
            $user->setEmail($i['email']);
            $user->setPassword(hash('sha256', $i['password']));
            $user->setUserName($i['username']);
            $r = AdminFunctions::get()->runSetup($user);
            if($r === TRUE){
                if(UserFunctions::get()->authenticate($i['username'], $i['password'], $i['email'], 30, TRUE) == TRUE){
                    SystemFunctions::get()->configured(TRUE);
                    $this->sendResponse('Setup Completed');
                }
                else{
                    $this->sendResponse('Something went wrong.', TRUE, 404);
                }
            }
            else if($r === MySQLQuery::QUERY_ERR){
                $this->sendResponse($r, TRUE, 404, '"details":'.SystemFunctions::get()->getMainSession()->getDBLink()->toJSON());
            }
            else{
                $this->sendResponse($r, TRUE,404);
            }
        }
        else if($action == 'update-database-attributes'){
            $this->updateDBAttrs();
        }
        else if($action == 'update-send-email-account'){
            $this->updateSendMail();
        }
        else if($action == 'get-language'){
            try{
                $langx = Language::loadTranslation($this->getInputs()['language-code']);
                $j = new JsonX;
                $j->addArray('language', $langx->getLanguageVars(),TRUE);
                $this->send('application/json', $j);
            } catch (Exception $ex) {
                $this->sendResponse('No Such Language.', TRUE, 404);
            }
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
     * Updates the email that is used to send system notifications.
     * @since 1.1
     */
    private function updateSendMail() {
        $i = $this->getInputs();
        $account = new EmailAccount();
        $account->setAddress($i['email-address']);
        $account->setServerAddress($i['server-address']);
        $account->setPort($i['server-port']);
        $account->setName($i['name']);
        $account->setPassword($i['password']);
        $account->setUsername($i['username']);
        $r = MailFunctions::get()->updateEmailAccount($account);
        if($r === TRUE){
            $this->sendResponse('Account Updated');
        }
        else if($r == MailFunctions::INV_CREDENTIALS){
            $this->sendResponse($r, TRUE, 404, '"details":"The given username and password are invalid."');
        }
        else{
            $this->sendResponse($r, TRUE, 404, '"details":"The given server address or port are invalid."');
        }
    }
    
    /**
     * A function that is called to update database attributes
     * @since 1.0
     */
    private function updateDBAttrs() {
        $i = $this->getInputs();
        $r = SystemFunctions::get()->updateDBAttributes($i['host'], $i['database-username'], $i['database-password'], $i['database-name']);
        if($r === TRUE){
            $this->sendResponse('Database Updated.');
        }
        else if($r == SystemFunctions::DB_NOT_EMPTY){
            $this->sendResponse(SystemFunctions::DB_NOT_EMPTY, TRUE, 404, '"details":{"detailed-message":"The selected schema is not empty.","error-code":10000}');
        }
        else{
            $this->sendResponse(SessionManager::DB_CONNECTION_ERR, TRUE, 404, '"details":'.
            SystemFunctions::get()->getMainSession()->getDBLink()->toJSON());
        }
    }
    
    public function isAuthorized() {
        $a = $this->getAction();
        if($a == 'update-database-attributes'|| 'update-send-email-account'){
            if(class_exists('Config')){
                if(class_exists('SiteConfig')){
                    return !Config::get()->isConfig() || 
                    SystemFunctions::get()->getAccessLevel() == 0;
                }
                else{
                    return TRUE;
                }
            }
            else{
                return TRUE;
            }
        }
        else if($a == 'create-first-account'){
            if(class_exists('Config')){
                return !Config::get()->isConfig();
            }
            return FALSE;
        }
        else{
            if(class_exists('Config')){
                return SystemFunctions::get()->getAccessLevel() == 0;
            }
            return TRUE;
        }
    }
}
$SysAPIs = new SysAPIs();
$SysAPIs->process();
