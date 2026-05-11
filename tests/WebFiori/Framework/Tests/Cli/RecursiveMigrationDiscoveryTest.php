<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\DryRunMigrationsCommand;
use WebFiori\Framework\Cli\Commands\FreshMigrationsCommand;
use WebFiori\Framework\Cli\Commands\MigrationsStatusCommand;
use WebFiori\Framework\Cli\Commands\RollbackMigrationsCommand;
use WebFiori\Framework\Cli\Commands\RunMigrationsCommandNew;

/**
 * Test cases for recursive discovery of migrations in subdirectories.
 * 
 * @see https://github.com/WebFiori/framework/issues/317
 * @author Ibrahim
 */
class RecursiveMigrationDiscoveryTest extends CLITestCase {
    private ConnectionInfo $testConnection;
    private array $createdPaths = [];

    /**
     * @test
     * Verify that migrations:run discovers migrations in subdirectories.
     */
    public function testRunDiscoversMigrationsInSubdirectories() {
        $this->createSubdirMigration('Master', 'CreateUsersTable');
        $this->createSubdirMigration('Lms', 'CreateCoursesTable');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Running migrations...', $outputStr);
        $this->assertStringContainsString('CreateUsersTable', $outputStr);
        $this->assertStringContainsString('CreateCoursesTable', $outputStr);
        $this->assertStringContainsString('Info: Applied: 2 migration(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that migrations:run discovers seeders in subdirectories.
     */
    public function testRunDiscoversSeedersInSubdirectories() {
        $this->createSubdirSeeder('Master', 'UsersSeeder');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('UsersSeeder', $outputStr);
        $this->assertStringContainsString('Info: Applied: 1 seeder(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that migrations:fresh discovers migrations in subdirectories.
     */
    public function testFreshDiscoversMigrationsInSubdirectories() {
        $this->createSubdirMigration('Master', 'FreshSubdirMigration');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('FreshSubdirMigration', $outputStr);
        $this->assertStringContainsString('Info: Applied: 1 migration(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that migrations:rollback discovers migrations in subdirectories.
     */
    public function testRollbackDiscoversMigrationsInSubdirectories() {
        $this->createSubdirMigration('Master', 'RollbackSubdirMigration');
        $this->initMigrations();

        // First apply
        $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        // Then rollback
        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolled back: ', $outputStr);
        $this->assertStringContainsString('RollbackSubdirMigration', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that migrations:dry-run discovers migrations in subdirectories.
     */
    public function testDryRunDiscoversMigrationsInSubdirectories() {
        $this->createSubdirMigration('Sustainability', 'DryRunSubdirMigration');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            DryRunMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Pending migrations/seeders:', $outputStr);
        $this->assertStringContainsString('DryRunSubdirMigration', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that migrations:status discovers migrations in subdirectories.
     */
    public function testStatusDiscoversMigrationsInSubdirectories() {
        $this->createSubdirMigration('Lms', 'StatusSubdirMigration');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            MigrationsStatusCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Pending migrations:', $outputStr);
        $this->assertStringContainsString('StatusSubdirMigration', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that deeply nested subdirectories are also discovered.
     */
    public function testRunDiscoversDeeplyNestedMigrations() {
        $this->createDeepSubdirMigration('Master', 'V1', 'DeepNestedMigration');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('DeepNestedMigration', $outputStr);
        $this->assertStringContainsString('Info: Applied: 1 migration(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that a mix of top-level and subdirectory migrations are all discovered.
     */
    public function testRunDiscoversMixedTopLevelAndSubdirectory() {
        $this->createTopLevelMigration('TopLevelMigration');
        $this->createSubdirMigration('Master', 'SubdirMigration');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('TopLevelMigration', $outputStr);
        $this->assertStringContainsString('SubdirMigration', $outputStr);
        $this->assertStringContainsString('Info: Applied: 2 migration(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Verify that migrations:dry-run discovers seeders in subdirectories.
     */
    public function testDryRunDiscoversSeedersInSubdirectories() {
        $this->createSubdirSeeder('Master', 'DryRunSubdirSeeder');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            DryRunMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Pending migrations/seeders:', $outputStr);
        $this->assertStringContainsString('DryRunSubdirSeeder', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    private function initMigrations(): void {
        $this->executeMultiCommand([
            'WebFiori\\Framework\\Cli\\Commands\\InitMigrationsCommand',
            '--connection' => 'test-connection'
        ]);
    }

    private function createSubdirMigration(string $subdir, string $name): void {
        $dir = APP_PATH.'Database'.DS.'Migrations'.DS.$subdir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $this->createdPaths[] = $dir;
        }

        $filePath = $dir.DS.$name.'.php';

        $content = <<<PHP
<?php
namespace App\Database\Migrations\\$subdir;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class $name extends AbstractMigration {
    public function up(Database \$db): void {
        // Test migration in subdirectory
    }
    
    public function down(Database \$db): void {
        // Test rollback
    }
}
PHP;

        file_put_contents($filePath, $content);
        $this->createdPaths[] = $filePath;
    }

    private function createDeepSubdirMigration(string $subdir, string $nestedDir, string $name): void {
        $dir = APP_PATH.'Database'.DS.'Migrations'.DS.$subdir.DS.$nestedDir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $this->createdPaths[] = $dir;
            // Also track parent if it was created
            $parentDir = APP_PATH.'Database'.DS.'Migrations'.DS.$subdir;
            if (!in_array($parentDir, $this->createdPaths)) {
                $this->createdPaths[] = $parentDir;
            }
        }

        $filePath = $dir.DS.$name.'.php';

        $content = <<<PHP
<?php
namespace App\Database\Migrations\\$subdir\\$nestedDir;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class $name extends AbstractMigration {
    public function up(Database \$db): void {
        // Test deeply nested migration
    }
    
    public function down(Database \$db): void {
        // Test rollback
    }
}
PHP;

        file_put_contents($filePath, $content);
        $this->createdPaths[] = $filePath;
    }

    private function createTopLevelMigration(string $name): void {
        $dir = APP_PATH.'Database'.DS.'Migrations';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = $dir.DS.$name.'.php';

        $content = <<<PHP
<?php
namespace App\Database\Migrations;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class $name extends AbstractMigration {
    public function up(Database \$db): void {
        // Top-level migration
    }
    
    public function down(Database \$db): void {
        // Test rollback
    }
}
PHP;

        file_put_contents($filePath, $content);
        $this->createdPaths[] = $filePath;
    }

    private function createSubdirSeeder(string $subdir, string $name): void {
        $dir = APP_PATH.'Database'.DS.'Seeders'.DS.$subdir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $this->createdPaths[] = $dir;
        }

        $filePath = $dir.DS.$name.'.php';

        $content = <<<PHP
<?php
namespace App\Database\Seeders\\$subdir;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class $name extends AbstractSeeder {
    public function run(Database \$db): void {}
    public function rollback(Database \$db): void {}
}
PHP;

        file_put_contents($filePath, $content);
        $this->createdPaths[] = $filePath;
    }

    private function cleanup(): void {
        // Remove files first, then directories (reverse order)
        $files = [];
        $dirs = [];

        foreach ($this->createdPaths as $path) {
            if (is_file($path)) {
                $files[] = $path;
            } else {
                $dirs[] = $path;
            }
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // Sort dirs by depth (deepest first) to avoid non-empty dir errors
        usort($dirs, function ($a, $b) {
            return substr_count($b, DS) - substr_count($a, DS);
        });

        foreach ($dirs as $dir) {
            if (is_dir($dir) && $this->isDirEmpty($dir)) {
                rmdir($dir);
            }
        }

        $this->createdPaths = [];
    }

    private function isDirEmpty(string $dir): bool {
        $items = scandir($dir);
        return count($items) === 2; // only . and ..
    }

    private function setupTestConnection(): void {
        $this->testConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $this->testConnection->setName('test-connection');
        App::getConfig()->addOrUpdateDBConnection($this->testConnection);
    }

    private function dropSchemaTable(): void {
        try {
            $connection = App::getConfig()->getDBConnection('test-connection');
            if ($connection !== null) {
                $runner = new \WebFiori\Database\Schema\SchemaRunner($connection);
                $runner->dropSchemaTable();
            }
        } catch (\Throwable $e) {
            // Ignore errors during cleanup
        }
    }

    protected function setUp(): void {
        parent::setUp();
        $this->createdPaths = [];
        $this->setupTestConnection();
        $this->dropSchemaTable();
        $this->cleanStrayFiles();
    }

    protected function tearDown(): void {
        $this->cleanup();
        $this->dropSchemaTable();
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }

    /**
     * Remove any .php files left by other tests in Migrations/Seeders directories
     * (excluding .gitkeep). This ensures test isolation when recursive discovery is enabled.
     */
    private function cleanStrayFiles(): void {
        $dirs = [
            APP_PATH.'Database'.DS.'Migrations',
            APP_PATH.'Database'.DS.'Seeders',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile() && $item->getExtension() === 'php') {
                    unlink($item->getRealPath());
                } elseif ($item->isDir()) {
                    // Remove empty subdirs (but not the base dir)
                    $contents = scandir($item->getRealPath());
                    if (count($contents) === 2) { // only . and ..
                        rmdir($item->getRealPath());
                    }
                }
            }
        }
    }
}
