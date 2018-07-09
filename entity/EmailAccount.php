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
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * A class that represents an email account which is used to send or receive messages.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class EmailAccount {
    /**
     * Email address.
     * @var string 
     */
    private $address;
    /**
     * The user name that is used to login.
     * @var string
     * @since 1.0 
     */
    private $userName;
    /**
     * The password of the user account.
     * @var string
     * @since 1.0 
     */
    private $password;
    /**
     * The name of the email account.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * Server address of the email account.
     * @var string
     * @since 1.0 
     */
    private $emailServerAddress;
    /**
     * The port number that is used to access the email server.
     * @var int
     * @since 1.0 
     */
    private $port;
    /**
     * Sets the address of the email server.
     * @param string $addr The address of the email server (such as 'mail.example.com').
     * @since 1.0
     */
    public function setServerAddress($addr){
        $this->emailServerAddress = $addr;
    }
    /**
     * Sets the username that is used to access email server.
     * @param string $u The username that is used to access email server.
     * @since 1.0
     */
    public function setUsername($u){
        $this->userName = $u;
    }
    /**
     * Sets the password of the user account that is used to access email server.
     * @param string $pass The password of the user account that is used to access email server.
     * @since 1.0
     */
    public function setPassword($pass) {
        $this->password = $pass;
    }
    /**
     * Sets the email address.
     * @param string $address An email address.
     */
    public function setAddress($address) {
        $this->address = $address;
    }
    /**
     * Sets the name of the email account.
     * @param string $name The name of the account (such as 'Programming Team').
     * @since 1.0
     */
    public function setName($name) {
        $this->name = $name;
    }
    /**
     * Returns the username that is used to access email server.
     * @return string The username that is used to access email server.
     * @since 1.0
     */
    public function getUsername(){
        return $this->userName;
    }
    /**
     * Returns the password of the user account that is used to access email server.
     * @return string The password of the user account that is used to access email server.
     * @since 1.0
     */
    public function getPassword() {
        return $this->password;
    }
    /**
     * Returns the email address.
     * @return string The email address.
     * @since 1.0
     */
    public function getAddress() {
        return $this->address;
    }
    /**
     * Returns the name of the email account.
     * @return string The name of the email account.
     * @since 1.0
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Returns The address of the email server.
     * @return string The address of the email server (such as 'mail.example.com').
     * @since 1.0
     */
    public function getServerAddress() {
        return $this->emailServerAddress;
    }
    /**
     * Returns the port number of email server.
     * @return int The port number of email server.
     * @since 1.0
     */
    public function getPort() {
        return $this->port;
    }
    /**
     * Sets the port number of email server.
     * @param int $port The port number of email server such as 25.
     * @since 1.0
     */
    public function setPort($port){
        $this->port = $port;
    }
}
