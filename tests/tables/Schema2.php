<?php
namespace tables;

use webfiori\framework\DB;
/**
 * Description of Schema
 *
 * @author Ibrahim
 */
class Schema2 extends DB {
    public function __construct() {
        parent::__construct('testing-connection');
        $this->addTable(new PositionInfoTable());
    }
}
