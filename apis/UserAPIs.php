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
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * An API that is used to do any thing that is related to user management.
 *
 * @author Ibrahim
 * @version 1.0
 */
class UserAPIs extends API{
    public function __construct() {
        parent::__construct();
        $this->initAPI();
    }
    /**
     * Initialize the API Object.
     * @since 1.0
     */
    private function initAPI(){
        $this->setVersion('1.0.0');
        $a1 = new APIAction('add-user');
        $a1->addRequestMethod('POST');
        $a1->addParameter(new RequestParameter('username', 'string'));
        $a1->addParameter(new RequestParameter('email', 'email'));
        $a1->addParameter(new RequestParameter('password', 'string'));
        $a1->addParameter(new RequestParameter('conf-password', 'string'));
        $a1->addParameter(new RequestParameter('display-name', 'string', TRUE));
        $this->addAction($a1);
        
        //action #2
        $a2 = new APIAction('update-email');
        $a2->addRequestMethod('POST');
        $a2->addParameter(new RequestParameter('old-email','email'));
        $a2->addParameter(new RequestParameter('new-email','email'));
        $a2->addParameter(new RequestParameter('password','string'));
        $a2->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a2,TRUE);
        
        $a2S = new APIAction('confirm-updated-email');
        $a2S->addRequestMethod('get');
        $a2S->addRequestMethod('post');
        $a2->addParameter(new RequestParameter('token', 'string', TRUE));
        $a2->addParameter(new RequestParameter('confirmation-token', 'string', TRUE));
        
