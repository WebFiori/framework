<?php
namespace WebFiori\Framework\Test\Config;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Config\JsonDriver;

/**
 * End-to-end integration tests for JsonDriver config file composition via extends.
 *
 * Tests exercise the full pipeline: JsonDriver::initialize() resolves the
 * inheritance tree and all public accessor methods return the merged values.
 *
 * Each test backs up app-config.json, replaces it with an extends-enabled
 * version, creates sibling JSON files, then restores everything in tearDown.
 *
 * @since 3.1.0
 */
class JsonDriverInheritanceTest extends TestCase {
    private string $backupPath;
    private string $configPath;
    private string $entryPoint;

    /**
     * @test
     * A scalar absent from the child is inherited from the base.
     */
    public function testBaseScalarInheritedWhenAbsentInChild(): void {
        $this->writeConfig('test-base.json', [
            'primary-lang' => 'AR',
        ]);
        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-base.json'],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();

        $this->assertSame('AR', $driver->getPrimaryLanguage(), 'Scalar absent from child should come from base.');
    }

    /**
     * @test
     * Child env-var value overrides the same key from base.
     */
    public function testChildEnvVarOverridesBase(): void {
        $this->writeConfig('test-base.json', [
            'env-vars' => ['SHARED' => ['value' => 'base-value', 'description' => '']],
        ]);
        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-base.json'],
            'env-vars' => ['SHARED' => ['value' => 'child-value', 'description' => '']],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();
        $vars = $driver->getEnvVars();

        $this->assertSame('child-value', $vars['SHARED']['value'], 'Child must override base for same key.');
    }

    // =========================================================================
    // scalar replace
    // =========================================================================

    /**
     * @test
     * Child base-url and primary-lang override the base values (replace strategy).
     */
    public function testChildScalarOverridesBase(): void {
        $this->writeConfig('test-base.json', [
            'base-url' => 'https://base.example.com',
            'primary-lang' => 'AR',
        ]);
        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-base.json'],
            'base-url' => 'https://child.example.com',
            'primary-lang' => 'EN',
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();

        $this->assertSame('https://child.example.com', $driver->getBaseURL());
        $this->assertSame('EN', $driver->getPrimaryLanguage());
    }

    // =========================================================================
    // database-connections merge
    // =========================================================================

    /**
     * @test
     * DB connections from multiple bases are all available after merge.
     */
    public function testDatabaseConnectionsMergeFromMultipleBases(): void {
        $this->writeConfig('test-db1.json', [
            'database-connections' => [
                'conn-a' => ['type' => 'mysql', 'host' => 'host-a', 'port' => '3306', 'username' => 'u', 'password' => '', 'database' => 'db-a', 'name' => 'conn-a'],
            ],
        ]);
        $this->writeConfig('test-db2.json', [
            'database-connections' => [
                'conn-b' => ['type' => 'mysql', 'host' => 'host-b', 'port' => '3306', 'username' => 'u', 'password' => '', 'database' => 'db-b', 'name' => 'conn-b'],
            ],
        ]);
        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-db1.json', 'test-db2.json'],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();
        $conns = $driver->getDBConnections();

        $this->assertArrayHasKey('conn-a', $conns, 'Connection from first base must exist.');
        $this->assertArrayHasKey('conn-b', $conns, 'Connection from second base must exist.');
        $this->assertSame('host-a', $conns['conn-a']->getHost());
        $this->assertSame('host-b', $conns['conn-b']->getHost());
    }

    // =========================================================================
    // directive stripping
    // =========================================================================

