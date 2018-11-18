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
 * @version 1.4.3
 */
class SocketMailer {
    /**
     * The priority of the message. Affects 
     * @var int
     * @since 1.4.3
     * @see https://tools.ietf.org/html/rfc4021#page-33
     */
    private $priority;
    /**
     * A constant that colds the possible values for the header 'Priority'. 
     * @since 1.4.3
     * @see https://tools.ietf.org/html/rfc4021#page-33
     */
    const PRIORITIES = array(
        -1=>'non-urgent',
        0=>'normal',
        1=>'urgent'
    );
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
     * receiver address and value represents his name.
     * @var array
     */
    private $receivers;
    /**
     * An associative array of mail receivers (Carbon Copy). Key represents 
     * receiver address and the value represents his name.
     * @var array 
     */
    private $cc;
    /**
     * An associative array of mail receivers (Blind Carbon Copy). Key represents 
     * receiver address and the value represents his name.
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
     * If set to true, this means user is in message body writing mode.
     * @var boolean 
     */
    private $writeMode;
    /**
     * A boundary variable used to separate email message parts.
     * @var string
     * @since 1.3 
     */
    private $boundry;
    /**
     * An array that contains an objects of type 'File'. 
     * @var array 
     * @since 1.3
     */
    private $attachments;
    /**
     * The last message that was sent by email server.
     * @var string
     * @since 1.4 
     */
    private $lastResponse;
    /**
     * A boolean value that is set to TRUE if connection uses TLS.
     * @var boolean
     * @since 1.4.1 
     */
    private $useTls;
    /**
     * A boolean value that is set to TRUE if connection uses SSL.
     * @var boolean
     * @since 1.4.1 
     */
    private $useSsl;
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    public function __construct() {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Creating new instance of SocketMailer.');
        $this->setTimeout(5);
        $this->receivers = array();
        $this->cc = array();
        $this->bcc = array();
        $this->setSubject('EMAIL MESSAGE');
        $this->writeMode = FALSE;
        $this->isLoggedIn = FALSE;
        $this->boundry = hash('sha256', date(DATE_ISO8601));
        $this->attachments = array();
        $this->lastResponse = '';
        $this->useTls = FALSE;
        $this->setPriority(0);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the priority of the message.
     * @param int $priority The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent.
     * @since 1.4.3
     */
    public function setPriority($priority){
        if($priority == -1 || $priority == 0 || $priority == 1){
            $this->priority = $priority;
        }
    }
    /**
     * Returns the priority of the message.
     * @return int The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. Default value is 0.
     * @since 1.4.3
     */
    public function getPriority() {
        return $this->priority;
    }
    /**
     * Adds new attachment to the message.
     * @param File $attachment An object of type 'File' which contains all 
     * needed information about the file. It will be added only if the file 
     * exist in the path or the raw data of the file is set.
     * @since 1.3
     */
    public function addAttachment($attachment) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if class \'File\' exist...');
        if(class_exists('File')){
            Logger::log('Checking if passed parameter is an instance of \'File\'...');
            if($attachment instanceof File){
                Logger::log('Checking file path and row data...');
                if(file_exists($attachment->getAbsolutePath()) || file_exists(str_replace('\\', '/', $attachment->getAbsolutePath())) || $attachment->getRawData() !== NULL){
                    $this->attachments[] = $attachment;
                    Logger::log('Attachment added.');
                    Logger::logFuncReturn(__METHOD__);
                    return TRUE;
                }
                else{
                    Logger::log('Attachment not added. No file in the path and no raw data.', 'warning');
                }
            }
            else{
                Logger::log('Attachment not added. Given parameter is not an instance of \'File\'.', 'warning');
            }
        }
        Logger::logFuncReturn(__METHOD__);
        return FALSE;
    }
    /**
     * Sets or gets the value of the property 'useTls'.
     * @param boolean|NULL $bool [Optional] TRUE if the connection to the server will use TLS. 
     * FALSE if not. If NULL is given, the property will not updated. Default 
     * is NULL.
     * @return boolean $bool TRUE if the connection to the server will use TLS. 
     * FALSE if not. Default return value is FALSE
     * @since 1.0.1
     */
    public function isTLS($bool=null){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if NULL is given...');
        if($bool !== NULL){
            Logger::log('Not NULL. Updating property $useTls...');
            $this->useTls = $bool === TRUE ? TRUE : FALSE;
            if($this->useTls){
                Logger::log('Not NULL. Updating property $useSsl...');
                $this->useSsl = FALSE;
            }
        }
        else{
            Logger::log('No need to update.');
        }
        Logger::logReturnValue($this->useTls);
        Logger::logFuncReturn(__METHOD__);
        return $this->useTls;
    }
    /**
     * Sets or gets the value of the property 'useSsl'.
     * @param boolean|NULL $bool [Optional] TRUE if the connection to the server will use SSL. 
     * FALSE if not. If NULL is given, the property will not updated. Default 
     * is NULL.
     * @return boolean $bool TRUE if the connection to the server will use SSL. 
     * FALSE if not. Default return value is FALSE
     * @since 1.0.1
     */
    public function isSSL($bool=null){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if NULL is given...');
        if($bool !== NULL){
            Logger::log('Not NULL. Updating property $useSsl...');
            $this->useSsl = $bool === TRUE ? TRUE : FALSE;
            if($this->useSsl){
                Logger::log('Not NULL. Updating property $useTls...');
                $this->useTls = FALSE;
            }
        }
        else{
            Logger::log('No need to update.');
        }
        Logger::logReturnValue($this->useSsl);
        Logger::logFuncReturn(__METHOD__);
        return $this->useSsl;
    }
    /**
     * Checks if the user is logged in or not.
     * @return boolean The function will return TRUE if the user is 
     * logged in to the mail server. FALSE if not.
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
     * @return boolean The function will return TRUE if the user is 
     * logged in to the mail server. FALSE if not. The user might not be logged 
     * in in 3 cases:
     * <ul>
     * <li>If the mailer is not connected to the email server.</li>
     * <li>If the sender address is not set.</li>
     * <li>If the given username and password are incorrect.</li>
     * </ul>
     * @since 1.2
     */
    public function login($username,$password) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if connected to mail server...');
        if($this->isConnected()){
            Logger::log('Connected.');
            Logger::log('Checking if connected to mail server...');
            if(strlen($this->getSenderAddress()) != 0){
                Logger::log('Validating user credentials...');
                $this->sendC('AUTH LOGIN');
                $this->sendC(base64_encode($username));
                $this->sendC(base64_encode($password));
                
                Logger::log('Checking if authentication is success or not.');
                //a command to check if authentication is done
                $this->sendC('MAIL FROM: <'.$this->getSenderAddress().'>');

                if($this->getLastLogMessage() == '235 Authentication succeeded' || $this->getLastLogMessage() == '250 OK'){
                    Logger::log('Logged in. Valid credentials.');
                    $this->isLoggedIn = TRUE;
                }
                else{
                    Logger::log('Unable to login. Invalid credentials.','warning');
                    $this->isLoggedIn = FALSE;
                }
            }
            else{
                Logger::log('Unable to login. Sender not set.','warning');
            }
        }
        else{
            Logger::log('Unable to login. No connection available.','warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return $this->isLoggedIn();
    }
    /**
     * Returns the last logged message after executing some command.
     * @return string The last logged message after executing some command.
     * @since 1.2
     */
    public function getLastLogMessage(){
        return $this->lastResponse;
    }
    /**
     * Sets the subject of the message.
     * @param string $subject Email subject.
     * @since 1.0
     */
    public function setSubject($subject){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Validating message title...');
        Logger::log('New subject: \''.$subject.'\'.', 'debug');
        if(gettype($subject) == 'string' && strlen($subject) > 0){
            $this->subject = $subject;
            Logger::log('Subject updated.');
        }
        else{
            Logger::log('Invalid email subject.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the name and the address of the sender.
     * @param string $name The name of the sender.
     * @param string $address The email address of the sender.
     * @since 1.0
     */
    public function setSender($name, $address){
        Logger::logFuncCall(__METHOD__);
        Logger::log('New sender name: \''.$name.'\'.', 'debug');
        Logger::log('New sender address: \''.$address.'\'.', 'debug');
        $this->senderName = $name;
        $this->senderAddress = $address;
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the login username.
     * @param string $u Username.
     * @since 1.0
     */
    public function setUsername($u){
        Logger::logFuncCall(__METHOD__);
        Logger::log('New username: \''.$u.'\'.', 'debug');
        $this->uName = $u;
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets user password.
     * @param string $pass User password.
     * @since 1.0
     */
    public function setPassword($pass){
        Logger::logFuncCall(__METHOD__);
        Logger::log('New password: \''.$pass.'\'.', 'debug');
        $this->pass = $pass;
        Logger::logFuncReturn(__METHOD__);
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
        Logger::logFuncCall(__METHOD__);
        Logger::log('Name: \''.$name.'\'.', 'debug');
        Logger::log('Address: \''.$address.'\'.', 'debug');
        Logger::log('Is CC: \''.$isCC.'\'.', 'debug');
        Logger::log('Is Bcc: \''.$isBcc.'\'.', 'debug');
        if($isBcc){
            $this->bcc[$address] = $name;
            Logger::log('Receiver will receive the message as Bcc.');
        }
        else if($isCC){
            $this->cc[$address] = $name;
            Logger::log('Receiver will receive the message as CC.');
        }
        else{
            $this->receivers[$address] = $name;
            Logger::log('Receiver will receive the message directly.');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Checks if the mailer is in message writing mode or not.
     * @return boolean TRUE if the mailer is in writing mode. The 
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
     * @param boolean $sendMessage If set to TRUE, The connection will be closed and the 
     * message will be sent.
     * @since 1.0
     */
    public function write($msg,$sendMessage=false){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Message = \''.$msg.'\'.', 'debug');
        Logger::log('Send Message = \''.$sendMessage.'\'.', 'debug');
        Logger::log('Checking if in writing mode.');
        if($this->isInWritingMode()){
            Logger::log('In writing mode.');
            $this->sendC($msg);
            Logger::log('Checking if message must be sent.');
            if($sendMessage === TRUE){
                Logger::log('Must be sent. Appending attachments (if any).');
                $this->_appendAttachments();
                $this->sendC(self::NL.'.');
                $this->sendC('QUIT');
                Logger::log('Message sent.');
            }
            else{
                Logger::log('No need to send now.');
            }
        }
        else{
            Logger::log('Not in writing mode.');
            Logger::log('Checking sender address validity...');
            Logger::log('Sender address = \''.$this->getSenderAddress().'\'', 'debug');
            if(strlen($this->getSenderAddress()) != 0){
                Logger::log('Valid sender address.');
                Logger::log('Switching to message writing mode.');
                Logger::log('Adding message receivers...');
                foreach ($this->receivers as $address => $name){
                    $this->sendC('RCPT TO: <'.$address.'>');
                }
                foreach ($this->cc as $address => $name){
                    $this->sendC('RCPT TO: <'.$address.'>');
                }
                foreach ($this->bcc as $address => $name){
                    $this->sendC('RCPT TO: <'.$address.'>');
                }
                Logger::log('Finished.');
                $this->sendC('DATA');
                $priorityAsInt = $this->getPriority();
                $priorityHeaderVal = self::PRIORITIES[$priorityAsInt];
                if($priorityAsInt == -1){
                    $importanceHeaderVal = 'low';
                }
                else if($priorityAsInt == 1){
                    $importanceHeaderVal = 'High';
                }
                else{
                    $importanceHeaderVal = 'normal';
                }
                $this->sendC('Priority: '.$priorityHeaderVal);
                $this->sendC('Content-Transfer-Encoding: quoted-printable');
                $this->sendC('Importance: '.$importanceHeaderVal);
                $this->sendC('From: "'.$this->getSenderName().'" <'.$this->getSenderAddress().'>');
                $this->sendC('To: '.$this->getTo());
                $this->sendC('CC: '.$this->getCC());
                $this->sendC('BCC: '.$this->getBcc());
                $this->sendC('Date:'. date('r (T)'));
                $this->sendC('Subject:'. $this->subject);
                $this->sendC('MIME-Version: 1.0');
                $this->sendC('Content-Type: multipart/mixed; boundary="'.$this->boundry.'"'.self::NL);
                $this->sendC('--'.$this->boundry);
                $this->sendC('Content-Type: text/html; charset="UTF-8"'.self::NL);
                $this->sendC($msg);
                Logger::log('Checking if message must be sent.');
                if($sendMessage === TRUE){
                    Logger::log('Must be sent. Appending attachments (if any).');
                    $this->_appendAttachments();
                    $this->sendC(self::NL.'.');
                    $this->sendC('QUIT');
                    Logger::log('Message sent.');
                }
                else{
                    Logger::log('No need to send now.');
                }
            }
            else{
                Logger::log('Unable to switch to message writing mode. Sender address not set.','error');
            }
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * A function that is used to include email attachments.
     * @since 1.3
     */
    private function _appendAttachments(){
        Logger::logFuncCall(__METHOD__);
        if(count($this->attachments) != 0){
            foreach ($this->attachments as $file){
                if($file->read()){
                    $fileSize = $file->getSize();
                    $content = $file->getRawData();
                    $contentChunk = chunk_split(base64_encode($content));
                    $this->sendC('--'.$this->boundry);
                    $this->sendC('Content-Type: '.$file->getFileMIMEType().'; name="'.$file->getName().'"');
                    $this->sendC('Content-Transfer-Encoding: base64');
                    $this->sendC('Content-Disposition: attachment; filename="'.$file->getName().'"'.self::NL);
                    $this->sendC($contentChunk);
                }
                else{
                    $content = $file->getRawData();
                    $fileSize = strlen($content);
                    $contentChunk = chunk_split(base64_encode($content));
                    $this->sendC('--'.$this->boundry);
                    $this->sendC('Content-Type: '.$file->getFileMIMEType().'; name="'.$file->getName().'"');
                    $this->sendC('Content-Transfer-Encoding: base64');
                    $this->sendC('Content-Disposition: attachment; filename="'.$file->getName().'"'.self::NL);
                    $this->sendC($contentChunk);
                }
            }
            $this->sendC('--'.$this->boundry.'--');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function getBcc(){
        $arr = array();
        foreach ($this->bcc as $address => $name){
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
        foreach ($this->cc as $address => $name){
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
        foreach ($this->receivers as $address => $name){
            array_push($arr, $name.' <'.$address.'>');
        }
        return implode(',', $arr);
    }
    /**
     * Checks if the connection is still open or is it closed.
     * @return boolean TRUE if the connection is open.
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
        Logger::logFuncCall(__METHOD__);
        Logger::log('Updating port number.');
        Logger::log('New port number: \''.$port.'\'.', 'debug');
        if($port > 0){
            Logger::log('Port number updated.');
            $this->port = $port;
        }
        else{
            Logger::log('Invalid port number.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
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
        Logger::logFuncCall(__METHOD__);
        Logger::log('New host address: \''.$host.'\'.', 'debug');
        $this->host = $host;
        Logger::log('Host address updated.');
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sends a command to the mail server.
     * @param type $command
     * @return boolean
     * @since 1.0
     */
    public function sendC($command){
        Logger::logFuncCall(__METHOD__);
        if($this->isConnected()){
            if($this->isInWritingMode()){
                fwrite($this->conn, $command.self::NL);
                Logger::log('Writing content...');
                Logger::log('Content = \''.$command.'\'.', 'debug');
                if($command == self::NL.'.'){
                    Logger::log('End of message writing mode.');
                    $this->writeMode = FALSE;
                }
            }
            else{
                Logger::log('Sending the command \''.$command.'\'.');
                fwrite($this->conn, $command.self::NL);
                $response = trim($this->read());
                Logger::log('Server response: '.$response);
                $this->lastResponse = $response;
                Logger::log('Checking if the command is \'DATA\'.');
                if($command == 'DATA'){
                    $this->writeMode = TRUE;
                    Logger::log('Switched to message writing mode');
                }
                else{
                    Logger::log('No need to switch to message writing mode');
                }
            }
            Logger::logFuncReturn(__METHOD__);
            return TRUE;
        }
        else{
            Logger::log('No command executed since not connected.', 'warning');
            Logger::logFuncReturn(__METHOD__);
            return FALSE;
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    public function read(){
        Logger::logFuncCall(__METHOD__);
        $message = '';
        Logger::log('Reading server response...');
        while(!feof($this->conn)){
            $str = fgets($this->conn);
            $message .= $str;
            if (!isset($str[3]) or (isset($str[3]) and $str[3] == ' ')) {
                break;
            }
        }
        Logger::log('Reading finished.');
        Logger::logFuncReturn(__METHOD__);
        return $message;
    }
    /**
     * Connect to the mail server.
     * @return boolean TRUE if the connection established or already 
     * connected. FALSE if not. Once the connection is established, the 
     * function will send the command 'EHLO' to the server. 
     * @since 1.0
     */
    public function connect() {
        Logger::logFuncCall(__METHOD__);
        $retVal = TRUE;
        Logger::log('Checking if already connected...');
        if(!$this->isConnected()){
            Logger::log('Not connected. Trying to connect.');
            set_error_handler(function(){});
//            Logger::log('Checking if SSL or TLS will be used...');
            $port = $this->port;
//            if($port == 465){
//                Logger::log('SSL will be used.');
//            }
//            else if($port == 587){
//                Logger::log('TLS will be used.');
//            }
            $err = 0;
            $errStr = '';
            //$protocol = $port == 465 ? "ssl://" : '';
            Logger::log('Trying to connect to \''.$this->host.'\' at port '.$port.'...');
            if(function_exists('stream_socket_client')){
                Logger::log('Connecting using \'stream_socket_client\'.');
                $context = stream_context_create (array(
                    'ssl'=>array(
                        'verify_peer'=>FALSE,
                        'verify_peer_name'=>FALSE,
                        'allow_self_signed'=>TRUE
                    )
                ));
                $this->conn = stream_socket_client($this->host.':'.$port, $err, $errStr, $this->timeout*60, STREAM_CLIENT_CONNECT, $context);
            }
            else{
                Logger::log('Connecting using \'fsockopen\'.');
                $this->conn = fsockopen($this->host, $port, $err, $errStr, $this->timeout*60);
            }
            set_error_handler(NULL);
            if(is_resource($this->conn)){
                Logger::log('Connected.');
                Logger::log('Reading server response...');
                $response = $this->read();
                Logger::log('Server response: \''.$response.'\'');
                Logger::log('Sending the command \'EHLO\'.');
                if($this->sendC('EHLO '.$this->host)){
                    $retVal = TRUE;
                    if($port == 587){
                        //Logger::log('Using TLS. Sending the command \'STARTTLS\'.');
//                        if($this->sendC('STARTTLS')){
//                            $retVal = stream_socket_enable_crypto($this->conn, TRUE, STREAM_CRYPTO_METHOD_ANY_CLIENT);
//                            if($retVal === TRUE){
//                                Logger::log('Secure connection enabled.');
//                                $this->sendC('EHLO '.$this->host);
//                            }
//                            else{
//                                Logger::log('Unable to make secure connection.','error');
//                            }
//                        }
//                        else{
//                            Logger::log('Error while sending the command \'STARTTLS\'.','error');
//                        }
                    }
                    else if($port == 465){
//                        Logger::log('SSL will be used.');
//                        $retVal = stream_socket_enable_crypto($this->conn, TRUE, STREAM_CRYPTO_METHOD_ANY_CLIENT);
//                        if($retVal === TRUE){
//                            Logger::log('Secure connection enabled.');
//                            $this->sendC('EHLO '.$this->host);
//                        }
//                        else{
//                            Logger::log('Unable to make secure connection.','error');
//                        }
                    }
                    else{
                        //Logger::log('No secure connection will be used.');
                        //$retVal = TRUE;
                    }
                }
                
            }
            else{
                Logger::log('Unable to connect. Check your connection parameters.','error');
                Logger::log('Error code: '.$err.'.');
                Logger::log('Error message: '.$errStr.'.');
                $retVal = FALSE;
            }
        }
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Sets the timeout time of the connection.
     * @param int $val The value of timeout (in minutes). The timeout will be updated 
     * only if the connection is not yet established and the given value is grater 
     * than 0.
     */
    public function setTimeout($val) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Setting timeout to \''.$val.'\'', 'debug');
        if($val >= 1 && !$this->isConnected()){
            $this->timeout = $val;
            Logger::log('Timeout updated.');
        }
        else{
            Logger::log('Invalid timeout time: \''.$val.'\'.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
}
