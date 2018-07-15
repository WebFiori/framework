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
 * A class that works as interface between MySQLQuery class and the framework.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
abstract class QueryBuilder extends MySQLQuery{
    /**
     * The name of the query builder class.
     * @var string
     * @since 1.0 
     */
    private $className;
    /**
     * Constructs a new instance of the class.
     *  @param int $order The order of query builder table in the database. Used to 
     * make sure that the tables that are referenced by other tables put first. 
     * It must be a value greater than or equal to 0. If the given order is 
     * taken, the name will be added to the last position.
     * @see DatabaseSchema::add()
     * @throws Exception
     */
    public function __construct($order) {
        parent::__construct();
        $this->className = get_class($this);
        $schema = DatabaseSchema::get();
        if($schema->getOrder($this->getName()) == -1){
            if(gettype($order) == 'integer'){
                if($order >= 0){
                    $schema->add($this->getName(), $order);
                    return;
                }
            }
            throw new Exception('Invalid order: '.$order);
        }
    }
    /**
     * Returns the name of the query builder class name.
     * @return string The name of the query builder class name
     * @since 1.0
     */
    public function getName() {
        return $this->className;
    }
    /**
     * Returns the order of the query builder.
     * @return int he order of the query builder as specified by <b>DatabaseSchema::getOrder()</b>. 
     * @see DatabaseSchema::getOrder()
     * @since 1.0
     */
    public function getOrder() {
        return DatabaseSchema::get()->getOrder($this->getName());
    }
}