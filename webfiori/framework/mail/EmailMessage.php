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
use webfiori\framework\mail\SMTPServer;
use webfiori\framework\mail\SMTPAccount;
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
     * A boundary variable used to separate email message parts.
     * 
     * @var string
     * 
     * @since 2.0
     */
    private $boundry;
    /**
     * 
     * @var SMTPServer|null
     * 
     * @since 2.0
     */
    private $smtpServer;
    /**
     * The subject of the email message.
     * 
     * @var string 
     * 
     * @since 2.0
     */
    private $subject;
    /**
     * An array that contains an objects of type 'File' or 
     * file path. 
     * 
     * @var array 
     * 
     * @since 2.0
     */
    private $attachments;
    /**
     * SMTP account that will be used to send the message.
     * 
     * @var SMTPAccount
     * 
     * @since 1.0
     */
    private $smtpAcc;
    /**
     * A constant that colds the possible values for the header 'Priority'. 
     * 
     * @see https://tools.ietf.org/html/rfc4021#page-33
     * 
     * @since 2.0
     */
    const PRIORITIES = [
        -1 => 'non-urgent',
        0 => 'normal',
        1 => 'urgent'
    ];
    /**
     * 
     * @var array
     * 
     * @since 2.0
     */
    private $receiversArr;
    private $inReplyTo;
    /**
     * Sets the subject of the message.
     * 
     * @param string $subject Email subject.
     * 
     * @since 2.0
     */
    public function setSubject($subject) {
        $trimmed = $this->_trimControlChars($subject);

        if (strlen($trimmed) > 0) {
            $this->subject = $trimmed;
        }
    }
    /**
     * Returns the subject of the email.
     * 
     * @return string The subject of the email. Default return value is 
     * 'Hello From WebFiori Framework'.
     * 
     * @since 2.0
     */
    public function getSubject() {
        return $this->subject;
    }
    /**
     * Sets the priority of the message.
     * 
     * @param int $priority The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. If the passed value is greater than 1, 
     * then 1 will be used. If the passed value is less than -1, then -1 is 
     * used. Other than that, 0 will be used.
     * 
     * @since 2.0
     */
    public function setPriority($priority) {
        $asInt = intval($priority);

        if ($asInt <= -1) {
            $this->priority = -1;
        } else if ($asInt >= 1) {
            $this->priority = 1;
        } else {
            $this->priority = 0;
        }
    }
    /**
     *
     * @var HTMLDoc 
     * 
     * @since 1.0 
     */
    private $asHtml;

    private $log;
    /**
     * Creates new instance of the class.
     * @param type $sendAccountName
     * @return type
     * @throws SMTPException
     * @since 1.0
     */
    public function __construct($sendAccountName = '') {
        $this->log = [];
        $this->setPriority(0);
        $this->boundry = hash('sha256', date(DATE_ISO8601));
        $this->receiversArr = [
            'cc' => [],
            'bcc' => [],
            'to' => []
        ];
        $this->attachments = [];
        $this->inReplyTo = [];
        $this->asHtml = new HTMLDoc();
        
        if (class_exists(APP_DIR_NAME.'\AppConfig')) {
            $acc = WebFioriApp::getAppConfig()->getAccount($sendAccountName);

            if ($acc instanceof SMTPAccount) {
                $this->smtpAcc = $acc;
                return;
            }
            throw new SMTPException('No SMTP account was found which has the name "'.$sendAccountName.'".');
        }
        throw new SMTPException('Class "'.APP_DIR_NAME.'\\AppConfig" not found.');
    }
    /**
     * Adds new receiver address to the list of 'to' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return boolean If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addTo($address, $name = null) {
        return $this->_addAddress($address, $name, 'to');
    }
    /**
     * Adds a file as email attachment.
     * 
     * @param File|string $fileObjOrFilePath An object of type 'File'. This also can 
     * be the absolute path to a file in the file system.
     * 
     * @return boolean If the file is added, the method will return true. 
     * Other than that, the method will return false.
     * 
     * @since 2.0
     */
    public function addAttachment($fileObjOrFilePath) {
        $retVal = false;

        $type = gettype($fileObjOrFilePath);
        
        if ($type == 'string') {
            if (file_exists($fileObjOrFilePath)) {
                $this->attachments[] = $fileObjOrFilePath;
                $retVal = true;
            }
        } else {
            if (class_exists('webfiori\framework\File') && $fileObjOrFilePath instanceof File 
                && (file_exists($fileObjOrFilePath->getAbsolutePath()) || file_exists(str_replace('\\', '/', $fileObjOrFilePath->getAbsolutePath())) || $fileObjOrFilePath->getRawData() !== null)) {
                $this->attachments[] = $fileObjOrFilePath;
                $retVal = true;
            }
        }

        return $retVal;
    }
    /**
     * Adds new receiver address to the list of 'cc' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return boolean If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addCC($address, $name = null) {
        return $this->_addAddress($address, $name, 'cc');
    }
    /**
     * Adds new receiver address to the list of 'bcc' receivers.
     * 
     * @param string $address The email address of the receiver (such as 'example@example.com').
     * 
     * @param string $name An optional receiver name. If not provided, the 
     * email address is used as name.
     * 
     * @return boolean If the address is added, the method will return 
     * true. False otherwise.
     * 
     * @since 2.0
     */
    public function addBCC($address, $name = null) {
        return $this->_addAddress($address, $name, 'bcc');
    }
    private function _addAddress($address, $name, $type) {
        $nameTrimmed = $this->_trimControlChars(str_replace('<', '', str_replace('>', '', $name)));
        $addressTrimmed = $this->_trimControlChars(str_replace('<', '', str_replace('>', '', $address)));
        
        if (strlen($nameTrimmed) == 0) {
            $nameTrimmed = $addressTrimmed;
        }
        if (strlen($addressTrimmed) != 0 && in_array($type, ['cc', 'bcc', 'to'])) {
            $this->receiversArr[$type][$addressTrimmed] = $nameTrimmed;
            return true;
        }

        return false;
    }
    /**
     * Returns an associative array that contains the names and the addresses 
     * of people who will receive a blind carbon copy of the message.
     * 
     * The indices of the array will act as the addresses of the receivers and 
     * the value of each index will contain the name of the receiver.
     * 
     * @return array An array that contains receivers information.
     * 
     * @since 1.0.2
     */
    public function getBCC() {
        return $this->receiversArr['bcc'];
    }
    private function _getReceiversStr($type) {
        $arr = [];

        foreach ($this->receiversArr[$type] as $address => $name) {
            array_push($arr, '=?UTF-8?B?'.base64_encode($name).'?='.' <'.$address.'>');
        }

        return implode(',', $arr);
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
        $this->_getReceiversStr('bcc');
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
        $this->receiversArr['cc'];
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
        $this->_getReceiversStr('cc');
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
    public function getTo() {
        return $this->receiversArr['to'];
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
    public function getToStr() {
        return $this->_getReceiversStr('to');
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
     * Sends the message and set message instance to null.
     * 
     * @since 1.0
     */
    public function send() {
        $acc = $this->getSMTPAccount();
        $this->smtpServer = new SMTPServer($acc->getServerAddress(), $acc->getPort());
        
        if ($this->smtpServer->authLogin($acc->getUsername(), $acc->getPassword())) {
            $this->smtpServer->sendCommand('MAIL FROM: <'.$acc->getAddress().'>');
            $this->_receiversCommand('to');
            $this->_receiversCommand('cc');
            $this->_receiversCommand('bcc');
            $this->smtpServer->sendCommand('DATA');
            $importanceHeaderVal = $this->_priorityCommand();
            
            $this->smtpServer->sendCommand('Content-Transfer-Encoding: quoted-printable');
            $this->smtpServer->sendCommand('Importance: '.$importanceHeaderVal);
            $this->smtpServer->sendCommand('From: =?UTF-8?B?'. base64_encode($acc->getSenderName()).'?= <'.$acc->getSenderName().'>');
            $this->smtpServer->sendCommand('To: '.$this->getToStr());
            $this->smtpServer->sendCommand('CC: '.$this->getCCStr());
            $this->smtpServer->sendCommand('BCC: '.$this->getBCCStr());
            $this->smtpServer->sendCommand('Date:'.date('r (T)'));
            $this->smtpServer->sendCommand('Subject:'.'=?UTF-8?B?'.base64_encode($this->getSubject()).'?=');
            $this->smtpServer->sendCommand('MIME-Version: 1.0');
            $this->smtpServer->sendCommand('Content-Type: multipart/mixed; boundary="'.$this->boundry.'"'.self::NL);
            $this->smtpServer->sendCommand('--'.$this->boundry);
            $this->smtpServer->sendCommand('Content-Type: text/html; charset="UTF-8"'.self::NL);
            $this->smtpServer->sendCommand($this->_trimControlChars($this->getDocument()->toHTML()));
            $this->_appendAttachments();
            $this->smtpServer->sendCommand(self::NL.'.');
            $this->smtpServer->sendCommand('QUIT');
            
        } else {
            throw new SMTPException('Unable to login to SMTP server: '.$this->smtpServer->getLastResponse(), $this->smtpServer->getLastResponseCode());
        }
    }
    /**
     * A method that is used to include email attachments.
     * 
     * @since 1.3
     */
    private function _appendAttachments() {
        if (count($this->attachments) != 0) {
            foreach ($this->attachments as $file) {
                if ($file->getRawData() === null) {
                    $file->read();
                }
                $content = $file->getRawData();
                $contentChunk = chunk_split(base64_encode($content));
                $this->smtpServer->sendCommand('--'.$this->boundry);
                $this->smtpServer->sendCommand('Content-Type: '.$file->getFileMIMEType().'; name="'.$file->getName().'"');
                $this->smtpServer->sendCommand('Content-Transfer-Encoding: base64');
                $this->smtpServer->sendCommand('Content-Disposition: attachment; filename="'.$file->getName().'"'.self::NL);
                $this->smtpServer->sendCommand($contentChunk);
            }
            $this->smtpServer->sendCommand('--'.$this->boundry.'--');
        }
    }
    /**
     * Returns the priority of the message.
     * 
     * @return int The priority of the message. -1 for non-urgent, 0 
     * for normal and 1 for urgent. Default value is 0.
     * 
     * @since 2.0
     */
    public function getPriority() {
        return $this->priority;
    }
    private function _priorityCommand() {
        $priorityAsInt = $this->getPriority();
        $priorityHeaderVal = self::PRIORITIES[$priorityAsInt];

        if ($priorityAsInt == -1) {
            $importanceHeaderVal = 'low';
        } else if ($priorityAsInt == 1) {
            $importanceHeaderVal = 'High';
        } else {
            $importanceHeaderVal = 'normal';
        }
        $this->smtpServer->sendCommand('Priority: '.$priorityHeaderVal);

        return $importanceHeaderVal;
    }
    private function _receiversCommand($type) {
        foreach ($this->receiversArr[$type] as $address => $name) {
            $this->smtpServer->sendCommand('RCPT TO: <'.$address.'>');
        }
    }
    /**
     * 
     * @return SMTPAccount
     */
    public function getSMTPAccount() {
        return $this->smtpAcc;
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
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
     * @since 1.0.5
     */
    private function getDocument() {
        return $this->asHtml;
    }
    /**
     * Removes control characters from the start and end of string in addition 
     * to white spaces.
     * 
     * @param string $str The string that will be trimmed.
     * 
     * @return string The string after its control characters trimmed.
     */
    private function _trimControlChars($str) {
        return trim($str, "\x00..\x20");
    }
}
