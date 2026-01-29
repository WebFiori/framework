<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Command;
use WebFiori\Mail\Exceptions\SMTPException;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\SMTPServer;
use WebFiori\Framework\App;

/**
 * A command which is used to add an SMTP account.
 *
 * @author Ibrahim
 *
 */
class AddSmtpConnectionCommand extends Command {
    public function __construct() {
        parent::__construct('add:smtp-connection', [], 'Add an SMTP account.');
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
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

                if ($this->confirm('Would you like to store connection information anyway?', false)) {
                    App::getConfig()->addOrUpdateSMTPAccount($smtpConn);
                    $this->success('Connection information was stored in application configuration.');
                }
            }
        } catch (SMTPException $ex) {
            $this->error('An exception with message "'.$ex->getMessage().'" was thrown while trying to connect.');

            if ($this->confirm('Would you like to store connection information anyway?', false)) {
                App::getConfig()->addOrUpdateSMTPAccount($smtpConn);
                $this->success('Connection information was stored in application configuration.');
            }
        }

        return 0;
    }
}
