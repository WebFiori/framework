<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require '../../root.php';
Util::displayErrors();
class Q1 extends QueryBuilder{
    private $table;
    public function __construct() {
        parent::__construct(10);
        $this->table = new Table('q1_table');
    }
    public function getStructure() {
        return $this->table;
    }

}
class Q2 extends QueryBuilder{
    private $table;
    public function __construct() {
        parent::__construct(3);
        $this->table = new Table('q2_table');
    }
    public function getStructure() {
        return $this->table;
    }

}

$q1 = new Q1();
$q2 = new Q2();
Util::print_r(DatabaseSchema::get()->getClassNames());
Util::print_r(DatabaseSchema::get()->getSchema());
