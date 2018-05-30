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
 * A class to initiate database tables.
 *
 * @author Ibrahim
 * @version 1.1
 */
class DatabaseSchema {
    private static $schema;
    
    public function get() {
        if(self::$schema != NULL){
            return self::$schema;
        }
        self::$schema = new DatabaseSchema();
        return self::$schema;
    }
    
    private $queries;
    public function getSchema() {
        $schema = '';
        foreach ($this->queries as $query){
            $queryObj = new $query();
            $queryObj->createStructure();
            $schema .= $queryObj->getQuery();
        }
        return $schema;
    }
    public function __construct() {
        $this->queries = array();
        
    }
    
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