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
namespace webfiori\entity\mail;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 404 Not Found");
    die('<!DOCTYPE html><html><head><title>Not Found</title></head><body>'
    . '<h1>404 - Not Found</h1><hr><p>The requested resource was not found on the server.</p></body></html>');
}
/**
 * A class that represents an email account which is used to send or receive messages.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class SMTPAccount {
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
     * A boolean value that is set to TRUE if connection uses TLS.
     * @var boolean
     * @since 1.0.1 
     */
    private $useTls;
    /**
     * A boolean value that is set to TRUE if connection uses SSL.
     * @var boolean
     * @since 1.0.1 
     */
    private $useSsl;
    /**
     * Creates new instance of the class.
     * @since 1.0.1
     */
    public function __construct() {
        //$this->isSSL(FALSE);
        //$this->isTLS(FALSE);
        $this->setPort(465);
        $this->setUsername('');
        $this->setPassword('');
        $this->setServerAddress('');
        $this->setName('');
        $this->setAddress('');
    }
    /**
     * Sets the address of the email server.
     * @param string $addr The address of the email server (such as 'mail.example.com').
     * @since 1.0
     */
    public function setServerAddress($addr){
        $this->emailServerAddress = trim($addr);
    }
    /**
     * Sets or gets the value of the property 'useTls'.
     * @param boolean|NULL $bool TRUE if the connection to the server will use TLS. 
     * FALSE if not. If NULL is given, the property will not updated. Default 
     * is NULL.
     * @return boolean $bool TRUE if the connection to the server will use TLS. 
     * FALSE if not. Default return value is FALSE
     * @since 1.0.1
     */
//    public function isTLS($bool=null){
//        if($bool !== NULL){
//            $this->useTls = $bool === TRUE ? TRUE : FALSE;
//            if($this->useTls){
//                $this->useSsl = FALSE;
//            }
//        }
//        return $this->useTls;
//    }
    /**
     * Sets or gets the value of the property 'useSsl'.
     * @param boolean|NULL $bool [Optional] TRUE if the connection to the server will use SSL. 
     * FALSE if not. If NULL is given, the property will not updated. Default 
     * is NULL.
     * @return boolean $bool TRUE if the connection to the server will use SSL. 
     * FALSE if not. Default return value is FALSE
     * @since 1.0.1
     */
//    public function isSSL($bool=null){
//        if($bool !== NULL){
//            $this->useSsl = $bool === TRUE ? TRUE : FALSE;
//            if($this->useSsl){
//                $this->useTls = FALSE;
//            }
//        }
//        return $this->useSsl;
//    }
    /**
     * Sets the username that is used to access email server.
     * @param string $u The username that is used to access email server.
     * @since 1.0
     */
    public function setUsername($u){
        $this->userName = trim($u);
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
        $this->address = trim($address);
    }
    /**
     * Sets the name of the email account.
     * @param string $name The name of the account (such as 'Programming Team'). 
     * The name is used when sending an email message using the given SMTP account. 
     * The name will be used in the header 
     * 'FROM' when sending an email
     * @since 1.0
     */
    public function setName($name) {
        $this->name = trim($name);
    }
    /**
     * Returns the username that is used to access email server.
     * @return string The username that is used to access email server. Default 
     * is empty string.
     * @since 1.0
     */
    public function getUsername(){
        return $this->userName;
    }
    /**
     * Returns the password of the user account that is used to access email server.
     * @return string The password of the user account that is used to access email server. 
     * default is empty string.
     * @since 1.0
     */
    public function getPassword() {
        return $this->password;
    }
    /**
     * Returns the email address.
     * @return string The email address which will be used in the header 
     * 'FROM' when sending an email. Default is empty string.
     * @since 1.0
     */
    public function getAddress() {
        return $this->address;
    }
    /**
     * Returns the name of the email account.
     * @return string The name of the email account. The name will be used in the header 
     * 'FROM' when sending an email. Default is empty string.
     * @since 1.0
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Returns The address of the email server.
     * @return string The address of the email server (such as 'mail.example.com'). 
     * Default is empty string.
     * @since 1.0
     */
    public function getServerAddress() {
        return $this->emailServerAddress;
    }
    /**
     * Returns the port number of email server.
     * @return int The port number of email server. Default is 465.
     * @since 1.0
     */
    public function getPort() {
        return $this->port;
    }
    /**
     * Sets the port number of email server.
     * @param int $port The port number of email server such as 25. It will 
     * be only set if the given value is an integer and it is greater than 0.
     * @since 1.0
     */
    public function setPort($port){
        if(gettype($port) == 'integer' && $port > 0){
            $this->port = $port;
        }
    }
}
