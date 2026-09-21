<?php
namespace WebFiori\Framework\Test\Config;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Config\JsonConfigInheritance;
use WebFiori\Framework\Exceptions\ConfigurationException;

/**
 * Tests for JsonConfigInheritance — the extends composition feature (ADR-0053).
 */
class JsonConfigInheritanceTest extends TestCase {
    private string $dir;

    /**
     * @test
     * Child value wins over base for scalar (replace strategy).
     */
    public function testChildWinsScalar(): void {
        $this->write('base.json', ['base-url' => 'https://base.example.com', 'primary-lang' => 'EN']);
        $child = $this->write('child.json', [
            'extends' => ['base.json'],
            'base-url' => 'https://child.example.com',
        ]);

        $result = (new JsonConfigInheritance())->resolve($child);

        $this->assertSame('https://child.example.com', $result['base-url']);
        $this->assertSame('EN', $result['primary-lang']); // inherited from base
    }

    /**
     * @test
     * Cycle throws ConfigurationException with the cycle in the message.
     */
    public function testCycleThrows(): void {
        $this->write('a.json', ['extends' => ['b.json'], 'env-vars' => []]);
        $a = $this->write('a.json', ['extends' => ['b.json'], 'env-vars' => []]);
        $this->write('b.json', ['extends' => ['a.json'], 'env-vars' => []]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/circular/i');

        (new JsonConfigInheritance())->resolve($a);
    }

    /**
     * @test
     * Diamond: A extends [B, C], both extend D. D is resolved once.
     */
    public function testDiamondAllowed(): void {
        $this->write('d.json', ['env-vars' => ['D_VAR' => ['value' => 'd', 'description' => '']]]);
        $this->write('b.json', [
            'extends' => ['d.json'],
            'env-vars' => ['B_VAR' => ['value' => 'b', 'description' => '']],
        ]);
        $this->write('c.json', [
            'extends' => ['d.json'],
            'env-vars' => ['C_VAR' => ['value' => 'c', 'description' => '']],
        ]);
        $a = $this->write('a.json', [
            'extends' => ['b.json', 'c.json'],
        ]);

        // Must not throw; must contain all vars.
        $result = (new JsonConfigInheritance())->resolve($a);

        $this->assertSame('d', $result['env-vars']['D_VAR']['value']);
        $this->assertSame('b', $result['env-vars']['B_VAR']['value']);
        $this->assertSame('c', $result['env-vars']['C_VAR']['value']);
    }

    /**
     * @test
     * extends, inheritance_strategy, and write-targets are stripped from result.
     */
    public function testDirectivesStrippedFromResult(): void {
        $this->write('base.json', ['primary-lang' => 'EN']);
        $child = $this->write('child.json', [
            'extends' => ['base.json'],
            'inheritance_strategy' => ['env-vars' => 'merge'],
            'write-targets' => ['env-vars' => 'env-vars.json'],
        ]);

        $result = (new JsonConfigInheritance())->resolve($child);

        $this->assertArrayNotHasKey('extends', $result);
        $this->assertArrayNotHasKey('inheritance_strategy', $result);
    }

    /**
     * @test
     * Collections (env-vars) merge — child adds to base, same key = child wins.
     */
    public function testEnvVarsMerge(): void {
        $this->write('base.json', [
            'env-vars' => ['BASE_VAR' => ['value' => 'base', 'description' => '']],
        ]);
        $child = $this->write('child.json', [
            'extends' => ['base.json'],
            'env-vars' => [
                'BASE_VAR' => ['value' => 'overridden', 'description' => ''],
                'CHILD_VAR' => ['value' => 'child', 'description' => ''],
            ],
        ]);

        $result = (new JsonConfigInheritance())->resolve($child);

        $this->assertSame('overridden', $result['env-vars']['BASE_VAR']['value']);
        $this->assertSame('child', $result['env-vars']['CHILD_VAR']['value']);
    }

    /**
     * @test
     * inheritance_strategy override: force replace on a normally-merged section.
     */
    public function testInheritanceStrategyOverrideReplace(): void {
        $this->write('base.json', [
            'database-connections' => [
                'conn1' => ['type' => 'mysql', 'host' => 'base-host', 'name' => 'conn1'],
            ],
        ]);
        $child = $this->write('child.json', [
            'extends' => ['base.json'],
            'inheritance_strategy' => ['database-connections' => 'replace'],
            'database-connections' => [
                'conn2' => ['type' => 'mysql', 'host' => 'child-host', 'name' => 'conn2'],
            ],
        ]);

        $result = (new JsonConfigInheritance())->resolve($child);

        // Replace strategy: only child's conn2 survives.
        $this->assertArrayNotHasKey('conn1', $result['database-connections']);
        $this->assertArrayHasKey('conn2', $result['database-connections']);
    }

    /**
     * @test
     * Missing extended file throws ConfigurationException.
     */
    public function testMissingExtendedFileThrows(): void {
        $child = $this->write('child.json', ['extends' => ['does-not-exist.json']]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/not found/i');

        (new JsonConfigInheritance())->resolve($child);
    }

    /**
     * @test
     * Multi-level chain: A extends B extends C — all levels merged correctly.
     */
    public function testMultiLevelChain(): void {
        $this->write('c.json', ['env-vars' => ['C_VAR' => ['value' => 'c', 'description' => '']]]);
        $this->write('b.json', [
            'extends' => ['c.json'],
            'env-vars' => ['B_VAR' => ['value' => 'b', 'description' => '']],
        ]);
        $a = $this->write('a.json', [
            'extends' => ['b.json'],
            'env-vars' => ['A_VAR' => ['value' => 'a', 'description' => '']],
        ]);

        $result = (new JsonConfigInheritance())->resolve($a);

        $this->assertSame('a', $result['env-vars']['A_VAR']['value']);
        $this->assertSame('b', $result['env-vars']['B_VAR']['value']);
        $this->assertSame('c', $result['env-vars']['C_VAR']['value']);
    }

    /**
     * @test
     * Multiple bases (array extends) — left-to-right merge, child on top.
     */
    public function testMultipleBasesLeftToRight(): void {
        $this->write('b1.json', ['env-vars' => ['V1' => ['value' => 'b1', 'description' => '']]]);
        $this->write('b2.json', ['env-vars' => ['V1' => ['value' => 'b2', 'description' => ''], 'V2' => ['value' => 'b2', 'description' => '']]]);
        $child = $this->write('child.json', [
            'extends' => ['b1.json', 'b2.json'],
        ]);

        $result = (new JsonConfigInheritance())->resolve($child);

        // b2 is later → b2 wins over b1 for V1.
        $this->assertSame('b2', $result['env-vars']['V1']['value']);
        $this->assertSame('b2', $result['env-vars']['V2']['value']);
    }

    /**
     * @test
     * File with no extends key returns its own data unchanged.
     */
    public function testNoExtendsReturnsSelfData(): void {
        $file = $this->write('plain.json', [
            'base-url' => 'https://example.com',
            'env-vars' => ['MY_VAR' => ['value' => 'hello', 'description' => '']],
        ]);

        $result = (new JsonConfigInheritance())->resolve($file);

        $this->assertSame('https://example.com', $result['base-url']);
        $this->assertSame('hello', $result['env-vars']['MY_VAR']['value']);
    }

    private function write(string $name, array $data): string {
        $path = $this->dir.DIRECTORY_SEPARATOR.$name;
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return $path;
    }

    protected function setUp(): void {
        $this->dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_extends_test_'.uniqid();
        mkdir($this->dir, 0755, true);
    }

    protected function tearDown(): void {
        array_map('unlink', glob($this->dir.'/*.json'));
        rmdir($this->dir);
    }
}
