<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\cli;
use webfiori\WebFiori;
use webfiori\entity\DBConnectionFactory;
use webfiori\entity\DBConnectionInfo;
use webfiori\entity\mail\SMTPAccount;

/**
 * A command which is used to add a database connection or SMTP account.
 *
 * @author Ibrahim
 * @since 1.1.0
 * @version 1.0
 */
class AddCommand extends CLICommand {
    
    public function __construct() {
        parent::__construct('add', [
            
        ], 'Add a database connection or SMTP account.');
    }
    public function exec() {
        $options = [
            'New database connection.',
            'New SMTP connection.',
            'Quit.'
        ];
        $answer = $this->select('What would you like to add?', $options, count($options) - 1);
        if ($answer == 'New database connection.') {
            return $this->_addDbConnection();
        } else if ($answer == 'New SMTP connection.') {
            return $this->_addSmtp();
        }
        return 0;
    }
    private function _addSmtp() {
        $smtpConn = new SMTPAccount();
        $smtpConn->setServerAddress($this->getInput('SMTP Server address:', 'localhost'));
        $smtpConn->setPort($this->getInput('Port number:', 465));
        $smtpConn->setUsername($this->getInput('Username:'));
        $smtpConn->setPassword($this->getInput('Password:'));
        $smtpConn->setAddress($this->getInput('Sender email address:', $smtpConn->getUsername()));
        $smtpConn->setSenderName($this->getInput('Sender name:', 'WebFiori Framework'));
        $smtpConn->setAccountName($this->getInput('Give your connection a friendly name:', 'smtp-connection-'.count(WebFiori::getMailConfig()->getAccounts())));
        $this->println('Testing connection...');
        $result = WebFiori::getEmailController()->getSocketMailer($smtpConn);
        if (gettype($result) == 'object') {
            $this->success('Connectd. Adding connection information...');
            WebFiori::getEmailController()->updateOrAddEmailAccount($smtpConn);
            $this->success('Connection information was stored in the class "webfiori\conf\MailConfig".');
            return 0;
        } else {
            $this->error('Unable to connect to SMTP server.');
            $this->println('Error Information: '.$result);
            return -1;
        }
    }
    private function _addDbConnection() {
        $host = $this->getInput('Database host:', 'localhost');
        $port = $this->getInput('Port number:', 3306);
        $username = $this->getInput('Username:');
        $password = $this->getInput('Password:');
        $databaseName = $this->getInput('Database name:');
        $connName = $this->getInput('Give your connection a friendly name:', 'db-connection-'.count(WebFiori::getConfig()->getDBConnections()));
        $this->println('Trying to connect to the database...');
        $result = DBConnectionFactory::mysqlLink([
            'host' => $host,
            'port' => $port,
            'user' => $username,
            'pass' => $password,
            'db-name' => $databaseName
        ]);
        if (gettype($result) == 'array') {
            $this->error('Unable to connect to the database.');
            $this->println('Error Code: '.$result['error-code']);
            $this->println('Error Message: '.$result['error-message']);
            return -1;
        } else {
            $this->success('Connected. Adding the connection...');
            $conn = new DBConnectionInfo($username, $password, $databaseName, $host, $port);
            $conn->setConnectionName($connName);
            WebFiori::getSysController()->addOrUpdateDBConnections([$conn]);
            $this->success('Connection information was stored in the class "webfiori\conf\Config".');
            return 0;
        }
        
    }
}
