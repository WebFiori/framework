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
     * Server address of the email account.
     * @var string
     * @since 1.0 
     */
    private $emailServerAddress;
    /**
     * The name of the email account.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * The password of the user account.
     * @var string
     * @since 1.0 
     */
    private $password;
    /**
     * The port number that is used to access the email server.
     * @var int
     * @since 1.0 
     */
    private $port;
    /**
     * The user name that is used to login.
     * @var string
     * @since 1.0 
     */
    private $userName;
    /**
     * Creates new instance of the class.
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
     * </ul>
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
     * Returns the name of sender.
     * @return string The name of the email sender. The name will be used in the header 
     * 'FROM' when sending an email. Default is empty string.
     * @since 1.0
     */
    public function getSenderName() {
        return $this->name;
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
     * Returns the port number of email server.
     * @return int The port number of email server. Default is 465.
     * @since 1.0
     */
    public function getPort() {
        return $this->port;
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
     * Returns the username that is used to access email server.
     * @return string The username that is used to access email server. Default 
     * is empty string.
     * @since 1.0
     */
    public function getUsername() {
        return $this->userName;
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
    public function setSenderName($name) {
        $this->name = trim($name);
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
     * Sets the port number of email server.
     * @param int $port The port number of email server such as 25. It will 
     * be only set if the given value is an integer and it is greater than 0.
     * @since 1.0
     */
    public function setPort($port) {
        if (gettype($port) == 'integer' && $port > 0) {
            $this->port = $port;
        }
    }
    /**
     * Sets the address of the email server.
     * @param string $addr The address of the email server (such as 'mail.example.com').
     * @since 1.0
     */
    public function setServerAddress($addr) {
        $this->emailServerAddress = trim($addr);
    }
    /**
     * Sets the username that is used to access email server.
     * @param string $u The username that is used to access email server.
     * @since 1.0
     */
    public function setUsername($u) {
        $this->userName = trim($u);
    }
}
