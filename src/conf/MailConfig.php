<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
 
namespace webfiori\conf;
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
use webfiori\entity\mail\SMTPAccount;
/**
 * SMTP configuration class.
 * The developer can create multiple SMTP accounts and add 
 * Connection information inside the body of this class.
 * @author Ibrahim
 * @version 1.0
 */
class MailConfig{
    private $emailAccounts;
    /**
     *
     * @var MailConfig 
     * @since 1.0
     */
    private static $inst;
    /**
     * Return a single instance of the class.
     * Calling this method multiple times will result in returning 
     * the same instance every time.
     * @return MailConfig
     * @since 1.0
     */
    public static function &get(){
        if(self::$inst === null){
            self::$inst = new MailConfig();
        }
        return self::$inst;
    }
    private function __construct() {
    }
/**
     * Adds an email account.
     * The developer can use this method to add new account during runtime. 
     * The account will be removed once the program finishes.
     * @param EmailAccount $acc an object of type EmailAccount.
     * @param string $name A name to associate with the email account.
     * @since 1.0
     */
    private function addAccount($acc,$name){
        $this->emailAccounts[$name] = $acc;
    }
    private function &_getAccount($name){
        if(isset($this->emailAccounts[$name])){
            return $this->emailAccounts[$name];
        }
        $null = null;
        return $null;
    }
    /**
     * Returns an email account given its name.
     * The method will search for an account with the given name in the set 
     * of added accounts. If no account was found, null is returned.
     * @param string $name The name of the account.
     * @return EmailAccount|null If the account is found, The method 
     * will return an object of type EmailAccount. Else, the 
     * method will return null.
     * @since 1.0
     */
    public static function &getAccount($name){
        return self::get()->_getAccount($name);
    }
    private function _getAccounts(){
        return $this->emailAccounts;
    }
    /**
     * Returns an associative array that contains all email accounts.
     * The indices of the array will act as the names of the accounts. 
     * The value of the index will be an object of type EmailAccount.
     * @return array An associative array that contains all email accounts.
     * @since 1.0
     */
    public static function getAccounts(){
        return self::get()->_getAccounts();
    }
}
