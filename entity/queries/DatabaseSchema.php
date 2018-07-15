<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
/**
 * A class to create database schema.
 *
 * @author Ibrahim
 * @version 1.1
 */
class DatabaseSchema {
    private static $schema;
    /**
     * Returns a singleton of the class <b>DatabaseSchema</b>.
     * @return DatabaseSchema
     * @since 1.0
     */
    public function get() {
        if(self::$schema != NULL){
            return self::$schema;
        }
        self::$schema = new DatabaseSchema();
        return self::$schema;
    }
    /**
     * An array which contains all the names of the classes which 
     * extends the base class <b>MySQLQuery</b>.
     * @var array
     * @since 1.0 
     */
    private $queries;
    /**
     * Returns a string which represents the database schema.
     * @return string A string which represents the database schema. If 
     * the string is executed, it will create database tables.
     * @since 1.0
     */
    public function getSchema() {
        $schema = '';
        foreach ($this->queries as $query){
            $queryObj = new $query();
            $queryObj->createStructure();
            $schema .= $queryObj->getQuery();
        }
        return $schema;
    }
    
    private function __construct() {
        $this->queries = array();
    }
    /**
     * Adds a query builder class name to the set of classes that represents 
     * database tables.
     * @param string $queryClassName The name of query builder class.
     * @return boolean Once the name is added, the function will return <b>TRUE</b>. 
     * If the given name is already added or it is invalid, the function will 
     * return <b>FALSE</b>. The given name will be considered invalid only if 
     * no class was found which correspond to the given name.
     * @since 1.0
     */
    public function add($queryClassName) {
        if(class_exists($queryClassName)){
            foreach ($this->queries as $q){
                if($q == $queryClassName){
                    return FALSE;
                }
            }
            array_push($this->queries, $queryClassName);
            return TRUE;
        }
        return FALSE;
    }
}