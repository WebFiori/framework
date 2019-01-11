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
use webfiori\conf\MailConfig;
use webfiori\entity\File;
use webfiori\entity\Logger;
use webfiori\functions\BasicMailFunctions;
use phpStructs\html\HTMLDoc;
use phpStructs\html\HTMLNode;
use Exception;
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
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 * @version 1.0.2
 */
class EmailMessage {
    /**
     *
     * @var HTMLDoc 
     * @since 1.0 
     */
    private $asHtml;
    /**
     *
     * @var SocketMailer
     * @since 1.0 
     */
    private $socketMailer;
    /**
     *
     * @var EmailMessage 
     * @since 1.0
     */
    private static $em;
    /**
     * Creates new email message.
     * @param string $sendAccountName The name of SMTP account that will be used 
     * to send the message. The account must exist in the file 'MailConfig.php'. 
     * If it does not exist, an exception will be thrown. The name of the account 
     * must be supplied only for the first call.
     * @return EmailMessage
     * @since 1.0
     */
    public static function &createInstance($sendAccountName=''){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if an instance of the class is already active...');
        if(self::$em === NULL){
            Logger::log('No instance is active. Creating new one...');
            self::$em = new EmailMessage($sendAccountName);
        }
        Logger::log('Returning class intance.');
        Logger::logFuncReturn(__METHOD__);
        return self::$em;
    }
    /**
     * Creates new instance of the class.
     * @param type $sendAccountName
     * @return type
     * @throws Exception
     * @since 1.0
     */
    private function __construct($sendAccountName='') {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Creating new instance of \'EmailMessage\'.', 'info');
        if(class_exists('webfiori\conf\MailConfig')){
            Logger::log('Checking the existance of the account \''.$sendAccountName.'\'.', 'debug');
            $acc = MailConfig::getAccount($sendAccountName);
            if($acc instanceof SMTPAccount){
                Logger::log('SMTP Account retrieved.');
                Logger::log('Getting socket mailer ready.');
                $this->socketMailer = BasicMailFunctions::get()->getSocketMailer($acc);
                if($this->socketMailer == BasicMailFunctions::INV_CREDENTIALS){
                    Logger::log('Unable to login to the email server using provided parameters. An exception is thrown.', 'error');
                    Logger::requestCompleted();
                    throw new Exception('The account "'.$sendAccountName.'" has inalid credintials.');
                }
                else if($this->socketMailer == BasicMailFunctions::INV_HOST_OR_PORT){
                    Logger::log('Unable to connect to the email server. Incorrect port or server address. An exception is thrown.', 'error');
                    Logger::requestCompleted();
                    throw new Exception('The account "'.$sendAccountName.'" has inalid host or port number. Port: '.$acc->getPort().', Host: '.$acc->getServerAddress().'.');
                }
                else{
                    Logger::log('Instance created with no errors.');
                    Logger::logFuncReturn(__METHOD__);
                    $this->asHtml = new HTMLDoc();
                    return;
                }
            }
            Logger::log('No email account with the name \'MailConfig\' was found. An exception is thrown.', 'error');
            Logger::requestCompleted();
            throw new Exception('The account "'.$sendAccountName.'" does not exist.');
        }
        Logger::log('Class \'MailConfig\' is missing. An exception is thrown.', 'error');
        Logger::requestCompleted();
        throw new Exception('Class "MailConfig" not found.');
    }
    /**
     * Adds a file to the email message as an attachment.
     * @param File $file The file that will be added. It will be added only if the file 
     * exist in the path or the raw data of the file is set.
     * @since 1.0
     */
    public static function attach($file) {
        self::createInstance()->_getSocketMailer()->addAttachment($file);
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a carbon copy of the message.
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * @return array An array that contains receivers information.
     * @since 1.0.2
     */
    public static function getCC(){
        return self::createInstance()->_getSocketMailer()->getCC();
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a blind carbon copy of the message.
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * @return array An array that contains receivers information.
     * @since 1.0.2
     */
    public static function getBCC() {
        return self::createInstance()->_getSocketMailer()->getBCC();
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive an original copy of the message.
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * @return array An array that contains receivers information.
     * @since 1.0.2
     */
    public function getReceivers() {
        return self::createInstance()->_getSocketMailer()->getReceivers();
    }
    /**
     * Adds a text node to the body of the message.
     * @param string $text The text that will be in the body of the node.
     * @since 1.0
     */
    public static function write($text) {
        self::createInstance()->_getDocument()->addChild(HTMLNode::createTextNode($text));
    }
    /**
     * Adds a child HTML node to the body of the message.
     * @param HTMLNode $htmlNode An instance of 'HTMLNode'.
     * @since 1.0
     */
    public static function insertNode($htmlNode) {
        self::createInstance()->_getDocument()->addChild($htmlNode);
    }
    /**
     * Sets or gets the importance level of email message.
     * @param int $imp The importance level of the message. -1 for not urgent, 0 
     * for normal and 1 for urgent.
     * @return int The importance level of the message.
     * @since 1.0.1
     */
    public static function importance($imp=null) {
        if($imp !== NULL){
            self::createInstance()->_getSocketMailer()->setPriority($imp);
        }
        return self::createInstance()->_getSocketMailer()->getPriority();
    }
    /**
     * Sets or returns the HTML document that is associated with the email 
     * message.
     * @param HTMLDoc $new If it is not NULL, the HTML document 
     * that is associated with the message will be set to the given one.
     * @return HTMLDoc The document that is associated with the email message.
     * @since 1.0
     */
    public static function document($new=null){
        if($new != NULL){
            self::createInstance()->_setDocument($new);
        }
        return self::createInstance()->_getDocument();
    }
    /**
     * Adds new receiver address to the list of message receivers.
     * @param string $name The name of the email receiver (such as 'Ibrahim').
     * @param string $email The email address of the receiver (such as 'example@example.com').
     * @param boolean $isCC If set to TRUE, the receiver will receive 
     * a carbon copy of the message (CC).
     * @param boolean $isBcc If set to TRUE, the receiver will receive 
     * a blind carbon copy of the message (Bcc).
     * @since 1.0
     */
    public static function addReciver($name,$email,$isCC=false,$isBcc=false){
        self::createInstance()->_getSocketMailer()->addReceiver($name, $email, $isCC, $isBcc);
    }
    /**
     * Sets the subject of the email message.
     * @param string $subject The subject of the email message.
     * @since 1.0
     */
    public static function subject($subject) {
        self::createInstance()->_getSocketMailer()->setSubject($subject);
    }
    /**
     * Sends the message and set message instance to NULL.
     * @since 1.0
     */
    public static function send(){
        self::createInstance()->_sendMessage();
        self::$em = NULL;
    }
    /**
     * @since 1.0
     */
    private function _sendMessage() {
        $this->socketMailer->write($this->asHtml->toHTML(), TRUE);
    }
    /**
     * 
     * @return SocketMailer
     * @since 1.0
     */
    private function &_getSocketMailer() {
        return $this->socketMailer;
    }
    /**
     * 
     * @return HTMLDoc
     * @since 1.0
     */
    private function &_getDocument() {
        return $this->asHtml;
    }
    /**
     * 
     * @param HTMLDoc $doc
     */
    private function _setDocument($doc) {
        if($doc instanceof HTMLDoc){
            $this->asHtml = $doc;
        }
    }
}
