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

/**
 * A class that represents an SMTP account which is used to connect to SMTP server.
 *
 * @author Ibrahim
 * 
 * @version 1.0.3
 */
class SMTPAccount {
    /**
     * The name of SMTP account.
     * 
     * @var string 
     * 
     * @since 1.0.2
     */
    private $accName; 
    /**
     * Email address.
     * 
     * @var string 
     */
    private $address;
    /**
     * Server address of the email account.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $emailServerAddress;
    /**
     * The name of the email account.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $name;
    /**
     * The password of the user account.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $password;
    /**
     * The port number that is used to access the email server.
     * 
     * @var int
     * 
     * @since 1.0 
     */
    private $port;
    /**
     * The user name that is used to login.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $userName;
    /**
     * Creates new instance of the class.
     * 
     * @param array $options An optional array that contains connection info. The array 
     * can have the following indices:
     * <ul>
     * <li><b>port</b>: SMTP server port address. usually 25 or 465.</li>
     * <li><b>server-address</b>: SMTP server address.</li>
     * <li><b>user</b>: The username at which it is used to login to SMTP server.</li>
     * <li><b>pass</b>: The password of the user</li>
     * <li><b>sender-name</b>: The name of the sender that will appear when the 
     * message is sent.</li>
     * <li><b>sender-address</b>: The address that will appear when the 
     * message is sent. Usually, it is the same as the username.</li>
     * <li><b>account-name</b>: A unique name for the account. Used when creating 
     * new email message. If not provided, 'sender-name' is used.</li>
     * </ul>
     * 
     * @since 1.0.1
     */
    public function __construct($options = []) {
        if (isset($options['port'])) {
            $this->setPort($options['port']);
        } else {
            $this->setPort(465);
        }

        if (isset($options['user'])) {
            $this->setUsername($options['user']);
        } else {
            $this->setUsername('');
        }

        if (isset($options['pass'])) {
            $this->setPassword($options['pass']);
        } else {
            $this->setPassword('');
        }

        if (isset($options['server-address'])) {
            $this->setServerAddress($options['server-address']);
        } else {
            $this->setServerAddress('');
        }

        if (isset($options['sender-name'])) {
            $this->setSenderName($options['sender-name']);
        } else {
            $this->setSenderName('');
        }

        if (isset($options['sender-address'])) {
            $this->setAddress($options['sender-address']);
        } else {
            $this->setAddress('');
        }

        if (isset($options['account-name'])) {
            $this->setAccountName($options['account-name']);
        } else {
            $this->setAccountName($this->getSenderName());
        }
    }
    /**
     * Returns the name of the account.
     * 
     * The name of the account is used by the class 'EmailMessage' when creating 
     * new instance of the class. Also, the name is used when storing account 
     * information.
     * 
     * @return string A string that represents the name of the account.
     */
    public function getAccountName() {
        return $this->accName;
    }
    /**
     * Returns the email address.
     * 
     * @return string The email address which will be used in the header 
     * 'FROM' when sending an email. Default is empty string.
     * 
     * @since 1.0
     */
    public function getAddress() {
        return $this->address;
    }
    /**
     * Returns the password of the account that will be used to access SMTP server.
     * 
     * @return string The password of the user account that is used to access email server. 
     * default is empty string.
     * 
     * @since 1.0
     */
    public function getPassword() {
        return $this->password;
    }
    /**
     * Returns SMTP server port number.
     * 
     * @return int Default is 465.
     * 
     * @since 1.0
     */
    public function getPort() {
        return $this->port;
    }
    /**
     * Returns the name of sender that will be used in the 'FROM' header.
     * 
     * @return string The name of the email sender. Usually this is similar to 
     * email address but can also be a name.
     * 
     * @since 1.0
     */
    public function getSenderName() {
        return $this->name;
    }
    /**
     * Returns SMTP server address.
     * 
     * @return string The address of the SMTP server (such as 'mail.example.com'). 
     * 
     * @since 1.0
     */
    public function getServerAddress() {
        return $this->emailServerAddress;
    }
    /**
     * Returns the username that is used to access SMTP server.
     * 
     * @return string The username that is used to access email server. Default 
     * is empty string.
     * 
     * @since 1.0
     */
    public function getUsername() {
        return $this->userName;
    }
    /**
     * Sets the name of the account.
     * 
     * The name of the account is used by the class 'EmailMessage' when creating 
     * new instance of the class. Also, the name is used when storing the account.
     * 
     * @param string $name The name of the account.
     * 
     * @since 1.0.2
     */
    public function setAccountName($name) {
        $this->accName = $name;
    }
    /**
     * Sets the email address.
     * 
     * @param string $address An email address.
     * 
     * @since 1.0
     */
    public function setAddress($address) {
        $this->address = trim($address);
    }
    /**
     * Sets the password of the user account that is used to access email server.
     * 
     * @param string $pass The password of the user account that is used to access email server.
     * 
     * @since 1.0
     */
    public function setPassword($pass) {
        $this->password = $pass;
    }
    /**
     * Sets port number of SMTP server.
     * 
     * @param int $port The port number of email server. Common ports are 25, 465
     * and 586.
     * 
     * @since 1.0
     */
    public function setPort($port) {
        $this->port = intval($port);
    }
    /**
     * Sets the name of the email account.
     * 
     * @param string $name The name of the account (such as 'Programming Team'). 
     * The name is used when sending an email message using the given SMTP account. 
     * The name will be used in the header 
     * 'FROM' when sending an email.
     * 
     * @since 1.0
     */
    public function setSenderName($name) {
        $this->name = trim($name);
    }
    /**
     * Sets the address of the email server.
     * 
     * @param string $addr The address of the email server (such as 'mail.example.com').
     * 
     * @since 1.0
     */
    public function setServerAddress($addr) {
        $this->emailServerAddress = trim($addr);
    }
    /**
     * Sets the username that is used to access email server.
     * 
     * @param string $u The username that is used to access email server.
     * 
     * @since 1.0
     */
    public function setUsername($u) {
        $this->userName = trim($u);
    }
}
