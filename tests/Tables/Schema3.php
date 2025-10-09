<?php
namespace Tables;

use WebFiori\Framework\DB;
/**
 * Description of Schema
 *
 * @author Ibrahim
 */
class Schema3 extends DB {
    public function __construct() {
        parent::__construct('testing-connection');
        $this->addTable(new PositionInfoTable());
    }
}
