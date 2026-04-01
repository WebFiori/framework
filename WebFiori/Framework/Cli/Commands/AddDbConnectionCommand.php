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
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Framework\App;
use WebFiori\Framework\DB;

/**
 * A command which is used to add a database connection.
 *
 * @author Ibrahim
 *
 */
class AddDbConnectionCommand extends Command {
    public function __construct() {
        parent::__construct('add:db-connection', [
            new Argument('--db-type', 'The type of the database server. Supported values: '.implode(', ', ConnectionInfo::SUPPORTED_DATABASES).'.', true),
            new Argument('--host', 'The address of the database host.', true),
            new Argument('--port', 'Port number of the database server.', true),
            new Argument('--user', 'The username to use when connecting to the database.', true),
            new Argument('--password', 'The password to use when connecting to the database.', true),
            new Argument('--database', 'The name of the database to connect to.', true),
            new Argument('--name', 'A friendly name to identify the connection.', true),
            new Argument('--extras', 'A JSON string of key-value pairs with extra connection information.', true),
            new Argument('--no-check', 'If provided, the connection will be added without the attempt to check if provided credentials are valid.', true),
        ], 'Add a database connection.');
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $dbTypeArg = $this->getArgValue('--db-type');
        $supportedDbs = ConnectionInfo::SUPPORTED_DATABASES;

        if ($dbTypeArg !== null && in_array($dbTypeArg, $supportedDbs)) {
            $dbType = $dbTypeArg;
        } else {
            if ($dbTypeArg !== null && !in_array($dbTypeArg, $supportedDbs)) {
                $this->warning("Database not supported: $dbTypeArg");
            }
            $dbType = $this->select('Select database type:', $supportedDbs);
        }

        $connInfoObj = new ConnectionInfo($dbType, 'root', 'pass', 'ok');

        $hostArg = $this->getArgValue('--host');
        $connInfoObj->setHost($hostArg !== null ? $hostArg : $this->getInput('Database host:', '127.0.0.1'));

        $portArg = $this->getArgValue('--port');
        $connInfoObj->setPort($portArg !== null ? (int) $portArg : $this->getInput('Port number:', 3306));

        $userArg = $this->getArgValue('--user');
        $connInfoObj->setUsername($userArg !== null ? $userArg : $this->getInput('Username:'));

        $passArg = $this->getArgValue('--password');
        $connInfoObj->setPassword($passArg !== null ? $passArg : $this->getMaskedInput('Password:'));

        $dbArg = $this->getArgValue('--database');
        $connInfoObj->setDBName($dbArg !== null ? $dbArg : $this->getInput('Database name:'));

        $defaultName = 'db-connection-'.(count(App::getConfig()->getDBConnections()) + 1);
        $nameArg = $this->getArgValue('--name');
        $connInfoObj->setName($nameArg !== null ? $nameArg : $this->getInput('Give your connection a friendly name:', $defaultName));

        $extrasArg = $this->getArgValue('--extras');

        if ($extrasArg !== null) {
            $decoded = json_decode($extrasArg, true);

            if (is_array($decoded)) {
                $connInfoObj->setExtras($decoded);
            }
        }

        if ($this->isArgProvided('--no-check')) {
            App::getConfig()->addOrUpdateDBConnection($connInfoObj);
            $this->success('Connection information was stored in application configuration.');

            return 0;
        }

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

            if ($this->confirm('Would you like to store connection information anyway?', false)) {
                App::getConfig()->addOrUpdateDBConnection($connInfoObj);
                $this->success('Connection information was stored in application configuration.');
            }
        }

        return 0;
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
