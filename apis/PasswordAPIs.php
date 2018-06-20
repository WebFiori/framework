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
 * Description of PasswordAPIs
 *
 * @author Ibrahim
 */
class PasswordAPIs extends API{
    public function __construct() {
        parent::__construct();
        $a1 = new APIAction('forgot-password');
        $a1->setDescription('An API to call in case the user has forgotten his password.');
        $a1->addRequestMethod('post');
        $a1->addParameter(new RequestParameter('email', 'string'));
        $this->addAction($a1);
        
        $a2 = new APIAction('reset-password');
        $a2->addRequestMethod('post');
        $a2->addParameter(new RequestParameter('new-password', 'string'));
        $a2->addParameter(new RequestParameter('conf-new-password', 'string'));
        $a2->addParameter(new RequestParameter('reset-token', 'string'));
        
        $a3 = new APIAction();
        $a3->addRequestMethod('POST');
        $a3->setName('update-password');
        $a3->addParameter(new RequestParameter('user-id', 'int'));
        $a3->addParameter(new RequestParameter('old-pass','string'));
        $a3->addParameter(new RequestParameter('new-pass','string'));
        $a3->addParameter(new RequestParameter('conf-new-pass','string'));
        $a3->addParameter(new RequestParameter('token', 'string', TRUE));
        $this->addAction($a3,TRUE);
    }
    
    public function isAuthorized() {
        $action = $this->getAction();
        if($action == 'update-password'){
            if(PasswordFunctions::get()->getAccessLevel() == 0){
                return TRUE;
            }
            else{
                $i = $this->getInputs();
                if(isset($i['user-id'])){
                    if($i['user-id'] == PasswordFunctions::get()->getUserID()){
                        return TRUE;
                    }
                }
            }
        }
        else{
            return TRUE;
        }
    }

    public function processRequest() {
        $action = $this->getAction();
        if($action == 'update-password'){
            $this->actionNotImpl();
        }
        else if($action == 'reset-password'){
            $this->actionNotImpl();
        }
        else if($action == 'forgot-password'){
            $r = PasswordFunctions::get()->passwordForgotten($this->getInputs()['email']);
            if($r === MySQLQuery::QUERY_ERR){
                $this->databaseErr();
            }
            else{
                $this->sendResponse('Reset Request Created');
            }
        }
    }

}
$a = new PasswordAPIs();
$a->process();
