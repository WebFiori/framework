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
        $a2->addParameter(new RequestParameter('email', 'email'));
        $this->addAction($a2);
        
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
                if($i['user-id'] == PasswordFunctions::get()->getUserID()){
                    return TRUE;
                }
            }
        }
        else{
            return TRUE;
        }
        return FALSE;
    }
    
    public function resetPassword() {
        $i = $this->getInputs();
        if($i['new-password'] == $i['conf-new-password']){
            $r = PasswordFunctions::get()->resetPassword($i['email'], $i['reset-token'], $i['new-password']);
            if($r === TRUE){
                $this->sendResponse('Password Updated');
            }
            else if($r == MySQLQuery::QUERY_ERR){
                $this->databaseErr();
            }
            else{
                $this->sendResponse($r, TRUE, 404);
            }
        }
        else{
            $this->sendResponse('The given two passwords do not match.', TRUE, 404);
        }
    }
    /**
     * Called by the routing function to perform the 'update-password' action
     * @since 1.0
     */
    public function updatePassword(){
        $input = $this->getInputs();
        $r = PasswordFunctions::get()->updatePassword($input['old-pass'], $input['new-pass'], $input['user-id']);
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
    public function processRequest() {
        $action = $this->getAction();
        if($action == 'update-password'){
            $this->updatePassword();
        }
        else if($action == 'reset-password'){
            $this->resetPassword();
        }
        else if($action == 'forgot-password'){
            $r = PasswordFunctions::get()->passwordForgotten($this->getInputs()['email']);
            if($r === MySQLQuery::QUERY_ERR){
                $this->databaseErr(PasswordFunctions::get()->getMainSession()->getDBLink()->toJSON());
            }
            else{
                $this->sendResponse('Reset Request Created');
            }
        }
    }
    
    
}
if(defined('API_CALL') && API_CALL === TRUE){
    $a = new PasswordAPIs();
    $a->process();
}
