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
 * A file that contains system emails configurations.
 *
 * @author Ibrahim
 * @version 1.0
 */
class MailConfig {
    private $emailAccounts;
    /**
     *
     * @var MailConfig 
     * @since 1.0
     */
    private static $inst;
    /**
     * 
     * @return MailConfig
     * @since 1.0
     */
    public static function get(){
        if(self::$inst !== NULL){
            return self::$inst;
        }
        self::$inst = new MailConfig();
        return self::$inst;
    }
    
    private function __construct() {
        $acc1 = new EmailAccount();
        $acc1->setAddress('mail.programmingacademia.com');
        $acc1->setUsername('no-replay@programmingacademia.com');
        $acc1->setPassword('132970');
        $acc1->setName('Programming Academia Team');
        $this->addAccount($acc1, 'no-replay');
    }
    /**
     * Adds an email account.
     * @param EmailAccount $acc an object of type <b>EmailAccount</b>.
     * @param string $name A name to associate with the email account.
     * @since 1.0
     */
    private function addAccount($acc,$name){
        $this->emailAccounts[$name] = $acc;
    }
    /**
     * Returns an email account given its name.
     * @param string $name The name of the account.
     * @return EmailAccount|null If the account is found, The function 
     * will return an object of type <b>EmailAccount</b>. Else, the 
     * function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getAccount($name){
        if(isset($this->emailAccounts[$name])){
            return $this->emailAccounts[$name];
        }
        return NULL;
    }
    /**
     * Returns an array that contains all email accounts.
     * @return array An array that contains all email accounts.
     * @since 1.0
     */
    public function getAccounts(){
        return $this->emailAccounts;
    }
}
class EmailAccount {
    private $address;
    private $userName;
    private $password;
    private $name;
    private $emailServerAddress;
    
    public function setServerAddress($addr){
        $this->emailServerAddress = $addr;
    }


    public function setUsername($u){
        $this->userName = $u;
    }
    
    public function setPassword($pass) {
        $this->password = $pass;
    }
    
    public function setAddress($address) {
        $this->address = $address;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getUsername(){
        return $this->userName;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getServerAddress() {
        return $this->emailServerAddress;
    }
}
