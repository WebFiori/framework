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

use WebFiori\Cli\Argument;
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
        parent::__construct('add:smtp-connection', [
            new Argument('--host', 'The address of SMTP server host.', true),
            new Argument('--port', 'Port number of the SMTP server.', true),
            new Argument('--user', 'The username to use when connecting to the server.', true),
            new Argument('--password', 'The password to use when connecting to the server.', true),
            new Argument('--sender-address', 'The email address that will appear as sender.', true),
            new Argument('--sender-name', 'The name that will appear as sender.', true),
            new Argument('--name', 'A friendly name to identify the connection.', true),
            new Argument('--oauth-token', 'OAuth access token to use for authentication instead of password.', true),
            new Argument('--no-check', 'If provided, the connection will be added without the attempt to check if provided credentials are valid.', true),
        ], 'Add an SMTP account.');
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $smtpConn = new SMTPAccount();

        $hostArg = $this->getArgValue('--host');
        $smtpConn->setServerAddress($hostArg !== null ? $hostArg : $this->getInput('SMTP Server address:', '127.0.0.1'));

        $smtpConn->setPort(25);
        $addr = $smtpConn->getServerAddress();

        if ($addr == 'smtp.outlook.com'
            || $addr == 'outlook.office365.com'
            || $addr == 'smtp.office365.com') {
            $smtpConn->setPort(587);
        } else if ($addr == 'smtp.gmail.com'
            || $addr == 'smtp.mail.yahoo.com') {
            $smtpConn->setPort(465);
        }

        $portArg = $this->getArgValue('--port');
        $smtpConn->setPort($portArg !== null ? (int) $portArg : $this->getInput('Port number:', $smtpConn->getPort()));

        $userArg = $this->getArgValue('--user');
        $smtpConn->setUsername($userArg !== null ? $userArg : $this->getInput('Username:'));

        $passArg = $this->getArgValue('--password');
        $smtpConn->setPassword($passArg !== null ? $passArg : $this->getMaskedInput('Password:'));

        $senderAddrArg = $this->getArgValue('--sender-address');
        $smtpConn->setAddress($senderAddrArg !== null ? $senderAddrArg : $this->getInput('Sender email address:', $smtpConn->getUsername()));

        $senderNameArg = $this->getArgValue('--sender-name');
        $smtpConn->setSenderName($senderNameArg !== null ? $senderNameArg : $this->getInput('Sender name:', $smtpConn->getAddress()));

        $defaultName = 'smtp-connection-'.count(App::getConfig()->getSMTPConnections());
        $nameArg = $this->getArgValue('--name');
        $smtpConn->setAccountName($nameArg !== null ? $nameArg : $this->getInput('Give your connection a friendly name:', $defaultName));

        $tokenArg = $this->getArgValue('--oauth-token');

        if ($tokenArg !== null) {
            $smtpConn->setAccessToken($tokenArg);
        }

        if ($this->isArgProvided('--no-check')) {
            App::getConfig()->addOrUpdateSMTPAccount($smtpConn);
            $this->success('Connection information was stored in application configuration.');

            return 0;
        }

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
