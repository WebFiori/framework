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
        $tok = new RequestParameter('token','string', TRUE);
        $userId = new RequestParameter('user-id','integer', FALSE);
        $pass = new RequestParameter('password','string', FALSE);
        //action #1
        $a1 = new APIAction();
        $a1->addRequestMethod('POST');
        $a1->setName('add-user');
        $a1p1 = new RequestParameter('username','string', FALSE);
        $a1p3 = new RequestParameter('email','email', FALSE);
        $a1->addParameter($a1p1);
        $a1->addParameter($pass);
        $a1->addParameter($a1p3);
        $a1->addParameter(new RequestParameter('access-level','integer', FALSE));
        $this->addAction($a1);
        
        //action #2
        $a2 = new APIAction();
        $a2->addRequestMethod('POST');
        $a2->setName('update-email');
        $a2->addParameter($userId);
        $a2->addParameter(new RequestParameter('email','email', FALSE));
        $a2->addParameter($pass);
        $a2->addParameter($tok);
        $this->addAction($a2,TRUE);
        //action #3
        $a3 = new APIAction();
        $a3->addRequestMethod('GET');
        $a3->setName('get-users');
        $a3->addParameter($tok);
        $this->addAction($a3,TRUE);
        
        $a6 = new APIAction();
        $a6->setActionMethod('POST');
        $a6->setName('update-user-status');
        $a6->addParameter($userId);
        $a6->addParameter(new RequestParameter('status','string', FALSE));
        $a6->addParameter($tok);
        $this->addAction($a6,TRUE);
        
        $a7 = new APIAction();
        $a7->addRequestMethod('POST');
        $a7->setName('update-display-name');
        $a7->addParameter($userId);
        $a7->addParameter(new RequestParameter('display-name','string', FALSE));
        $a7->addParameter($tok);
        $this->addAction($a7,TRUE);
        
        $a8 = new APIAction();
        $a8->addRequestMethod('POST');
        $a8->setName('update-access-level');
        $a8->addParameter($userId);
        $a8->addParameter(new RequestParameter('access-level','integer', FALSE));
        $a8->addParameter($tok);
        $this->addAction($a8,TRUE);
        
        $a9 = new APIAction();
        $a9->addRequestMethod('POST');
        $a9->setName('activate-account');
        $a9->addParameter(new RequestParameter('activation-token','string', FALSE));
        $a9->addParameter($tok);
        $this->addAction($a9,TRUE);
        
        $a10 = new APIAction();
        $a10->addRequestMethod('GET');
        $a10->setName('get-profile');
        $a10->addParameter($userId);
        $a10->addParameter($tok);
        $this->addAction($a10,TRUE);
    }
    /**
     * Called by the routing function to perform the 'update-staus' action
     * @since 1.0
     */
    private function updateStatus(){
        $inputs = $this->getInputs();
        if(isset($inputs['user-id'])){
            if(isset($inputs['status'])){
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
            else{
                $this->missingParam('status');
            }
        }
        else{
            $this->missingParam('user-id');
        }
    }
    /**
     * Called by the routing function to perform the 'update-display-name' action
     * @since 1.0
     */
    private function updateDisplayName(){
        $inputs = $this->getInputs();
        if(isset($inputs['user-id'])){
            if(isset($inputs['display-name'])){
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
            else{
                $this->missingParam('email');
            }
        }
        else{
            $this->missingParam('user-id');
        }
    }
    /**
     * Called by the routing function to perform the 'update-email' action
     * @since 1.0
     */
    private function updateEmail(){
        $inputs = $this->getInputs();
        if(isset($inputs['user-id'])){
            if(isset($inputs['email'])){
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
            else{
                $this->missingParam('email');
            }
        }
        else{
            $this->missingParam('user-id');
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
        if(isset($inputs['activation-token'])){
            $user = UserFunctions::get()->activateAccount($inputs['activation-token']);
            if($user instanceof User){
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
        else{
            $this->missingParam('activation-token');
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
     * Called by the routing function to perform the 'update-password' action
     * @since 1.0
     */
    public function updatePassword(){
        $input = $this->getInputs();
        if(isset($input['old-pass'])){
            if(isset($input['new-pass'])){
                if(isset($input['user-id'])){
                    $r = UserFunctions::get()->updatePassword($input['old-pass'], $input['new-pass'], $input['user-id']);
                    if($r === TRUE){
                        $this->sendResponse('Password Updated', FALSE, 200);
                    }
                    else if($r == MySQLQuery::QUERY_ERR){
                        $this->databaseErr();
                    }
                    else if($r == UserFunctions::NO_SUCH_USER){
                        $this->sendResponse('No Such User', TRUE, 404);
                    }
                    else if($r == UserFunctions::NOT_AUTH){
                        $this->notAuth();
                    }
                    else if($r == UserFunctions::PASSWORD_MISSMATCH){
                        $this->sendResponse('Password Missmatch', TRUE, 404);
                    }
                    else{
                        $this->sendResponse('Something Wrong', TRUE, 404);
                    }
                }
                else{
                    $this->missingParam('user-id');
                }
            }
            else{
                $this->missingParam('new-pass');
            }
        }
        else{
            $this->missingParam('old-pass');
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
        else if($action == 'update-password'){
            $this->updatePassword();
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
        if(isset($inputs['access-level'])){
            if(isset($inputs['user-id'])){
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
            else{
                $this->missingParam('user-id');
            }
        }
        else{
            $this->missingParam('access-level');
        }
    }
    /**
     * Called by the routing function to perform the 'add-user' action
     * @since 1.0
     */
    private function addUser(){
        $inputs = $this->getInputs();
        if(isset($inputs['username'])){
            if(isset($inputs['email'])){
                if(isset($inputs['password'])){
                    if(isset($inputs['access-level'])){
                        $user = new User($inputs['username'], hash('sha256',$inputs['password']), $inputs['email']);
                        $user->setAccessLevel($inputs['access-level']);
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
                    else{
                        $this->missingParam('access-level');
                    }
                }
                else{
                    $this->missingParam('password');
                }
            }
            else{
                $this->missingParam('email');
            }
        }
        else{
            $this->missingParam('username');
        }
    }

    public function isAuthorized() {
        return SessionManager::get()->validateToken();
    }
}
$api = new UserAPIs();
$api->process();
