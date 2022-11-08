<?php
namespace app\database;

use webfiori\database\mysql\MySQLTable;
/**
 * Description of TestTable
 *
 * @author Ibrah
 */
class Test2Table extends MySQLTable {
    public function __construct() {
        parent::__construct('test2');
        $this->addColumns([
            'user-id' => [
                'type' => 'int',
                'size' => 11
            ]
        ]);
        $this->addReference(new TestTable(), ['user-id' => 'id'], 'user_id_fk', 'cascade', 'cascade');
    }
}
