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

/**
 * A class to create database schema.
 *
 * @author Ibrahim
 * 
 * @version 1.2.1
 */
class DatabaseSchema {
    /**
     * An array which contains all the names of the classes which 
     * extends the base class MySQLQuery.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $queries;
    /**
     * Singleton of <b>DatabaseSchema</b>.
     * 
     * @var DatabaseSchema
     * 
     * @since 1.0 
     */
    private static $schema;
    private function __construct() {
        $this->queries = [
            //add your query class names in here

            //0=>'ExampleQuery'
        ];
    }
    /**
     * Adds a query builder class name to the set of classes that represents 
     * database tables.
     * 
     * @param string $queryClassName The name of query builder class. If the 
     * class is contained in a name space, the name of the name space must 
     * be included (e.g. 'myNs\myFolder\MyQuery').
     * 
     * @param int $order The order of query builder table in the database. Used to 
     * make sure that the tables that are referenced by other tables put first. 
     * It must be a value greater than or equal to 20. If the given order is 
     * taken, the name will be added to the last position.
     * 
     * @return boolean Once the name is added, the method will return true. 
     * If the given name is already added or it is invalid, the method will 
     * return false. The given name will be considered invalid only if 
     * no class was found which correspond to the given name.
     * 
     * @since 1.0
     */
    public function add($queryClassName,$order) {
        if (class_exists($queryClassName)) {
            foreach ($this->queries as $q) {
                if ($q == $queryClassName) {
                    return false;
                }
            }

            if (gettype($order) == 'integer') {
                if ($order >= 20) {
                    if (!isset($this->queries[$order])) {
                        $this->queries[$order] = $queryClassName;
                    } else {
                        $this->_orderCheck($queryClassName);
                    }
                }
            } else {
                $this->_orderCheck($queryClassName);
            }

            return true;
        }

        return false;
    }
    /**
     * Returns a singleton of the class DatabaseSchema.
     * 
     * @return DatabaseSchema
     * 
     * @since 1.0
     */
    public static function get() {
        if (self::$schema != null) {
            return self::$schema;
        }
        self::$schema = new DatabaseSchema();

        return self::$schema;
    }
    /**
     * Returns the array which contains the names of query builder classes.
     * 
     * @return array The array which contains the names of query builder classes.
     * 
     * @since 1.2
     */
    public function getClassNames() {
        return $this->queries;
    }
    /**
     * Creates and returns a query that can be used to create MySQL database.
     * 
     * The generated SQL query will have 'create database' statement in 
     * addition to 'use' statement.
     * 
     * @param string $schemaName The name of the database.
     * 
     * @return string A string that can be used to create MySQL database.
     * 
     * @since 1.2.1
     */
    public function getCreateDatabaseStatement($schemaName) {
        return 'create database if not exists '.$schemaName.';'."\n"
             .'use '.$schemaName.';'."\n"
             .$this->getSchema();
    }
    /**
     * Returns the order of the last query builder class name.
     * 
     * @return int The order of the last  query builder class name. If the 
     * schema has no query builder class names, the method will return -1.
     * 
     * @since 1.2
     */
    public function getLastOrder() {
        $keys = array_keys($this->queries);
        $count = count($keys);

        if ($count > 0) {
            $max = $keys[0];

            foreach ($keys as $index) {
                if ($index > $max) {
                    $max = $index;
                }
            }

            if ($max < 20) {
                return 20;
            }

            return $max;
        }

        return -1;
    }
    /**
     * Returns the order of query builder class given its name.
     * 
     * @param string $queryClassName The name of the query builder class. 
     * 
     * @return int If the given query builder class was added, the method will return 
     * its order. If no class was found which has the given name, the method will 
     * return -1.
     * 
     * @since 1.2
     */
    public function getOrder($queryClassName) {
        $count = count($this->queries);
        $keys = array_keys($this->queries);

        for ($x = 0 ; $x < $count ; $x++) {
            if ($queryClassName == $this->queries[$keys[$x]]) {
                return $keys[$x];
            }
        }

        return -1;
    }
    /**
     * Returns a string which represents the database schema.
     * 
     * @return string A string which represents the database schema. If 
     * the string is executed, it will create database tables.
     * 
     * @since 1.0
     */
    public function getSchema() {
        $schemaStr = '';
        $lastSchemaIndex = $this->getLastOrder();

        for ($x = 0 ;$x <= $lastSchemaIndex ; $x++) {
            if (isset($this->queries[$x])) {
                $queryObj = new $this->queries[$x]();
                $queryObj->createStructure();
                $schemaStr .= $queryObj->getQuery();
            }
        }

        return $schemaStr;
    }
    private function _orderCheck($queryClassName) {
        $lstOrder = $this->getLastOrder();

        if ($lstOrder != -1) {
            $this->queries[$lstOrder] = $queryClassName;
        } else {
            $this->queries[20] = $queryClassName;
        }
    }
}
