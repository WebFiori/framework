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
        parent::__construct('add:db-connection', [], 'Add a database connection.');
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
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
