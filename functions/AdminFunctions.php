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
 * A class that contains administrative functions
 *
 * @author Ibrahim
 * @version 1.0
 */
class AdminFunctions extends Functions{
    private static $singleton;
    /**
     * 
     * @return AdminFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton !== NULL){
            return self::$singleton;
        }
        self::$singleton = new AdminFunctions();
        return self::$singleton;
    }
    
    public function __construct() {
        parent::__construct();
    }
    /**
     * Initialize database if not initialized.
     * @return boolean <b>TRUE</b> If initialized. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function createDatabase(){
        $this->useDatabase();
        $schema = new DatabaseSchema();
        //creating any random query object just to execute create
        //tables statements.
        $q = new UserQuery();
        $q->setQuery($schema->getSchema(), 'update');
        if($this->excQ($q)){
            return TRUE;
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
    }
    
    public function updateDatabaseAttributes($host,$username,$pass,$dbName,$createDb=false){
        
    }
    
    
}
