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
namespace webfiori\framework\mail;

use webfiori\framework\ConfigController;
use webfiori\framework\exceptions\SMTPException;
use webfiori\framework\File;
use webfiori\framework\WebFioriApp;
use webfiori\ui\HTMLDoc;
use webfiori\ui\HTMLNode;
/**
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 * @version 1.0.5
 */
class EmailMessage {
    /**
     *
     * @var HTMLDoc 
     * @since 1.0 
     */
    private $asHtml;

    private $log;
    /**
     *
     * @var SocketMailer
     * @since 1.0 
     */
    private $socketMailer;
    /**
     * Creates new instance of the class.
     * @param type $sendAccountName
     * @return type
     * @throws SMTPException
     * @since 1.0
     */
    public function __construct($sendAccountName = '') {
        $this->log = [];

        if (class_exists(APP_DIR_NAME.'\AppConfig')) {
            $acc = WebFioriApp::getAppConfig()->getAccount($sendAccountName);

            if ($acc instanceof SMTPAccount) {
                $this->socketMailer = ConfigController::get()->getSocketMailer($acc);

                if ($this->socketMailer == ConfigController::INV_CREDENTIALS) {
                    throw new SMTPException('The account "'.$sendAccountName.'" has invalid credintials.');
                } else {
                    if ($this->socketMailer == ConfigController::INV_HOST_OR_PORT) {
                        throw new SMTPException('The account "'.$sendAccountName.'" has invalid host or port number. Port: '.$acc->getPort().', Host: '.$acc->getServerAddress().'.');
                    } else {
                        $this->asHtml = new HTMLDoc();
                        $this->asHtml->getHeadNode()->addMeta('charset', 'UTF-8');

                        return;
                    }
                }
            }
            throw new SMTPException('No SMTP account was found which has the name "'.$sendAccountName.'".');
        }
        throw new SMTPException('Class "'.APP_DIR_NAME.'\\AppConfig" not found.');
    }
    /**
     * Adds new receiver address to the list of message receivers.
     * 
     * @param string $name The name of the email receiver (such as 'Ibrahim').
     * 
     * @param string $email The email address of the receiver (such as 'example@example.com').
     * 
     * @param boolean $isCC If set to true, the receiver will receive 
     * a carbon copy of the message (CC).
     * 
     * @param boolean $isBcc If set to true, the receiver will receive 
     * a blind carbon copy of the message (Bcc).
     * 
     * @since 1.0.4
     */
    public function addReceiver($name,$email,$isCC = false,$isBcc = false) {
        $this->_getSocketMailer()->addReceiver($name, $email, $isCC, $isBcc);
    }
    /**
     * Adds a file to the email message as an attachment.
     * 
     * @param File $file The file that will be added. It will be added only if the file 
     * exist in the path or the raw data of the file is set.
     * 
     * @since 1.0
     */
    public function attach($file) {
        $this->_getSocketMailer()->addAttachment($file);
    }
    /**
     * Sets or returns the HTML document that is associated with the email 
     * message.
     * 
     * @param HTMLDoc $new If it is not null, the HTML document 
     * that is associated with the message will be set to the given one.
     * 
     * @return HTMLDoc The document that is associated with the email message.
     * 
     * @since 1.0
     */
    public function document($new = null) {
        if ($new != null) {
            $this->_setDocument($new);
        }

        return $this->getDocument();
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a blind carbon copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * @since 1.0.2
     */
    public function getBCC() {
        return $this->_getSocketMailer()->getBCC();
    }
    /**
     * Returns a string that contains the names and the addresses 
     * of people who will receive a blind carbon copy of the message.
     * 
     * The format of the string will be as follows:
     * <p>NAME_1 &lt;ADDRESS_1&gt;, NAME_2 &lt;ADDRESS_2&gt; ...</p>
     * 
     * @return string A string that contains receivers information.
     * 
     * @since 1.0.3
     */
    public function getBCCStr() {
        $this->_getSocketMailer()->getBCCStr();
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a carbon copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * 
     * @since 1.0.2
     */
    public function getCC() {
        $this->_getSocketMailer()->getCC();
    }
    /**
     * Returns a string that contains the names and the addresses 
     * of people who will receive a carbon copy of the message.
     * 
     * The format of the string will be as follows:
     * <p>NAME_1 &lt;ADDRESS_1&gt;, NAME_2 &lt;ADDRESS_2&gt; ...</p>
     * 
     * @return string A string that contains receivers information.
     * 
     * @since 1.0.3
     */
    public function getCCStr() {
        $this->_getSocketMailer()->getCCStr();
    }
    /**
     * Returns a child node given its ID.
     * 
     * @param string $id The ID of the child.
     * 
     * @return null|HTMLNode The method returns an object of type HTMLNode. 
     * if found. If no node has the given ID, the method will return null.
     * 
     * @since 1.0.5
     */
    public function getChildByID($id) {
        return $this->getDocument()->getChildByID($id);
    }
    /**
     * Returns an array that contains log messages which are generated 
     * from sending SMTP commands.
     * 
     * @return array The array will be indexed. In every index, there 
     * will be a sub-associative array with the following indices:
     * <ul>
     * <li>command</li>
     * <li>response-code</li>
     * <li>response-message</li>
     * </ul>
     * 
     * @since 1.0.4
     */
    public function getLog() {
        if ($this->_getSocketMailer() !== null) {
            return $this->_getSocketMailer()->getResponsesLog();
        }

        return $this->log;
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive an original copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * 
     * @since 1.0.2
     */
    public function getReceivers() {
        return $this->_getSocketMailer()->getReceivers();
    }
    /**
     * Returns a string that contains the names and the addresses 
     * of people who will receive an original copy of the message.
     * 
     * The format of the string will be as follows:
     * <p>NAME_1 &lt;ADDRESS_1&gt;, NAME_2 &lt;ADDRESS_2&gt; ...</p>
     * 
     * @return string A string that contains receivers information.
     * 
     * @since 1.0.3
     */
    public function getReceiversStr() {
        return $this->_getSocketMailer()->getReceiversStr();
    }
    /**
     * Sets or gets the importance level of email message.
     * 
     * @param int $imp The importance level of the message. -1 for not urgent, 0 
     * for normal and 1 for urgent.
     * 
     * @return int The importance level of the message.
     * 
     * @since 1.0.1
     */
    public function importance($imp = null) {
        if ($imp !== null) {
            $this->_getSocketMailer()->setPriority($imp);
        }

        return $this->_getSocketMailer()->getPriority();
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * 
     * @param HTMLNode|string $node The node that will be inserted. Also, 
     * this can be the tag name of the node such as 'div'.
     * 
     * @param string|null $parentNodeId The ID of the node that the given node 
     * will be inserted to. If null is given, the node will be added directly inside 
     * the element &lt;body&gt;. Default value is null.
     * 
     * @return HTMLNode|null The method will return the inserted 
     * node if it was inserted. If it is not, the method will return null.
     * 
     * @since 1.0.5
     */
    public function insert($node, $parentNodeId = null) {
        if (gettype($node) == 'string') {
            $node = new HTMLNode($node);
        }
        $parent = $parentNodeId !== null ? $this->getChildByID($parentNodeId) 
                : $this->getDocument()->getBody();

        if ($parent !== null) {
            $parent->addChild($node);

            return $node;
        }
    }
    /**
     * Adds a child HTML node to the body of the message.
     * 
     * @param HTMLNode $htmlNode An instance of 'HTMLNode'.
     * 
     * @since 1.0
     */
    public function insertNode($htmlNode) {
        $this->getDocument()->addChild($htmlNode);
    }
    /**
     * Sends the message and set message instance to null.
     * 
     * @since 1.0
     */
    public function send() {
        $this->_sendMessage();
    }
    /**
     * Sets the document at which the message will use.
     * 
     * @param HTMLDoc $doc An HTML document.
     * 
     * @since 1.0.5
     */
    public function setDocument($doc) {
        if ($doc instanceof HTMLDoc) {
            $this->asHtml = $doc;
        }
    }
    /**
     * Sets the subject of the email message.
     * 
     * @param string $subject The subject of the email message.
     * 
     * @since 1.0
     */
    public function subject($subject) {
        $this->_getSocketMailer()->setSubject($subject);
    }
    /**
     * Adds a text node to the body of the message.
     * 
     * @param string $text The text that will be in the body of the node.
     * 
     * @since 1.0
     */
    public function write($text) {
        $this->getDocument()->addChild(HTMLNode::createTextNode($text,false));
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
     * @since 1.0
     */
    private function _sendMessage() {
        $this->socketMailer->write($this->asHtml->toHTML(), true);
        $this->log = $this->socketMailer->getResponsesLog();
    }
    /**
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
     * @since 1.0.5
     */
    private function getDocument() {
        return $this->asHtml;
    }
}