    /**
     * @test
     * extends/inheritance_strategy/write-targets are not exposed as env-vars
     * and do not appear in any public accessor.
     */
    public function testDirectivesNotExposedAsEnvVars(): void {
        $this->writeConfig('test-base.json', ['primary-lang' => 'EN']);
        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-base.json'],
            'inheritance_strategy' => ['env-vars' => 'merge'],
            'write-targets' => ['env-vars' => 'test-env.json'],
            'env-vars' => ['MY_VAR' => ['value' => 'hello', 'description' => '']],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();
        $vars = $driver->getEnvVars();

        $this->assertArrayNotHasKey('extends', $vars);
        $this->assertArrayNotHasKey('inheritance_strategy', $vars);
        $this->assertArrayNotHasKey('write-targets', $vars);
        $this->assertArrayHasKey('MY_VAR', $vars);
        $this->assertSame('hello', $vars['MY_VAR']['value']);
    }

    // =========================================================================
    // env-vars merge
    // =========================================================================

    /**
     * @test
     * Base env-vars are inherited; child env-var is also present; both survive.
     */
    public function testDriverMergesEnvVarsFromBase(): void {
        $this->writeConfig('test-base.json', [
            'env-vars' => ['BASE_API_KEY' => ['value' => 'base-key', 'description' => 'From base']],
        ]);

        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-base.json'],
            'env-vars' => ['CHILD_VAR' => ['value' => 'child-value', 'description' => 'From child']],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();
        $vars = $driver->getEnvVars();

        $this->assertArrayHasKey('BASE_API_KEY', $vars, 'Env var from base must be present after merge.');
        $this->assertSame('base-key', $vars['BASE_API_KEY']['value']);
        $this->assertArrayHasKey('CHILD_VAR', $vars, 'Env var from child must be present.');
        $this->assertSame('child-value', $vars['CHILD_VAR']['value']);
    }

    // =========================================================================
    // write-targets
    // =========================================================================

    /**
     * @test
     * getWriteTarget() returns the configured target file and falls back to
     * the entry point for unconfigured sections.
     */
    public function testGetWriteTargetReturnsConfiguredPath(): void {
        file_put_contents($this->entryPoint, json_encode([
            'write-targets' => [
                'database-connections' => 'test-db1.json',
                'env-vars' => 'test-env.json',
            ],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();

        $this->assertStringEndsWith('test-db1.json', $driver->getWriteTarget('database-connections'));
        $this->assertStringEndsWith('test-env.json', $driver->getWriteTarget('env-vars'));
        $this->assertStringEndsWith('app-config.json', $driver->getWriteTarget('smtp-connections'));
    }

    // =========================================================================
    // smtp-connections merge
    // =========================================================================

    /**
     * @test
     * SMTP accounts from a base file are accessible via the driver.
     */
    public function testSmtpConnectionsInheritedFromBase(): void {
        $this->writeConfig('test-smtp.json', [
            'smtp-connections' => [
                'no-reply' => [
                    'host' => 'smtp.example.com', 'port' => '587',
                    'username' => 'no-reply@example.com', 'password' => 'secret',
                    'address' => 'no-reply@example.com', 'sender-name' => 'My App',
                    'access-token' => null,
                ],
            ],
        ]);
        file_put_contents($this->entryPoint, json_encode([
            'extends' => ['test-smtp.json'],
        ], JSON_PRETTY_PRINT));

        $driver = $this->makeDriver();
        $smtp = $driver->getSMTPConnections();

        $this->assertArrayHasKey('no-reply', $smtp);
        $this->assertSame('smtp.example.com', $smtp['no-reply']->getServerAddress());
    }

    private function makeDriver(): JsonDriver {
        $driver = new JsonDriver();
        $driver->initialize();

        return $driver;
    }

    private function writeConfig(string $name, array $data): void {
        file_put_contents($this->configPath.$name, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function setUp(): void {
        $this->configPath = APP_PATH.'Config'.DIRECTORY_SEPARATOR;
        $this->entryPoint = $this->configPath.'app-config.json';
        $this->backupPath = $this->configPath.'app-config.json.bak';
        copy($this->entryPoint, $this->backupPath);
    }

    protected function tearDown(): void {
        if (file_exists($this->backupPath)) {
            copy($this->backupPath, $this->entryPoint);
            unlink($this->backupPath);
        }

        foreach (glob($this->configPath.'test-*.json') as $f) {
            unlink($f);
        }
    }
}
