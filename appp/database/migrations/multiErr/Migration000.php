<?php
namespace app\database\migrations\multiErr;

use WebFiori\Database\Database;
use WebFiori\Database\migration\AbstractMigration;
/**
 * A database migration class.
 */
class Migration000 extends AbstractMigration {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('Third One', 2);
    }
    /**
     * Performs the action that will apply the migration.
     * 
     * @param Database $schema The database at which the migration will be applied to.
     */
    public function up(Database $schema) {
        $this->x();
    }
    /**
     * Performs the action that will revert back the migration.
     * 
     * @param Database $schema The database at which the migration will be applied to.
     */
    public function down(Database $schema) {
        $schema->y();
    }
}
