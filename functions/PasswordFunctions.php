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
     * Expiry time for a password reset token in minutes.
     * @var int
     * @since 1.0
     */
    const TOKEN_VALIDTY_TIME = 1440;
    /**
     * A constant that indicates a reset token is invalid.
     * @var string 
     * @since 1.0
     */
    const INV_TOKEN = 'inv_token';
    /**
     * A constant that indicates a reset token is not found.
     * @var string 
     * @since 1.0
     */
    const NO_SUCH_TOKEN = 'no_such_token';
    /**
     * An object that is used to construct password reset related queries.
     * @var PasswordResetQuery
     * @since 1.0 
     */
    private $resetQuery;
    /**
     *
     * @var UserQuery 
     * @since 1.0
     */
    private $userQuery;
    /**
     *
     * @var PasswordFunctions
     * @since 1.0 
     */
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
        $this->userQuery = new UserQuery();
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
            
            //used to remove a token 
            //if already exists
            $this->validateResetToken($this->getResetToken($user->getID()));
            
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
    /**
     * Reset user password given his email address and reset token.
     * @param string $email The email address of the user.
     * @param string $token Password reset token.
     * @param string $newPass The new user password.
     * @return boolean|string The function will return <b>TRUE</b> once the 
     * user password is changed. If a database error happens, 
     * the function will return <b>MySQLQuery::QUERY_ERR</b>. If the given token 
     * is invalid or the user has no reset token or the user 
     * does not exits, the function will return <b>PasswordFunctions::INV_TOKEN</b>.
     * @since 1.0
     */
    public function resetPassword($email,$token,$newPass) {
        if($this->validateResetToken($token) === TRUE){
            $user = UserFunctions::get()->getUserByEmail($email);
            if($user instanceof User){
                $resetToken = $this->getResetToken($user->getID());
                if($resetToken == MySQLQuery::QUERY_ERR || $resetToken == PasswordFunctions::INV_TOKEN){
                    return $resetToken;
                }
                else{
                    if($resetToken == $token){
                        $this->userQuery->updatePassword(hash(Authenticator::HASH_ALGO_NAME, $newPass), $user->getID());
                        if($this->excQ($this->userQuery)){
                            $this->resetQuery->removeByToken($token);
                            if($this->excQ($this->resetQuery)){
                                $count = $user->getResetCount();
                                $this->userQuery->updateLastPassResetTime($user->getID(), ++$count);
                                if($this->excQ($this->userQuery)){
                                    MailFunctions::get()->notifyOfPasswordChange($user);
                                    return TRUE;
                                }
                            }
                        }
                        return MySQLQuery::QUERY_ERR;
                    }
                    else{
                        return PasswordFunctions::INV_TOKEN;
                    }
                }
            }
        }
        return self::INV_TOKEN;
    }
    /**
     * Returns password reset token given his ID.
     * @param int $userId The ID of the user.
     * @return string The user reset token if found. If the given user 
     * does not have a reset token, the function will return 
     * <b>PasswordFunctions::INV_TOKEN</b>. If a database error 
     * occur, the function will return <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.0
     */
    private function getResetToken($userId) {
        $this->resetQuery->get($userId);
        if($this->excQ($this->resetQuery)){
            $row = $this->getRow();
            if($row != NULL){
                return $row[$this->resetQuery->getColName('reset-token')];
            }
            return PasswordFunctions::INV_TOKEN;
        }
        return MySQLQuery::QUERY_ERR;
    }
    /**
     * Checks if password reset token is valid or not.
     * @param string $token Password reset token.
     * @return boolean|string If the given token is valid, the function will return 
     * <b>TRUE</b>. A token is considered as valid if the time at which the token 
     * was created subtracted from current time 
     * is not greater than <b>PasswordFunctions::TOKEN_VALIDTY_TIME</b>. If a 
     * database error has occurred, the function will return 
     * <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.0
     */
    public function validateResetToken($token) {
        $this->resetQuery->getByResetToken($token);
        if($this->excQ($this->resetQuery)){
            $row = $this->getRow();
            if($row != NULL){
                $tokDate = strtotime($row[$this->resetQuery->getColName('request-time')]);
                $now = time();
                $passedMinutes = ($now - $tokDate)/60;
                if($passedMinutes > self::TOKEN_VALIDTY_TIME){
                    $this->resetQuery->removeByToken($token);
                    if($this->excQ($this->resetQuery)){
                        return FALSE;
                    }
                    else{
                        return MySQLQuery::QUERY_ERR;
                    }
                }
                else{
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
}
