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
    
    private function initAPI(){
        $this->setVirsion('1.0.0');
        $tok = new RequestParameter('token','string', TRUE);
        $userId = new RequestParameter('user-id','integer', FALSE);
        $pass = new RequestParameter('password','string', FALSE);
        //action #1
        $a1 = new APIAction();
        $a1->setActionMethod('POST');
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
        $a2->setActionMethod('POST');
        $a2->setName('update-email');
        $a2->addParameter($userId);
        $a2->addParameter(new RequestParameter('email','email', FALSE));
        $a2->addParameter($pass);
        $a2->addParameter($tok);
        $this->addAction($a2,TRUE);
        //action #3
        $a3 = new APIAction();
        $a3->setActionMethod('GET');
        $a3->setName('get-users');
        $a3->addParameter($tok);
        $this->addAction($a3,TRUE);
        
        //action #4
        $a4 = new APIAction();
        $a4->setActionMethod('GET');
        $a4->setName('get-user');
        $a4->addParameter($userId);
        $a4->addParameter($tok);
        $this->addAction($a4,TRUE);
        
        //action #5
        $a5 = new APIAction();
        $a5->setActionMethod('POST');
        $a5->setName('update-password');
        $a5->addParameter($userId);
        $a5->addParameter(new RequestParameter('old-pass','string', FALSE));
        $a5->addParameter(new RequestParameter('new-pass','string', FALSE));
        $a5->addParameter($tok);
        $this->addAction($a5,TRUE);
        
        $a6 = new APIAction();
        $a6->setActionMethod('POST');
        $a6->setName('update-user-status');
        $a6->addParameter($userId);
        $a6->addParameter(new RequestParameter('status','string', FALSE));
        $a6->addParameter($tok);
        $this->addAction($a6,TRUE);
        
        $a7 = new APIAction();
        $a7->setActionMethod('POST');
        $a7->setName('update-display-name');
        $a7->addParameter($userId);
        $a7->addParameter(new RequestParameter('display-name','string', FALSE));
        $a7->addParameter($tok);
        $this->addAction($a7,TRUE);
        
        $a8 = new APIAction();
        $a8->setActionMethod('POST');
        $a8->setName('update-access-level');
        $a8->addParameter($userId);
        $a8->addParameter(new RequestParameter('access-level','integer', FALSE));
        $a8->addParameter($tok);
        $this->addAction($a8,TRUE);
        
        $a9 = new APIAction();
        $a9->setActionMethod('POST');
        $a9->setName('activate-account');
        $a9->addParameter(new RequestParameter('activation-token','string', FALSE));
        $a9->addParameter($tok);
        $this->addAction($a9,TRUE);
    }
    
    public function checkAction($inputs){
        $action = parent::getAction();
        if($action == 'add-user'){
            $this->actionNotImpl();
        }
        else if($action == 'get-users'){
            $this->actionNotImpl();
        }
        else if($action == 'get-user'){
            $this->actionNotImpl();
        }
        else if($action == 'update-email'){
            $this->actionNotImpl();
        }
        else if($action == 'update-password'){
            $this->actionNotImpl();
        }
        else if($action == 'update-display-name'){
            $this->actionNotImpl();
        }
        else if($action == 'update-access-level'){
            $this->actionNotImpl();
        }
        else if($action == 'update-user-status'){
            $this->actionNotImpl();
        }
        else if($action == 'activate-account'){
            $this->actionNotImpl();
        }
    }
    public function addUser($inputs){
        if(isset($inputs['username'])){
            if(isset($inputs['email'])){
                if(isset($inputs['password'])){
                    if(isset($inputs['access-level'])){
                        $user = new User($inputs['username'], hash('sha256',$inputs['password']), $inputs['email']);
                        $r = UserFunctions::register($user);
                        if($r == MySQLQuery::QUERY_ERR){
                            $this->databaseErr();
                        }
                        else if($r == UserFunctions::USERNAME_TAKEN){

                        }
                        else if($r == UserFunctions::USER_ALREAY_REG){

                        }
                        else if($r == UserFunctions::EMPTY_STRING){

                        }
                        else{
                            http_response_code(201);
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
    
}
$api = new UserAPIs();
$api->process('checkAction');
