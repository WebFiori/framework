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

/**
 * An API used to give users access to system resources.
 *
 * @author Ibrahim
 * @version 1.0
 */
if(!defined('ROOT_DIR')){
    header('HTTP/1.1 403 Forbidden');
    exit;
}

class AuthAPI extends API{
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0.0');
        $a1 = new APIAction('login');
        $a1->setDescription('Grant the user the access to the system.');
        $a1->addRequestMethod('POST');
        $a1->addParameter(new RequestParameter('username', 'string'));
        $a1->getParameterByName('username')->setDescription('The user name of '
                . 'the user or his email address.');
        $a1->addParameter(new RequestParameter('password', 'string'));
        $a1->getParameterByName('password')->setDescription('The user account\'s password.');
        $a1->addParameter(new RequestParameter('session-duration', 'integer',TRUE));
        $a1->getParameterByName('session-duration')->setMinVal(0);
        $a1->getParameterByName('session-duration')->setDefault(5);
        $a1->getParameterByName('session-duration')->setDescription('The duration of '
                . 'the session in minutes before the user is automatically logged off the system. Default '
                . 'is 5 minutes.');
        $a1->addParameter(new RequestParameter('refresh-timeout', 'boolean',TRUE));
        $a1->getParameterByName('refresh-timeout')->setDefault('n');
        $a1->getParameterByName('refresh-timeout')->setDescription('If set to true, '
                . 'the session duration will be reset with every request the client sends. Default '
                . 'is false.');
        $this->addAction($a1);
        
        $a2 = new APIAction();
        $a2->addRequestMethod('POST');
        $a2->setName('logout');
        $this->addAction($a2);
    }
    
    public function processRequest() {
        $inputs = $this->getInputs();
        $action = $this->getAction();
        if($action == 'login'){
            $loggedId = UserFunctions::get()->getUserID();
            if($loggedId != -1){
                $this->sendResponse('A user is already logged in.', TRUE, 404);
                return;
            }
            if(isset($inputs['session-duration'])){
                $duration = $inputs['session-duration'];
            }
            else{
                $duration = 30;
            }
            if(isset($inputs['refresh-timeout'])){
                if($inputs['refresh-timeout'] == 'true'){
                    $refTimeout = TRUE;
                }
                else{
                    $refTimeout = FALSE;
                }
            }
            else{
                $refTimeout = FALSE;
            }
            $r = UserFunctions::get()->authenticate($inputs['username'], $inputs['password'], $inputs['username'],$duration,$refTimeout);
            if($r == TRUE){
                if(UserFunctions::get()->getMainSession()->getUser()->getStatus() == 'S'){
                    $this->sendResponse('Account Suspended',TRUE,401);
                    UserFunctions::get()->getMainSession()->kill();
                }
                else{
                    $this->sendResponse('Logged In', FALSE, 200, '"session":'.UserFunctions::get()->getMainSession()->toJSON());
                }
            }
            else if($r == MySQLQuery::QUERY_ERR){
                $this->databaseErr(UserFunctions::get()->getMainSession()->getDBLink()->getErrorMessage());
            }
            else{
                $this->sendResponse('Inncorect username, email or password.', TRUE, 401);
            }
        }
        else if($action == 'logout'){
            UserFunctions::get()->getMainSession()->kill();
            $this->sendResponse('Logged Out', FALSE, 200);
        }
    }

    public function isAuthorized() {
        return TRUE;
    }
}
$api = new AuthAPI();
$api->process();
