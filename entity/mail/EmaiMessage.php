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
 * Description of EmaiMessage
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class EmaiMessage {
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
     * @var EmaiMessage 
     * @since 1.0
     */
    private static $em;
    /**
     * 
     * @return EmaiMessage
     * @since 1.0
     */
    public static function &createInstance($sendAccountName=''){
        if(self::$em != NULL){
            return self::$em;
        }
        self::$em = new EmaiMessage($sendAccountName);
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
        if(class_exists('MailConfig')){
            Logger::log('Checking the existance of the account \''.$sendAccountName.'\'.', 'debug');
            $acc = MailConfig::getAccount($sendAccountName);
            if($acc instanceof EmailAccount){
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
     * @param HTMLDoc $new [Optional] If it is not NULL, the HTML document 
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
     * @param boolean $isCC [Optional] If set to TRUE, the receiver will receive 
     * a carbon copy of the message (CC).
     * @param boolean $isBcc [Optional] If set to TRUE, the receiver will receive 
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
