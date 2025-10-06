<?php
namespace app\database\migrations\multi;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;
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
    public function up(Database $schema) : void {
        //TODO: Implement the action which will apply the migration to database.
    }
    /**
     * Performs the action that will revert back the migration.
     * 
     * @param Database $schema The database at which the migration will be applied to.
     */
    public function down(Database $schema) : void {
        //TODO: Implement the action which will revert back the migration.
    }
}
