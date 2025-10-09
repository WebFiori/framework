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
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Command;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Mail\Exceptions\SMTPException;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\SMTPServer;
use WebFiori\Framework\App;
use WebFiori\Framework\DB;
use WebFiori\Framework\Writers\LangClassWriter;

/**
 * A command which is used to add a database connection or SMTP account.
 *
 * @author Ibrahim
 *
 * @since 1.1.0
 *
 * @version 1.0
 */
class AddCommand extends Command {
    public function __construct() {
        parent::__construct('add', [], 'Add a database connection or SMTP account.');
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
            return $this->addDbConnection();
        } else if ($answer == 'New SMTP connection.') {
            return $this->addSmtp();
        } else if ($answer == 'New website language.') {
            return $this->addLang();
        }

        return 0;
    }
    private function addDbConnection(): int {
        $dbType = $this->select('Select database type:', ConnectionInfo::SUPPORTED_DATABASES);

        $connInfoObj = new ConnectionInfo('mysql', 'root', 'pass', 'ok');

        if ($dbType == 'mssql') {
            $connInfoObj = new ConnectionInfo('mssql', 'root', 'pass', 'ok');
        }

        $connInfoObj->setHost($this->getInput('Database host:', '127.0.0.1'));
        $connInfoObj->setPort($this->getInput('Port number:', 3306));
        $connInfoObj->setUsername($this->getInput('Username:'));
        $connInfoObj->setPassword($this->getInput('Password:'));
        $connInfoObj->setDBName($this->getInput('Database name:'));
        $connInfoObj->setName($this->getInput('Give your connection a friendly name:', 'db-connection-'.(count(App::getConfig()->getDBConnections()) + 1)));
        $this->println('Trying to connect to the database...');

        $addConnection = $this->tryConnect($connInfoObj);
        $orgHost = $connInfoObj->getHost();
        $orgErr = $addConnection !== true ? $addConnection->getMessage() : '';
        
        if ($addConnection !== true) {
            if ($connInfoObj->getHost() == '127.0.0.1') {
                $this->println("Trying with 'localhost'...");
                $connInfoObj->setHost('localhost');
                $addConnection = $this->tryConnect($connInfoObj);
            } else if ($connInfoObj->getHost() == 'localhost') {
                $this->println("Trying with '127.0.0.1'...");
                $connInfoObj->setHost('127.0.0.1');
                $addConnection = $this->tryConnect($connInfoObj);
            }
        }

        if ($addConnection === true) {
            $this->success('Connected. Adding the connection...');

            App::getConfig()->addOrUpdateDBConnection($connInfoObj);
            $this->success('Connection information was stored in application configuration.');
        } else {
            $connInfoObj->setHost($orgHost);
            $this->error('Unable to connect to the database.');
            $this->error($orgErr);
            $this->confirmAdd($connInfoObj);
        }

        return 0;
    }
    private function addLang(): int {
        $langCode = strtoupper(trim($this->getInput('Language code:')));

        if (strlen($langCode) != 2) {
            $this->error('Invalid language code.');

            return -1;
        }

        if (App::getConfig()->getAppName($langCode) !== null) {
            $this->info('This language already added. Nothing changed.');

            return 0;
        }
        App::getConfig()->setAppName($this->getInput('Name of the website in the new language:'), $langCode);
        App::getConfig()->setDescription($this->getInput('Description of the website in the new language:'), $langCode);
        App::getConfig()->setTitle($this->getInput('Default page title in the new language:'), $langCode);
        $writingDir = $this->select('Select writing direction:', [
            'ltr', 'rtl'
        ]);

        $writer = new LangClassWriter($langCode, $writingDir);
        $writer->writeClass();
        $this->success('Language added. Also, a class for the language '
                .'is created at "'.APP_DIR.'\Langs" for that language.');

        return 0;
    }
    private function addSmtp(): int {
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
        $smtpConn->setAccountName($this->getInput('Give your connection a friendly name:', 'smtp-connection-'.count(App::getConfig()->getSMTPConnections())));
        $this->println('Trying to connect. This can take up to 1 minute...');
        $server = new SMTPServer($smtpConn->getServerAddress(), $smtpConn->getPort());

        try {
            if ($server->authLogin($smtpConn->getUsername(), $smtpConn->getPassword())) {
                $this->success('Connected. Adding connection information...');
                App::getConfig()->addOrUpdateSMTPAccount($smtpConn);
                $this->success('Connection information was stored in application configuration.');
            } else {
                $this->error('Unable to connect to SMTP server.');
                $this->println('Error Information: '.$server->getLastResponse());

                $this->confirmAdd($smtpConn);
            }
        } catch (SMTPException $ex) {
            $this->error('An exception with message "'.$ex->getMessage().'" was thrown while trying to connect.');
            $this->confirmAdd($smtpConn);
        }

        return 0;
    }
    private function confirmAdd($smtpOrDbConn) {
        if ($this->confirm('Would you like to store connection information anyway?', false)) {
            if ($smtpOrDbConn instanceof SMTPAccount) {
                App::getConfig()->addOrUpdateSMTPAccount($smtpOrDbConn);
            } else if ($smtpOrDbConn instanceof ConnectionInfo) {
                App::getConfig()->addOrUpdateDBConnection($smtpOrDbConn);
            }
            $this->success('Connection information was stored in application configuration.');
        }
    }
    private function tryConnect($connectionInfo) {
        try {
            $db = new DB($connectionInfo);
            $db->getConnection();

            return true;
        } catch (DatabaseException $ex) {
            return $ex;
        }
    }
}
