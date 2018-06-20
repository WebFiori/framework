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
 * Password reset controller.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class PasswordFunctions extends Functions{
    /**
     * An object that is used to construct password reset related queries.
     * @var PasswordResetQuery
     * @since 1.0 
     */
    private $resetQuery;
    private static $singleton;
    /**
     * 
     * @return PasswordFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton !== NULL){
            return self::$singleton;
        }
        self::$singleton = new PasswordFunctions();
        return self::$singleton;
    }
    
    public function __construct() {
        parent::__construct();
        parent::useDatabase();
        $this->resetQuery = new PasswordResetQuery();
    }
    /**
     * Request a password reset for a user given his email address.
     * @param string $emailAddress The email address of the user.
     * @return boolean | string The function will return <b>TRUE</b> if 
     * a password reset request was created. If a database error happens, 
     * the function will return <b>MySQLQuery::QUERY_ERR</b>. If 
     * no user was found which has the given email address, the function will 
     * return <b>UserFunctions::NO_SUCH_USER</b>.
     * @since 1.0
     */
    public function passwordForgotten($emailAddress) {
        $user = UserFunctions::get()->getUserByEmail($emailAddress);
        if($user instanceof User){
            $resetTok = hash('sha256', date(DATE_ISO8601).$user->getID().$user->getRegDate());
            $user->setResetToken($resetTok);
            $this->resetQuery->add($user);
            if($this->excQ($this->resetQuery)){
                MailFunctions::get()->sendPasswordChangeConfirm($user);
                return TRUE;
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        else{
            return UserFunctions::NO_SUCH_USER;
        }
    }
}
