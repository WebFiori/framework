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
 * The base class for creating application logic and connecting to the database.
 *
 * @author Ibrahim
 * @version 1.2
 */
class Functions {
    /**
     * A constant that indicates a user is not authorized to perform specific 
     * function.
     * @var string 
     * @since 1.0
     */
    const NOT_AUTH = 'not_autherized';
    /**
     * A constant that indicates a given method parameter is an empty string.
     * @var string Constant that indicates a given method parameter is an empty string.
     * @since 1.0
     */
    const EMPTY_STRING = 'emp_string';
    public function __construct() {
        $this->mainSession = new SessionManager('pa-session');
        $this->mainSession->initSession();
    }
    /**
     * A function that must be called after session is started to 
     * initiate database connection.
     * @since 1.1
     */
    public function useDatabase() {
        $systemStatus = LisksCode::sysStatus();
        if($systemStatus == Util::DB_NEED_CONF && 
                !defined('SETUP_MODE')){
            header('content-type:application/json');
            http_response_code(500);
            die('{"message":"'.$systemStatus.'","type":"error",'
                    . '"details":"It seems the system is unable to connect to the database.",'
                    . '"db-instance:'.Util::getDatabaseTestInstance()->toJSON().'}');
        }
        $result = $this->mainSession->useDb(array(
            'host'=>Config::get()->getDBHost(),
            'user'=>Config::get()->getDBUser(),
            'pass'=>Config::get()->getDBPassword(),
            'db-name'=> Config::get()->getDBName()
        ));
        if($result !== TRUE && !defined('SETUP_MODE')){
            header('content-type:application/json');
            die($this->mainSession->getDBLink()->toJSON());
        }
        else if($result !== TRUE && defined('SETUP_MODE')){
            return FALSE;
        }
    }
    /**
     * A session manager.
     * @var SessionManager an instance of 'SessionManager'.
     * @since 1.0 
     */
    private $mainSession;
    /**
     * Execute a query.
     * @param MySQLQuery $qObj An object of type 'MySQLQuery'.
     * @return boolean 'TRUE' if no errors occur while executing the query.
     * FAlSE in case of error.
     * @since 1.0
     */
    public function excQ($qObj){
        $this->useDatabase();
        if($this->mainSession->getDBLink() != NULL){
            return $this->mainSession->getDBLink()->executeQuery($qObj);
        }
        return FALSE;
    }
    /**
     * Checks if the current session user has a privilege or not given privilege 
     * ID.
     * @param string $pId The ID of the privilege.
     * @return boolean If the user has the given privilege, the function will 
     * return TRUE. If the user does not have the privilege, the function will 
     * return FALSE.
     * @since 1.2
     */
    public function hasPrivilege($pId){
        if($this->getUserID() != -1){
            return $this->mainSession->getUser()->hasPrivilege($pId);
        }
        return FALSE;
    }
    /**
     * Returns the instance of <b>SessionManager</b> that is used by the logic.
     * @return SessionManager An object of type <b>SessionManager</b>
     * @since 1.0
     */
    public function getMainSession(){
        return $this->mainSession;
    }
    /**
     * Returns language code from the session manager.
     * @param boolean $forceUpdate If set to TRUE, language code will 
     * be forced to update based on the value of the attribute 'lang' 
     * of a GET or POST request or a cookie.
     * @return strint A two characters  that represents language code.
     * @since 1.2
     */
    public final function getSessionLang($forceUpdate=true){
        return $this->getMainSession()->getLang($forceUpdate);
    }
    /**
     * Returns the link that is used to connect to the database.
     * @return DatabaseLink The link that is used to connect to the database.
     * @since 1.2
     */
    public function getDBLink() {
        return $this->getMainSession()->getDBLink();
    }
    /**
     * Returns the number of rows resulted from executing a query.
     * @return int|NULL Number of rows resulted from executing a query. The 
     * function will return <b>-1</b> in case no connection was established to 
     * the database.
     * @since 1.0
     */
    public function rows(){
        if($this->mainSession->getDBLink() != NULL){
            return $this->getMainSession()->getDBLink()->rows();
        }
        return -1;
    }
    /**
     * Returns A row that is resulted from executing a query.
     * @return array| NULL An array that contains row info. The 
     * function will return NULL in case no connection was established to 
     * the database.
     * @since 1.0
     */
    public function getRow(){
        if($this->getMainSession()->getDBLink() != NULL){
            $row = $this->getMainSession()->getDBLink()->getRow();
            return $row;
        }
        return NULL;
    }
    /**
     * Returns the ID of the user who is currently logged in.
     * @return int The ID of the user who is currently logged in. The 
     * function will return -1 in case no user is logged in.
     * @since 1.0
     */
    public function getUserID(){
        if($this->getMainSession()->getUser() != NULL){
            return intval($this->getMainSession()->getUser()->getID());
        }
        return -1;
    }
}
