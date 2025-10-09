<?php
namespace App\Database;

use WebFiori\Database\MySql\MySQLTable;
/**
 * A class which represents the database table 'test'.
 * The table which is associated with this class will have the following columns:
 * <ul>
 * <li><b>id</b>: Name in database: 'id'. Data type: 'int'.</li>
 * <li><b>new-col</b>: Name in database: 'new_col'. Data type: 'int'.</li>
 * </ul>
 */
class TestTable extends MySQLTable {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('test');
        $this->addColumns([
            'id' => [
                'type' => 'int',
                'size' => '11',
            ],
        ]);
    }
}
