<?php
namespace app\database;

use webfiori\database\mysql\MySQLTable;
/**
 * Description of TestTable
 *
 * @author Ibrah
 */
class TestTable extends MySQLTable {
    public function __construct() {
        parent::__construct('test');
        $this->addColumns([
            'id' => [
                'type' => 'int',
                'size' => 11
            ]
        ]);
    }
}
