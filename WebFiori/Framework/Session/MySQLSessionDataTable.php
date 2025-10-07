<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Session;

use WebFiori\Database\MySql\MySQLTable;

/**
 * A class which represents the database table 'session_data'.
 * The table which is associated with this class will have the following columns:
 * <ul>
 * <li><b>s-id</b>: Name in database: 's_id'. Data type: 'varchar'.</li>
 * <li><b>chunk-number</b>: Name in database: 'chunk_number'. Data type: 'int'.</li>
 * <li><b>data</b>: Name in database: 'data'. Data type: 'nvarchar'.</li>
 * </ul>
 */
class MySQLSessionDataTable extends MySQLTable {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('session_data');
        $this->setComment('This table is used to hold the data part of sessions.');
        $this->addColumns([
            's-id' => [
                'type' => 'varchar',
                'size' => '128',
                'primary' => true,
                'comment' => 'The ID of the session. '
                .'Taken from main sessions table.',
            ],
            'chunk-number' => [
                'type' => 'int',
                'primary' => true,
                'comment' => 'The number of data chunk.',
            ],
            'data' => [
                'type' => 'varchar',
                'size' => '1000',
                'comment' => 'One data chunk of size 1000.',
            ],
        ]);

        $this->addReference(new MySQLSessionsTable(), ['s-id'], 'session_data_fk', 'cascade', 'cascade');
    }
}

return __NAMESPACE__;
