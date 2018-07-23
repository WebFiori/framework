<?php

/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
 * Description of AccessControlAPIs
 *
 * @author ibrah
 */
class AccessControlAPIs extends API{
    public function __construct() {
        parent::__construct('1.0.0');
        $a1 = new APIAction('get-privileges');
        $a1->addRequestMethod('get');
        $a1->addParameter(new RequestParameter('group-id', 'string',TRUE));
        $a1->getParameterByName('group-id')->setDefault('ALL');
        $this->addAction($a1,TRUE);
        
        $a2 = new APIAction('get-user-privileges');
        $a2->addParameter(new RequestParameter('user-id', 'string'));
        $a2->addRequestMethod('get');
        $this->addAction($a2,TRUE);
        
        $a3 = new APIAction('update-user-privileges');
        $a3->addRequestMethod('post');
        $a3->addParameter(new RequestParameter('user-id', 'string'));
        $a3->addParameter(new RequestParameter('privileges-string', 'string'));
        $this->addAction($a3, TRUE);
        
        $a14 = new APIAction('get-privileges-groups');
        $a14->addRequestMethod('get');
        $this->addAction($a14, TRUE);
        $this->process();
    }
    public function isAuthorized() {
        $action = $this->getAction();
        if($action == 'get-privileges'){
            return SystemFunctions::get()->hasPrivilege('GET_PRIVILEGES');
        }
        else if($action == 'update-user-privileges'){
            return SystemFunctions::get()->hasPrivilege('UPDATE_USER_PERMISSIONS');
        }
        else if($action == 'get-privileges-groups'){
            return SystemFunctions::get()->hasPrivilege('GET_PRIVILEGES');
        }
        else if($action == 'get-user-privileges'){
            return SystemFunctions::get()->hasPrivilege('GET_PRIVILEGES');
        }
    }
    private function getPrivileges() {
        $groupId = $this->getInputs()['group-id'];
        if($groupId == 'ALL'){
            $privilegesArr = Access::privileges();
            $jsonArr = array();
            foreach ($privilegesArr as $pr){
                $j = new JsonX();
                $j->add('id', $pr->getID());
                $j->add('name', $pr->getName());
                $jsonArr[] = $j;
            }
            $ret = new JsonX();
            $ret->addArray('privileges', $jsonArr, FALSE);
            $this->send('application/json', $ret);
        }
        else{
            $privilegesArr = Access::privileges($groupId);
            $jsonArr = array();
            foreach ($privilegesArr as $pr){
                $j = new JsonX();
                $j->add('id', $pr->getID());
                $j->add('name', $pr->getName());
                $jsonArr[] = $j;
            }
            $ret = new JsonX();
            $ret->addArray('privileges', $jsonArr, FALSE);
            $this->send('application/json', $ret);
        }
    }
    
    private function getPrivilegesGroups() {
        $groups = Access::groups();
        $gArr = array();
        foreach ($groups as $group){
            $groupJ = new JsonX();
            $groupJ->add('name', $group->getName());
            $groupJ->add('id', $group->getID());
            $groupP = array();
            foreach ($group->privileges() as $p){
                $pJ = new JsonX();
                $pJ->add('name', $p->getName());
                $pJ->add('id', $p->getID());
                $groupP[] = $pJ;
            }
            $groupJ->addArray('privileges', $groupP, FALSE);
            $gArr[] = $groupJ;
        }
        $j = new JsonX();
        $j->addArray('privileges-groups', $gArr, FALSE);
        $this->send('application/json', $j);
    }
    private function getUserPrivileges(){
        $userId = $this->getInputs()['user-id'];
        $user = UserFunctions::get()->getUserByID($userId);
        if($user instanceof User){
            $privilegesArr = $user->privileges();
            $jsonArr = array();
            foreach ($privilegesArr as $pr){
                $j = new JsonX();
                $j->add('id', $pr->getID());
                $j->add('name', $pr->getName());
                $jsonArr[] = $j;
            }
            $ret = new JsonX();
            $ret->addArray('privileges', $jsonArr, FALSE);
            $this->send('application/json', $ret);
        }
        else if($user == MySQLQuery::QUERY_ERR){
            $this->databaseErr(SystemFunctions::get()->getDBLink()->toJSON());
        }
        else if($user == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($user == UserFunctions::NO_SUCH_USER){
            $this->sendResponse($user, TRUE, 404,'"details":"No user was found which has the given ID."');
        }
    }
    private function updatePrivileges() {
        $userId = $this->getInputs()['user-id'];
        $privilegesStr = $this->getInputs()['privileges-string'];
        $user = UserFunctions::get()->getUserByID($userId);
        if($user instanceof User){
            $user->removeAllPrivileges();
            Access::resolvePriviliges($privilegesStr, $user);
            $r = UserFunctions::get()->updateUserPrivileges($user);
            if($r === TRUE){
                $this->sendResponse('Privileges Updated.', FALSE, 200);
            }
            else if($r == Functions::NOT_AUTH){
                $this->notAuth();
            }
            else if($user == MySQLQuery::QUERY_ERR){
                $this->databaseErr(SystemFunctions::get()->getDBLink()->toJSON());
            }
        }
        else if($user == MySQLQuery::QUERY_ERR){
            $this->databaseErr(SystemFunctions::get()->getDBLink()->toJSON());
        }
        else if($user == UserFunctions::NOT_AUTH){
            $this->notAuth();
        }
        else if($user == UserFunctions::NO_SUCH_USER){
            $this->sendResponse($user, TRUE, 404,'"details":"No user was found which has the given ID."');
        }
    }
    public function processRequest() {
        $action = $this->getAction();
        if($action == 'get-privileges'){
            $this->getPrivileges();
        }
        else if($action == 'update-user-privileges'){
            $this->updatePrivileges();
        }
        else if($action == 'get-privileges-groups'){
            $this->getPrivilegesGroups();
        }
        else if($action == 'get-user-privileges'){
            $this->getUserPrivileges();
        }
    }

}
if(defined('API_CALL') && API_CALL === TRUE){
    $api = new AccessControlAPIs();
}
