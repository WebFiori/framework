<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require '../../root.php';
Util::displayErrors();
class Q1 extends MySQLQuery{
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new Table('q1_table');
    }
    public function getStructure() {
        return $this->table;
    }

}
class Q2 extends MySQLQuery{
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new Table('q2_table');
    }
    public function getStructure() {
        return $this->table;
    }

}

DatabaseSchema::get()->add('Q1', 60);
DatabaseSchema::get()->add('Q2', 30);
Util::print_r(DatabaseSchema::get()->getClassNames());
Util::print_r(DatabaseSchema::get()->getSchema());
