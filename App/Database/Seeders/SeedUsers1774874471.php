<?php
namespace App\Database\Seeders;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;
/**
 * Seeds 30 random users
 *
 * @author Ibrahim
 */
class SeedUsers1774874471 extends AbstractSeeder {
        /**
         * Run the seeder to populate the database with data.
         *
         * @param Database $db The database instance to execute seeding on.
         */
        public function run(Database $db): void {
                $rawDb = new \WebFiori\Database\Database($db->getConnectionInfo());
        $rawDb->table('users')->insert(['name' => 'Ibrahim', 'email' => 'user@ibrahim.com'])->execute();
        for ($i = 1; $i <= 10; $i++) {
            $rawDb->table('users')->insert(['name' => 'User'.$i, 'email' => 'user'.$i.'@example.com'])->execute();
        }
        }
}