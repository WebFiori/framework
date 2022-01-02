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
namespace webfiori\framework;

use webfiori\database\ConnectionInfo;
use webfiori\database\Database;
use webfiori\database\DatabaseException;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\Table;

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
     * specified when the connection was added to the file 'Config.php'.
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
        $conn = WebFioriApp::getAppConfig()->getDBConnection($connName);

        if (!($conn instanceof ConnectionInfo)) {
            throw new DatabaseException("No connection was found which has the name '$connName'.");
        }
        parent::__construct($conn);
    }
    /**
     * Adds a table to the instance.
     * 
     * @param Table $table the table that will be added.
     * 
     * @return boolean If the table is added, the method will return true. False 
     * otherwise.
     * 
     * @since 1.0
     */
    public function addTable(Table $table) {
        $connInfo = $this->getConnectionInfo();

        if ($connInfo === null) {
            
            foreach ($table->getForignKeys() as $fk) {
                parent::addTable($fk->getSource());
            }
            
            return parent::addTable($table);
        } else {
            $connType = $connInfo->getDatabaseType();

            if (($connType == 'mysql' && $table instanceof MySQLTable) 
             || ($connType == 'mssql' && $table instanceof MSSQLTable)) {
                
                foreach ($table->getForignKeys() as $fk) {
                    parent::addTable($fk->getSource());
                }
                
                return parent::addTable($table);
            }
        }
        return false;
    }
    /**
     * Auto-register database tables which exist on a specific directory.
     * 
     * Note that the statement 'return __NAMESPACE__' should be included at the 
     * end of the table class for auto-register to work. If the statement 
     * does not exist, the method will assume that the path is the namespace of 
     * the classes. Also, the classes which represents tables must be suffixed 
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
    public function register($pathToScan) {
        WebFioriApp::autoRegister($pathToScan, function (Table $table, DB $db)
        {
            foreach ($table->getForignKeys() as $fk) {
                $db->addTable($fk->getSource());
            }
            $db->addTable($table);
        }, 'Table', [$this]);
    }
}
