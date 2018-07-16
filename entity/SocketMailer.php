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
 * A class that can be used to send email messages using sockets.
 *
 * @author Ibrahim
 * @version 1.2
 */
class SocketMailer {
    const NL = "\r\n";
    /**
     * The resource that is used to fire commands
     * @var resource 
     */
    private $conn;
    /**
     * A boolean that is set to true if authentication succeeded.
     * @var boolean
     * @since 1.2 
     */
    private $isLoggedIn;
    /**
     * The name of mail server host.
     * @var string 
     */
    private $host;
    /**
     * The port number.
     * @var int 
     */
    private $port;
    /**
     * The username that is used to login to the mail server.
     * @var string 
     */
    private $uName;
    /**
     * The password that is used in authentication.
     * @var string 
     */
    private $pass;
    /**
     * Connection timeout (in minutes)
     * @var int 
     */
    private $timeout;
    /**
     * An associative array of mail receivers. Key represents 
     * receiver name and value represents email address.
     * @var array
     */
    private $receivers;
    /**
     * An associative array of mail receivers (Carbon Copy). Key represents 
     * receiver name and value represents email address.
     * @var array 
     */
    private $cc;
    /**
     * An associative array of mail receivers (Blind Carbon Copy). Key represents 
     * receiver name and value represents email address.
     * @var array 
     */
    private $bcc;
    /**
     * The email address of the sender.
     * @var string 
     */
    private $senderAddress;
    /**
     * The name of the sender.
     * @var string 
     */
    private $senderName;
    /**
     * The subject of the email message.
     * @var string 
     */
    private $subject;
    /**
     * An array that contains server messages.
     * @var array 
     */
    private $log;
    /**
     * If set to true, this means user is in message body writing mode.
     * @var boolean 
     */
    private $writeMode;
    public function __construct() {
        $this->log = array();
        array_push($this->log, 'Creating new instance of SocketMailer.');
        $this->setTimeout(5);
        $this->receivers = array();
        $this->cc = array();
        $this->bcc = array();
        $this->setSubject('EMAIL MESSAGE');
        $this->writeMode = FALSE;
        $this->isLoggedIn = FALSE;
    }
    /**
     * Checks if the user is logged in or not.
     * @return boolean The function will return <b>TRUE</b> if the user is 
     * logged in to the mail server. <b>FALSE</b> if not.
     * @since 1.2
     */
    public function isLoggedIn() {
        return $this->isLoggedIn;
    }
    /**
     * Authenticate the user given email server username and password. Authentication 
     * must be done after connecting to the server.
     * @param string $username The email server username.
     * @param string $password The user password.
     * @return boolean The function will return <b>TRUE</b> if the user is 
     * logged in to the mail server. <b>FALSE</b> if not. The user might not be logged 
     * in in 3 cases:
     * <ul>
     * <li>If the mailer is not connected to the email server.</li>
     * <li>If the sender address is not set.</li>
     * <li>If the given username and password are incorrect.</li>
     * </ul>
     * @since 1.2
     */
    public function login($username,$password) {
        if($this->isConnected()){
            if(strlen($this->getSenderAddress()) != 0){
                array_push($this->log, 'Validating user credentials.');
                $this->sendC('AUTH LOGIN');
                $this->sendC(base64_encode($username));
                $this->sendC(base64_encode($password));
                //a command to check if authentication is done
                $this->sendC('MAIL FROM: <'.$this->getSenderAddress().'>');

                if($this->getLastLogMessage() == 'Response: 235 Authentication succeeded'){
                    array_push($this->log, 'Logged in. Valid credentials.');
                    $this->isLoggedIn = TRUE;
                }
                else{
                    array_push($this->log, 'Unable to login. Invalid credentials.');
                    $this->isLoggedIn = FALSE;
                }
            }
            else{
                array_push($this->log, 'Unable to login. Sender not set.');
            }
        }
        else{
            array_push($this->log, 'Unable to login. No connection available.');
        }
        return $this->isLoggedIn();
    }
    /**
     * Returns the last logged message after executing some command.
     * @return string The last logged message after executing some command.
     * @since 1.2
     */
    public function getLastLogMessage(){
        $count = count($this->getLog());
        if($count == 0){
            return '';
        }
        return $this->getLog()[$count - 1];
    }
    /**
     * Returns log messages.
     * @return array An array that contains all logged messages.
     * @ince 1.0
     */
    public function getLog() {
        return $this->log;
    }
    /**
     * Sets the subject of the message.
     * @param string $subject Email subject.
     * @since 1.0
     */
    public function setSubject($subject){
        $this->subject = $subject;
        array_push($this->log, 'Subject Updated to: '.$subject);
    }
    /**
     * Sets the name and the address of the sender.
     * @param string $name The name of the sender.
     * @param string $address The email address of the sender.
     * @since 1.0
     */
    public function setSender($name, $address){
        $this->senderName = $name;
        $this->senderAddress = $address;
        array_push($this->log, 'Sender set to: "'.$name.'" \''.$address.'\'');
    }
    /**
     * Sets the login username.
     * @param string $u Username.
     * @since 1.0
     */
    public function setUsername($u){
        $this->uName = $u;
        array_push($this->log, 'Username set to: "'.$u.'"');
    }
    /**
     * Sets user password.
     * @param string $pass User password.
     * @since 1.0
     */
    public function setPassword($pass){
        $this->pass = $pass;
        array_push($this->log, 'Password is set.');
    }
    /**
     * Adds new receiver.
     * @param string $name The name of the email receiver (such as 'Ibrahim').
     * @param string $address The email address of the receiver.
     * @param boolean $isCC [Optional] If set to true, the receiver will receive 
     * a carbon copy of the message.
     * @param boolean $isBcc [Optional] If set to true, the receiver will receive 
     * a blind carbon copy of the message.
     * @since 1.0
     */
    public function addReceiver($name, $address, $isCC=false, $isBcc=false){
        if($isBcc){
            $this->bcc[$name] = $address;
            array_push($this->log, 'BCC: "'.$name.'" \''.$address.'\'');
        }
        else if($isCC){
            $this->cc[$name] = $address;
            array_push($this->log, 'CC: "'.$name.'" \''.$address.'\'');
        }
        else{
            $this->receivers[$name] = $address;
            array_push($this->log, 'Receiver: "'.$name.'" \''.$address.'\'');
        }
    }
    /**
     * Checks if the mailer is in message writing mode or not.
     * @return boolean <b>TRUE</b> if the mailer is in writing mode. The 
     * mailer will only switch to writing mode after sending the command 'DATA'.
     * @since 1.1
     */
    public function isInWritingMode(){
        return $this->writeMode;
    }
    /**
     * Returns the name of message sender.
     * @return string The name of the sender.
     * @since 1.1
     */
    public function getSenderName(){
        return $this->senderName;
    }
    /**
     * Returns the email address of the sender.
     * @return string The email address of the sender.
     * @since 1.1
     */
    public function getSenderAddress(){
        return $this->senderAddress;
    }
    /**
     * Write a message to the buffer.
     * @param string $msg The message to write. 
     * @param boolean $sendMessage If set to <b>TRUE</b>, The connection will be closed and the 
     * message will be sent.
     * @since 1.0
     */
    public function write($msg,$sendMessage=false){
        array_push($this->log, '');
        if($this->isInWritingMode()){
            $this->sendC($msg);
            if($sendMessage === TRUE){
                $this->sendC(self::NL.'.');
                $this->sendC('QUIT');
            }
        }
        else{
            if(strlen($this->getSenderAddress()) != 0){
                array_push($this->log, 'Switching to message writing mode.');
                foreach ($this->receivers as $val){
                    $this->sendC('RCPT TO: <'.$val.'>');
                }
                foreach ($this->cc as $val){
                    $this->sendC('RCPT TO: <'.$val.'>');
                }
                foreach ($this->bcc as $val){
                    $this->sendC('RCPT TO: <'.$val.'>');
                }
                $this->sendC('DATA');
                $this->sendC('From: "'.$this->getSenderName().'" <'.$this->getSenderAddress().'>');
                $this->sendC('To: '.$this->getTo());
                $this->sendC('CC: '.$this->getCC());
                $this->sendC('BCC: '.$this->getBcc());
                $this->sendC('Date:'. date('r (T)'));
                $this->sendC('Subject:'. $this->subject);
                $this->sendC('MIME-Version: 1.0');
                $this->sendC('Content-Type: text/html; charset=UTF-8');
                $this->sendC($msg);
                if($sendMessage === TRUE){
                    $this->sendC(self::NL.'.');
                    $this->sendC('QUIT');
                }
            }
            else{
                array_push($this->log, 'Unable to switch to message writing mode. Sender address not set.');
            }
        }
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function getBcc(){
        $arr = array();
        foreach ($this->bcc as $name => $address){
            array_push($arr, $name.' <'.$address.'>');
        }
        return implode(',', $arr);
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function getCC(){
        $arr = array();
        foreach ($this->cc as $name => $address){
            array_push($arr, $name.' <'.$address.'>');
        }
        return implode(',', $arr);
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function getTo(){
        $arr = array();
        foreach ($this->receivers as $name => $address){
            array_push($arr, $name.' <'.$address.'>');
        }
        return implode(',', $arr);
    }
    /**
     * Checks if the connection is still open or is it closed.
     * @return boolean <b>TRUE</b> if the connection is open.
     * @since 1.0
     */
    public function isConnected() {
        return is_resource($this->conn);
    }
    /**
     * Sets the connection port.
     * @param int $port The port number to set.
     * @since 1.0
     */
    public function setPort($port) {
        if($port > 0){
            $this->port = $port;
            array_push($this->log, 'Port set to: '.$port);
        }
    }
    /**
     * 
     * @return int
     * @since 1.0
     */
    public function getTimeout(){
        return $this->timeout;
    }

    /**
     * Sets the name of mail server host.
     * @param string $host The name of the host (such as mail.mysite.com).
     * @since 1.0
     */
    public function setHost($host){
        $this->host = $host;
        array_push($this->log, 'Host set to: '.$host);
    }
    /**
     * Sends a command to the mail server.
     * @param type $command
     * @return boolean
     * @since 1.0
     */
    public function sendC($command){
        if($this->isConnected()){
            if($this->isInWritingMode()){
                fwrite($this->conn, $command.self::NL);
                array_push($this->log, 'Writing: '.$command);
                if($command == self::NL.'.'){
                    $this->writeMode = FALSE;
                    array_push($this->log, 'End of writing mode.');
                }
            }
            else{
                array_push($this->log, 'Sending the command: '.$command);
                fwrite($this->conn, $command.self::NL);
                array_push($this->log, trim('Response: '.$this->read()));
                if($command == 'DATA'){
                    $this->writeMode = TRUE;
                    array_push($this->log, 'Switched to writing mode');
                }
            }
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    public function read(){
        $message = '';
        while(!feof($this->conn)){
            $str = fgets($this->conn);
            $message .= $str;
            if (!isset($str[3]) or (isset($str[3]) and $str[3] == ' ')) {
                break;
            }
        }
        return $message;
    }
    /**
     * Connect to the mail server.
     * @return boolean <b>TRUE</b> if the connection established or already 
     * connected. <b>FALSE</b> if not. Once the connection is established, the 
     * function will send the command 'EHLO' to the server. 
     * @since 1.0
     */
    public function connect() {
        if(!$this->isConnected()){
            set_error_handler(function(){});
            $err = 0;
            $errStr = '';
            $this->conn = fsockopen($this->host, $this->port, $err, $errStr, $this->timeout*60);
            set_error_handler(NULL);
            if(is_resource($this->conn)){
                array_push($this->log, 'Connected');
                $this->sendC('EHLO');
                return TRUE;
            }
            else{
                array_push($this->log, 'Unable to connect. Check your connection parameters.');
                return FALSE;
            }
        }
        return TRUE;
    }
    /**
     * Sets the timeout time of the connection.
     * @param int $val The value of timeout (in minutes). The timeout will be updated 
     * only if the connection is not yet established and the given value is grater 
     * than 0.
     */
    public function setTimeout($val) {
        if($val >= 1 && !$this->isConnected()){
            array_push($this->log, 'Timeout set to '.$val.' minutes.');
            $this->timeout = $val;
        }
    }
}
