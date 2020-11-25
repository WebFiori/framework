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

use webfiori\database\Database;
use webfiori\database\ConnectionInfo;
use webfiori\database\DatabaseException;
use webfiori\conf\Config;

/**
 * A class that can be used to represent system database.
 * 
 * The developer can extend this class to have his own database schema. The main 
 * aim of this class is to make it easy for developers to use the connections 
 * which are stored in the class 'Config'.
 *
 * @author Ibrahim
 * 
 * @version 1.0
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
        $conn = Config::getDBConnection($connName);
        if (!($conn instanceof ConnectionInfo)) {
            throw new DatabaseException("No connection was found which has the name '$connName'.");
        }
        parent::__construct($conn);
    }
}
