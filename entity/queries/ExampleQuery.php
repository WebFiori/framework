<?php

/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
 * An example that shows how to define database table.
 *
 * @author Ibrahim
 */
class ExampleQuery extends MySQLQuery{
    /**
     * Each query class, must be linked to database table. The table is represented 
     * by the class 'Table' The table will contain 
     * all needed information about the columns and keys.
     * @var Table 
     */
    private $table;
    /**
     * Constructs new instance of the class.
     */
    public function __construct() {
        parent::__construct();
        $this->table = new Table('example_table');
        
        //after this step, stat by adding columns to the table and 
        //setting the attibutes of each column. 
        //after adding columns, you can start by adding foreign keys.
    }
    /**
     * This function must be implemented in a way that it returns the linked Table instance.
     * @return Table
     */
    public function getStructure() {
        return $this->table;
    }

}
