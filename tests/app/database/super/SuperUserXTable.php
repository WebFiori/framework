<?php
namespace app\database\super;

use webfiori\database\mysql\MySQLTable;
use webfiori\database\ColOption;
use webfiori\database\DataType;
/**
 * A class which represents the database table 'super_users'.
 * The table which is associated with this class will have the following columns:
 * <ul>
 * <li><b>id</b>: Name in database: 'id'. Data type: 'int'.</li>
 * <li><b>first-name</b>: Name in database: 'first_name'. Data type: 'varchar'.</li>
 * <li><b>is-happy</b>: Name in database: 'is_happy'. Data type: 'bool'.</li>
 * </ul>
 */
class SuperUserXTable extends MySQLTable {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('super_users');
        $this->setComment('A table to hold super users information.');
        $this->addColumns([
            'id' => [
                ColOption::TYPE => DataType::INT,
                ColOption::SIZE => '11',
                ColOption::COMMENT => 'The unique ID of the super user.',
            ],
            'first-name' => [
                ColOption::TYPE => DataType::VARCHAR,
                ColOption::SIZE => '50',
                ColOption::COMMENT => 'No Comment.',
            ],
            'is-happy' => [
                ColOption::TYPE => DataType::BOOL,
                ColOption::DEFAULT => true,
                ColOption::COMMENT => 'Check if the hero is happy or not.',
            ],
        ]);
    }
}
