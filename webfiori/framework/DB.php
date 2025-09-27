<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;
use WebFiori\Database\DatabaseException;
use WebFiori\Database\MSSql\MSSQLTable;
use WebFiori\Database\MySql\MySQLTable;
use WebFiori\Database\Table;

/**
 * A class that can be used to represent system database.
 *
 * The developer can extend this class to have his own database schema. The main
 * aim of this class is to make it easy for developers to use the connections
 * which are stored in the class 'Config'.
 *
 * @author Ibrahim
 *
 * @version 1.0.1
 *
 * @since 2.0.0
 */
class DB extends Database {
    /**
     * Creates new instance of the class.
     *
     * @param ConnectionInfo|string $connName This can be an object that holds
     * connection information or a string that represents connection name as
     * specified when the connection was added to application configuration.
     *
     *
     * @throws DatabaseException If no connection was found which has the
     * given name.
     *
     * @since 1.0
     */
    public function __construct($connName) {
        if ($connName instanceof ConnectionInfo) {
            parent::__construct($connName);

            return;
        }
        $conn = App::getConfig()->getDBConnection($connName);


        if (!($conn instanceof ConnectionInfo)) {
            throw new DatabaseException("No connection was found which has the name '$connName'. Driver: ".get_class(App::getConfig()).'.');
        }
        parent::__construct($conn);
    }
    /**
     * Adds a table to the instance.
     *
     * @param Table $table the table that will be added.
     *
     * @param bool $updateOwnerDb If the owner database of the table is already
     * set and this parameter is set to true, the owner database will be
     * updated to the database specified in the instance. This parameter
     * is used to maintain foreign key relationships between tables which
     * belongs to different databases.
     *
     * @return boolean If the table is added, the method will return true. False
     * otherwise.
     *
     * @since 1.0
     */
    public function addTable(Table $table, bool $updateOwnerDb = true) : bool {
        $connType = $this->getConnectionInfo()->getDatabaseType();

        if (($connType == 'mysql' && $table instanceof MySQLTable)
         || ($connType == 'mssql' && $table instanceof MSSQLTable)) {
            foreach ($table->getForeignKeys() as $fk) {
                parent::addTable($fk->getSource(), false);
            }

            return parent::addTable($table, $updateOwnerDb);
        }

        return false;
    }
    /**
     * Auto-register database tables which exist on a specific directory.
     *
     * Note that the classes which represents tables must be suffixed
     * with the word 'Table' (e.g. UsersTable). Also, the registration will depend
     * on the database that the connection is for. For example, if the connection
     * is for MySQL database, then only tables of type 'MySQLTable'.
     *
     * @param string $pathToScan A path which is relative to application source
     * code. For example, If your application folder name is 'app'
     * and if tables classes exist in the folder 'app\database', then the value of this
     * argument must be 'database'.
     *
     * @since 1.0.1
     */
    public function register(string $pathToScan) {
        App::autoRegister($pathToScan, function (Table $table, DB $db)
        {
            foreach ($table->getForeignKeys() as $fk) {
                $db->addTable($fk->getSource(), false);
            }
            $db->addTable($table);
        }, 'Table', [], [$this]);
    }
}
