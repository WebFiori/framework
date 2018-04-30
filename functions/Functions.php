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
 * The base class for creating application logic.
 *
 * @author Ibrahim
 * @version 1.1
 */
class Functions {
    /**
     * A constant that indicates a user is not authorized to perform specific 
     * action.
     * @var string 
     * @since 1.0
     */
    const NOT_AUTH = 'not_autherized';
    
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
        $result = $this->mainSession->useDb(array(
            'host'=>Config::get()->getDBHost(),
            'user'=>Config::get()->getDBUser(),
            'pass'=>Config::get()->getDBPassword(),
            'db-name'=> Config::get()->getDBName()
        ));
        if($result !== TRUE){
            header('content-type:application/json');
            die($this->mainSession->getDBLink()->toJSON());
        }
    }
    /**
     * A session manager.
     * @var SessionManager an instance of <b>SessionManager</b>.
     * @since 1.0 
     */
    private $mainSession;
    /**
     * Execute a query.
     * @param MySQLQuery $qObj An object of type <b>MySQLQuery</b>.
     * @return boolean <b>TRUE</b> if no errors occur while executing the query.
     *  <b>FAlSE</b> in case of error.
     * @since 1.0
     */
    public function excQ($qObj){
        if($this->mainSession->getDBLink() != NULL){
            return $this->mainSession->getDBLink()->executeQuery($qObj);
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
     * Returns the number of rows resulted from executing a query.
     * @return int|NULL Number of rows resulted from executing a query. The 
     * function will return <b>NULL</b> in case no connection was established to 
     * the database.
     * @since 1.0
     */
    public function rows(){
        if($this->mainSession->getDBLink() != NULL){
            return $this->getMainSession()->getDBLink()->rows();
        }
        return NULL;
    }
    /**
     * Returns A row that is resulted from executing a query.
     * @return array| NULL An array that contains row info. The 
     * function will return <b>NULL</b> in case no connection was established to 
     * the database.
     * @since 1.0
     */
    public function getRow(){
        if($this->getMainSession()->getDBLink() != NULL){
            return $this->getMainSession()->getDBLink()->getRow();
        }
        return NULL;
    }
    /**
     * Returns the ID of the user who is currently logged in.
     * @return int|NULL The ID of the user who is currently logged in. The 
     * function will return <b>NULL</b> in case no user is logged in.
     * @since 1.0
     */
    public function getUserID(){
        if($this->getMainSession()->getUser() != NULL){
            return $this->getMainSession()->getUser()->getID();
        }
        return NULL;
    }
    /**
     * Returns the access level of the logged in user.
     * @return int|NULL The access level of the logged in user. The 
     * function will return <b>NULL</b> in case no user is logged in.
     * @since 1.0
     */
    public function getAccessLevel(){
        if($this->getMainSession()->getUser() != NULL){
            return $this->getMainSession()->getUser()->getAccessLevel();
        }
        return NULL;
    }
}
