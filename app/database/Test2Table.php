<?php
namespace app\database;

use WebFiori\Database\MySql\MySQLTable;
/**
 * Description of TestTable
 *
 * @author Ibrahim
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