        //action #3
        $a3 = new APIAction('get-users');
        $a3->addRequestMethod('GET');
        $a3->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a3,TRUE);
        
        $a6 = new APIAction();
        $a6->addRequestMethod('POST');
        $a6->setName('update-user-status');
        $a6->addParameter(new RequestParameter('user-id', 'integer'));
        $a6->addParameter(new RequestParameter('status','string', FALSE));
        $a6->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a6,TRUE);
        
        $a7 = new APIAction();
        $a7->addRequestMethod('POST');
        $a7->setName('update-display-name');
        $a7->addParameter(new RequestParameter('user-id', 'integer'));
        $a7->addParameter(new RequestParameter('display-name','string'));
        $a7->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a7,TRUE);
        
        $a8 = new APIAction();
        $a8->addRequestMethod('POST');
        $a8->setName('update-access-level');
        $a8->addParameter(new RequestParameter('user-id', 'integer'));
        $a8->addParameter(new RequestParameter('access-level','integer'));
        $a8->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a8,TRUE);
        
        $a9 = new APIAction();
        $a9->addRequestMethod('POST');
        $a9->setName('activate-account');
        $a9->addParameter(new RequestParameter('activation-token','string'));
        $a9->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a9,TRUE);
        
        $a10 = new APIAction();
        $a10->addRequestMethod('GET');
        $a10->setName('get-profile');
        $a10->addParameter(new RequestParameter('user-id', 'integer'));
        $a10->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a10,TRUE);
        
        $this->setVersion('1.0.1');
        
        
    }
    /**
     * Called by the routing function to perform the 'update-staus' action
     * @since 1.0
     */
    private function updateStatus(){
        $inputs = $this->getInputs();
        $user = UserFunctions::get()->updateStatus($inputs['status'], $inputs['user-id']);
        if($user instanceof User){
            $this->sendResponse('User Status Updated', FALSE, 200, '"profile":'.$user->toJSON());
        }
        else if($user == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($user == UserFunctions::NO_SUCH_USER){
            $this->sendResponse('No Such User', TRUE, 404);
        }
        else if($user == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else if($user == UserFunctions::STATUS_NOT_ALLOWED){
            $json = new JsonX();
            foreach (UserFunctions::USER_STATUS as $k =>$v){
                $json->add($k, $v);
            }
            $this->sendResponse('Status Not Allowed', TRUE, 404, '"status":"'.$inputs['status'].'",'
                    . '"allowed":'.$json);
        }
        else{
            $this->sendResponse('Something wrong', TRUE, 404);
        }
    }
    /**
     * Called by the routing function to perform the 'update-display-name' action
     * @since 1.0
     */
    private function updateDisplayName(){
        $inputs = $this->getInputs();
        $user = UserFunctions::get()->updateDisplayName($inputs['display-name'], $inputs['user-id']);
        if($user instanceof User){
            $this->sendResponse('Display Name Updated', FALSE, 200, '"user":'.$user->toJSON());
        }
        else if($user == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($user == UserFunctions::NO_SUCH_USER){
            $this->sendResponse('No Such User', TRUE, 404);
        }
        else if($user == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else{
            $this->sendResponse('Something wrong', TRUE, 404,'"thing":"'.$user.'"');
        }
    }
    /**
     * Called by the routing function to perform the 'update-email' action
     * @since 1.0
     */
    private function updateEmail(){
        $inputs = $this->getInputs();
        $user = UserFunctions::get()->updateEmail($inputs['email'], $inputs['user-id']);
        if($user instanceof User){
            $this->sendResponse('Email Updated', FALSE, 200, '"user":'.$user->toJSON());
        }
        else if($user == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($user == UserFunctions::NO_SUCH_USER){
            $this->sendResponse('No Such User', TRUE, 404);
        }
        else if($user == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else if($user == UserFunctions::USER_ALREAY_REG){
            $this->sendResponse('Email Already Registred', TRUE, 404);
        }
        else{
            $this->sendResponse('Something wrong', TRUE, 404,'"thing":"'.$user.'"');
        }
    }
    /**
     * Called by the routing function to perform the 'get-profile' action
     * @since 1.0
     */
    private function getProfile(){
        $inputs = $this->getInputs();
        if(isset($inputs['user-id'])){
            $user = UserFunctions::get()->getUserByID($inputs['user-id']);
            if($user instanceof User){
                $this->sendResponse('User Profile', FALSE, 200, '"profile":'.$user->toJSON());
            }
            else if($user == UserFunctions::NOT_AUTH){
                $this->notAuth();
            }
            else if($user == UserFunctions::NO_SUCH_USER){
                $this->sendResponse('No Such User', TRUE, 404);
            }
            else if($user == MySQLQuery::QUERY_ERR){
                $this->databaseErr();
            }
            else{
                $this->sendResponse('Something wrong', TRUE, 404);
            }
        }
        else{
            $this->missingParam('user-id');
        }
    }
    /**
     * Called by the routing function to perform the 'activate-account' action
     * @since 1.0
     */
    private function activateAccount() {
        $inputs = $this->getInputs();
        $user = UserFunctions::get()->activateAccount($inputs['activation-token']);
        if($user === TRUE){
            $this->sendResponse('Activated', FALSE, 200);
        }
        else if($user == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($user == FALSE){
            $this->sendResponse('Wrong Token', TRUE, 404);
        }
        else if($user == UserFunctions::ALREADY_ACTIVATED){
            $this->sendResponse('Already Activated', FALSE, 200);
        }
        else if($user == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else{
            $this->sendResponse('Something wrong', TRUE, 404);
        }
    }
    /**
     * Called by the routing function to perform the 'get-users' action
     * @since 1.0
     */
    private function getUsers(){
        $users = UserFunctions::get()->getUsers();
        if($users == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($users == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else{
            $json = new JsonX();
            $json->add('users', $users);
            $this->sendResponse('List Of Users', FALSE, 200, '"users":'.$json);
        }
    }
    
    /**
     * A routing function.
     * @since 1.0
     */
    public function processRequest(){
        $action = parent::getAction();
        if($action == 'add-user'){
            $this->addUser();
        }
        else if($action == 'get-users'){
            $this->getUsers();
        }
        else if($action == 'get-profile'){
            $this->getProfile();
        }
        else if($action == 'update-email'){
            $this->updateEmail();
        }
        else if($action == 'update-display-name'){
            $this->updateDisplayName();
        }
        else if($action == 'update-access-level'){
            $this->updateAccessLevel();
        }
        else if($action == 'update-user-status'){
            $this->updateStatus();
        }
        else if($action == 'activate-account'){
            $this->activateAccount();
        }
    }
    /**
     * Called by the routing function to perform the 'update-access-level' action
     * @since 1.0
     */
    private function updateAccessLevel() {
        $inputs = $this->getInputs();
        $result = UserFunctions::get()->updateAccessLevel($inputs['access-level'], $inputs['user-id']);
        if($result instanceof User){
            $this->sendResponse('Access Level Updated', FALSE, 200, '"user":'.$result->toJSON());
        }
        else if($result == UserFunctions::NO_SUCH_USER){
            $this->sendResponse('No Such User', TRUE, 404);
        }
        else if($result == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else if($result == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else{
            $this->sendResponse('Something wrong', TRUE, 404);
        }
    }
    /**
     * Called by the routing function to perform the 'add-user' action
     * @since 1.0
     */
    private function addUser(){
        $inputs = $this->getInputs();
        if($inputs['password'] != $inputs['conf-password']){
            $this->sendResponse('The given two passwords do not match', TRUE, 404);
            return;
        }
        $user = new User($inputs['username'], hash('sha256',$inputs['password']), $inputs['email']);
        if(isset($inputs['display-name']) && strlen($inputs['display-name']) != 0){
            $user->setDisplayName($inputs['display-name']);
        }
        else{
            $user->setDisplayName($inputs['username']);
        }
        $r = UserFunctions::get()->register($user);
        if($r == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
        else if($r == UserFunctions::USERNAME_TAKEN){
            $this->sendResponse('Username Taken', TRUE, 404);
        }
        else if($r == UserFunctions::USER_ALREAY_REG){
            $this->sendResponse('User Already Registred', TRUE, 404);
        }
        else if($r == UserFunctions::REG_CLOSED){
            $this->sendResponse($r,TRUE,401,'"details":"The settings of the system does not allow user registration."');
        }
        else if($r == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }else if($r == UserFunctions::EMPTY_STRING){
            $this->sendResponse('Unkown Empty Parameter', TRUE, 404);
        }
        else if($r == FALSE){
            $this->sendResponse('Unable to Create Profile', TRUE, 404);
        }
        else if($r == UserFunctions::EMPTY_STRING){
            $this->sendResponse('Unkown Empty Parameter', TRUE, 404);
        }
        else{
            $this->sendResponse('User Profile Created', FALSE, 201, '"user":'.$r->toJSON());
        }
    }
    public function isAuthorized() {
        if($this->getAction() == 'add-user'){
            if(Config::get()->getUserRegStatus() == 'O'){
                return TRUE;
            }
            else if(Config::get()->getUserRegStatus() == 'AO'){
                return SystemFunctions::get()->getMainSession()->validateToken() && 
                       SystemFunctions::get()->getAccessLevel() == 0 ;
            }
            else{
                return FALSE;
            }
        }
        return TRUE;
    }
}
if(defined('API_CALL') && API_CALL === TRUE){
    $api = new UserAPIs();
    $api->process();
}