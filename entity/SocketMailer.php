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
 * A class that can be used to send email messages using sockets.
 *
 * @author Ibrahim
 * @version 1.1
 */
class SocketMailer {
    const NL = "\r\n";
    /**
     * The resource that is used to fire commands
     * @var resource 
     */
    private $conn;
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
        $this->setTimeout(5);
        $this->receivers = array();
        $this->cc = array();
        $this->bcc = array();
        $this->log = array();
        $this->subject = 'EMAIL MESSAGE';
        $this->writeMode = FALSE;
    }
    /**
     * Returns log messages.
     * @return array
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
     */
    public function write($msg,$sendMessage=false){
        if($this->isInWritingMode()){
            $this->sendC($msg);
            if($sendMessage === TRUE){
                $this->sendC(self::NL.'.');
                $this->sendC('QUIT');
            }
        }
        else{
            $this->sendC('MAIL FROM: <'.$this->senderAddress.'>');
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
                array_push($this->log, 'Response: '.$this->read());
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
     * 
     * @return boolean
     * @since 1.0
     */
    public function connect() {
        if(!$this->isConnected()){
            $err = 0;
            $errStr = '';
            $this->conn = fsockopen($this->host, $this->port, $err, $errStr, $this->timeout*60);
            return is_resource($this->conn);
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
            $this->timeout = $val;
        }
    }
}
