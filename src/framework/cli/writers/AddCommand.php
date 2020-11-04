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
namespace webfiori\framework\cli;

use webfiori\framework\DB;
use webfiori\database\ConnectionInfo;
use webfiori\framework\mail\SMTPAccount;
use webfiori\WebFiori;
use Exception;

/**
 * A command which is used to add a database connection or SMTP account.
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class AddCommand extends CLICommand {
    public function __construct() {
        parent::__construct('add', [

        ], 'Add a database connection or SMTP account.');
    }
    /**
     * Execute the command.
     * 
     * @return int
     */
    public function exec() {
        $options = [
            'New database connection.',
            'New SMTP connection.',
            'New website language.',
            'Quit.'
        ];
        $answer = $this->select('What would you like to add?', $options, count($options) - 1);

        if ($answer == 'New database connection.') {
            return $this->_addDbConnection();
        } else if ($answer == 'New SMTP connection.') {
            return $this->_addSmtp();
        } else if ($answer == 'New website language.') {
            return $this->_addLang();
        }

        return 0;
    }
    private function _addDbConnection() {
        $connInfoObj = new ConnectionInfo('mysql', 'roor', 'pass', 'ok');
        $connInfoObj->setHost($this->getInput('Database host:', '127.0.0.1'));
        $connInfoObj->setPort($this->getInput('Port number:', 3306));
        $connInfoObj->setUsername($this->getInput('Username:'));
        $connInfoObj->setPassword($this->getInput('Password:'));
        $connInfoObj->setDBName($this->getInput('Database name:'));
        $connInfoObj->setName($this->getInput('Give your connection a friendly name:', 'db-connection-'.count(WebFiori::getConfig()->getDBConnections())));
        $this->println('Trying to connect to the database...');
        
        $db = new DB($connInfoObj);
        
        try {
            $db->getConnection();
        } catch (Exception $ex) {
            $this->error('Unable to connect to the database.');
            $this->error($ex->getMessage());
            return -1;
        }

        $this->success('Connected. Adding the connection...');

        WebFiori::getSysController()->addOrUpdateDBConnections([$connInfoObj]);
        $this->success('Connection information was stored in the class "webfiori\conf\Config".');

        return 0;
    }
    private function _addLang() {
        $langCode = strtoupper(trim($this->getInput('Language code:')));

        if (strlen($langCode) != 2) {
            $this->error('Invalid language code.');

            return -1;
        }
        $siteInfo = WebFiori::getWebsiteController()->getSiteConfigVars();

        if (isset($siteInfo['website-names'][$langCode])) {
            $this->info('This language already added. Nothing changed.');

            return 0;
        }
        $siteInfo['website-names'][$langCode] = $this->getInput('Name of the website in the new language:');
        $siteInfo['site-descriptions'][$langCode] = $this->getInput('Description of the website in the new language:');
        WebFiori::getWebsiteController()->updateSiteInfo($siteInfo);
        $this->success('Language added.');
    }
    private function _addSmtp() {
        $smtpConn = new SMTPAccount();
        $smtpConn->setServerAddress($this->getInput('SMTP Server address:', '127.0.0.1'));
        $smtpConn->setPort($this->getInput('Port number:', 465));
        $smtpConn->setUsername($this->getInput('Username:'));
        $smtpConn->setPassword($this->getInput('Password:'));
        $smtpConn->setAddress($this->getInput('Sender email address:', $smtpConn->getUsername()));
        $smtpConn->setSenderName($this->getInput('Sender name:', 'WebFiori Framework'));
        $smtpConn->setAccountName($this->getInput('Give your connection a friendly name:', 'smtp-connection-'.count(WebFiori::getMailConfig()->getAccounts())));
        $this->println('Testing connection. This can take up to 1 minute...');
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
}
