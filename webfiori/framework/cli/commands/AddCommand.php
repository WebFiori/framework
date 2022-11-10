<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\commands;

use Exception;
use webfiori\database\ConnectionInfo;
use webfiori\cli\CLICommand;
use webfiori\framework\ConfigController;
use webfiori\framework\DB;
use webfiori\email\SMTPAccount;
use webfiori\email\SMTPServer;
use webfiori\framework\WebFioriApp;
use webfiori\framework\writers\LangClassWriter;

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
    public function exec() : int {
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
        $dbType = $this->select('Select database type:', ConnectionInfo::SUPPORTED_DATABASES);
        if ($dbType == 'mysql') {
            $connInfoObj = new ConnectionInfo('mysql', 'roor', 'pass', 'ok');
        } else if ($dbType == 'mssql') {
            $connInfoObj = new ConnectionInfo('mssql', 'roor', 'pass', 'ok');
        }
        
        $connInfoObj->setHost($this->getInput('Database host:', '127.0.0.1'));
        $connInfoObj->setPort($this->getInput('Port number:', 3306));
        $connInfoObj->setUsername($this->getInput('Username:'));
        $connInfoObj->setPassword($this->getInput('Password:'));
        $connInfoObj->setDBName($this->getInput('Database name:'));
        $connInfoObj->setName($this->getInput('Give your connection a friendly name:', 'db-connection-'.count(WebFioriApp::getAppConfig()->getDBConnections())));
        $this->println('Trying to connect to the database...');

        $addConnection = $this->tryConnect($connInfoObj);

        if ($addConnection !== true) {
            if ($connInfoObj->getHost() == '127.0.0.1') {
                $connInfoObj->setHost('localhost');
                $addConnection = $this->tryConnect($connInfoObj);
            } else if ($connInfoObj->getHost() == 'localhost') {
                $connInfoObj->setHost('127.0.0.1');
                $addConnection = $this->tryConnect($connInfoObj);
            }
        } 
        
        if ($addConnection === true) {
            $this->success('Connected. Adding the connection...');

            ConfigController::get()->addOrUpdateDBConnection($connInfoObj);
            $this->success('Connection information was stored in application configuration.');
        } else {
            $this->error('Unable to connect to the database.');
            $this->error($addConnection->getMessage());
            $this->_confirmAdd($connInfoObj);
        }
        return 0;
    }
    private function tryConnect($connectionInfo) {
        $db = new DB($connectionInfo);
        
        try {
            $db->getConnection();
            return true;
        } catch (Exception $ex) {
            return $ex;
        }
    }
    private function _addLang() {
        $langCode = strtoupper(trim($this->getInput('Language code:')));

        if (strlen($langCode) != 2) {
            $this->error('Invalid language code.');

            return -1;
        }
        $siteInfo = ConfigController::get()->getSiteConfigVars();

        if (isset($siteInfo['website-names'][$langCode])) {
            $this->info('This language already added. Nothing changed.');

            return 0;
        }
        $siteInfo['website-names'][$langCode] = $this->getInput('Name of the website in the new language:');
        $siteInfo['descriptions'][$langCode] = $this->getInput('Description of the website in the new language:');
        $siteInfo['titles'][$langCode] = $this->getInput('Default page title in the new language:');
        $writingDir = $this->select('Select writing direction:', [
            'ltr', 'rtl'
        ]);
        ConfigController::get()->updateSiteInfo($siteInfo);
        $writer = new LangClassWriter($langCode, $writingDir);
        $writer->writeClass();
        $this->success('Language added. Also, a class for the language '
                .'is created at "'.APP_DIR_NAME.'\langs" for that language.');
        return 0;
    }
    private function _addSmtp() {
        $smtpConn = new SMTPAccount();
        $smtpConn->setServerAddress($this->getInput('SMTP Server address:', '127.0.0.1'));
        $smtpConn->setPort(25);
        $addr = $smtpConn->getAddress();
        
        if ($addr == 'smtp.outlook.com'
            || $addr == 'outlook.office365.com'
            || $addr == 'smtp.office365.com') {
            $smtpConn->setPort(587);
        } else if ($addr == 'smtp.gmail.com'
            || $addr == 'smtp.mail.yahoo.com') {
            $smtpConn->setPort(465);
        }
        $smtpConn->setPort($this->getInput('Port number:', $smtpConn->getPort()));
        $smtpConn->setUsername($this->getInput('Username:'));
        $smtpConn->setPassword($this->getInput('Password:'));
        $smtpConn->setAddress($this->getInput('Sender email address:', $smtpConn->getUsername()));
        $smtpConn->setSenderName($this->getInput('Sender name:', $smtpConn->getAddress()));
        $smtpConn->setAccountName($this->getInput('Give your connection a friendly name:', 'smtp-connection-'.count(WebFioriApp::getAppConfig()->getAccounts())));
        $this->println('Testing connection. This can take up to 1 minute...');
        $server = new SMTPServer($smtpConn->getServerAddress(), $smtpConn->getPort());

        try {
            if ($server->authLogin($smtpConn->getUsername(), $smtpConn->getPassword())) {
                $this->success('Connectd. Adding connection information...');
                ConfigController::get()->updateOrAddEmailAccount($smtpConn);
                $this->success('Connection information was stored in application configuration.');

                
            } else {
                $this->error('Unable to connect to SMTP server.');
                $this->println('Error Information: '.$server->getLastResponse());

                $this->_confirmAdd($smtpConn);
            }
        } catch (Exception $ex) {
            $this->error('An exception with message "'.$ex->getMessage().'" was thrown while trying to connect.');
            $this->_confirmAdd($smtpConn);
        }
        
        return 0;
    }
    private function _confirmAdd($smtpOrDbConn) {
        if ($this->confirm('Would you like to store connection information anyway?', false)) {
            if ($smtpOrDbConn instanceof SMTPAccount) {
                ConfigController::get()->updateOrAddEmailAccount($smtpOrDbConn);
            } else if ($smtpOrDbConn instanceof ConnectionInfo) {
                ConfigController::get()->addOrUpdateDBConnection($smtpOrDbConn);
            }
            $this->success('Connection information was stored in application configuration.');
        }
    }
}
