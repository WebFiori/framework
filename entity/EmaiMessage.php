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
 * @version 1.0
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
        if(class_exists('MailConfig')){
            $acc = MailConfig::get()->getAccount($sendAccountName);
            if($acc instanceof EmailAccount){
                $this->socketMailer = BasicMailFunctions::get()->getSocketMailer($acc);
                if($this->socketMailer == BasicMailFunctions::INV_CREDENTIALS){
                    throw new Exception('The account "'.$sendAccountName.'" has inalid credintials.');
                }
                else if($this->socketMailer == BasicMailFunctions::INV_HOST_OR_PORT){
                    throw new Exception('The account "'.$sendAccountName.'" has inalid host or port number.');
                }
                else{
                    $this->asHtml = new HTMLDoc();
                    return;
                }
            }
            throw new Exception('The account "'.$sendAccountName.'" does not exist.');
        }
        throw new Exception('Class "MailConfig" not found.');
    }
    /**
     * 
     * @param File $file
     * @since 1.0
     */
    public static function attach($file) {
        self::createInstance()->getSocketMailer()->addAttachment($file);
    }
    /**
     * 
     * @param type $text
     * @since 1.0
     */
    public static function write($text) {
        self::createInstance()->getDocument()->addChild(HTMLNode::createTextNode($text));
    }
    /**
     * 
     * @param type $htmlNode
     * @since 1.0
     */
    public static function insertNode($htmlNode) {
        self::createInstance()->getDocument()->addChild($htmlNode);
    }
    /**
     * 
     * @param HTMLDoc $new
     * @since 1.0
     */
    public static function document($new=null){
        if($new != NULL){
            self::createInstance()->setDocument($new);
        }
        return self::createInstance()->getDocument();
    }
    /**
     * 
     * @param string $name The name of the email receiver (such as 'Ibrahim').
     * @param string $email The email address of the receiver.
     * @param boolean $isCC [Optional] If set to true, the receiver will receive 
     * a carbon copy of the message.
     * @param boolean $isBcc [Optional] If set to true, the receiver will receive 
     * a blind carbon copy of the message.
     * @since 1.0
     */
    public static function addReciver($name,$email,$isCC=false,$isBcc=false){
        self::createInstance()->getSocketMailer()->addReceiver($name, $email, $isCC, $isBcc);
    }
    /**
     * 
     * @param string $subject
     * @since 1.0
     */
    public static function subject($subject) {
        self::createInstance()->getSocketMailer()->setSubject($subject);
    }
    /**
     * @since 1.0
     */
    public static function send(){
        self::createInstance()->sendMessage();
    }
    /**
     * @since 1.0
     */
    private function sendMessage() {
        $this->socketMailer->write($this->asHtml->toHTML(), TRUE);
    }
    /**
     * 
     * @return SocketMailer
     * @since 1.0
     */
    private function &getSocketMailer() {
        return $this->socketMailer;
    }
    /**
     * 
     * @return HTMLDoc
     * @since 1.0
     */
    private function &getDocument() {
        return $this->asHtml;
    }
    /**
     * 
     * @param HTMLDoc $doc
     */
    private function setDocument($doc) {
        if($doc instanceof HTMLDoc){
            $this->asHtml = $doc;
        }
    }
}
