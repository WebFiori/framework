<?php
namespace WebFiori\Framework\Test\Config;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\App;
use WebFiori\Framework\Config\Controller;
use WebFiori\Framework\Config\EnvResolutionStrategy;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\SessionsManager;

/**
 * Tests for EnvResolutionStrategy — the runtime env var resolution feature (#408).
 *
 * @since 3.1.0
 */
class EnvResolutionStrategyTest extends TestCase {
    private EnvResolutionStrategy $originalStrategy;

    protected function setUp(): void {
        // Preserve the current strategy so tests are isolated.
        $this->originalStrategy = Controller::getEnvResolutionStrategy();
        InMemorySessionStorage::reset();
        SessionsManager::setStorage(new InMemorySessionStorage());
    }

    protected function tearDown(): void {
        // Restore strategy and clean up any putenv() calls.
        Controller::setEnvResolutionStrategy($this->originalStrategy);
        putenv('WF_TEST_VAR_408');
    }

    /**
     * @test
     * Default strategy is SYSTEM_FIRST.
     */
    public function testDefaultStrategyIsSystemFirst(): void {
        // Create a fresh state by resetting to default.
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::SYSTEM_FIRST);
        $this->assertSame(
            EnvResolutionStrategy::SYSTEM_FIRST,
            Controller::getEnvResolutionStrategy()
        );
    }

    /**
     * @test
     * App::setEnvResolutionStrategy delegates to Controller.
     */
    public function testAppFacadeDelegatesToController(): void {
        App::setEnvResolutionStrategy(EnvResolutionStrategy::CONFIG_ONLY);
        $this->assertSame(EnvResolutionStrategy::CONFIG_ONLY, Controller::getEnvResolutionStrategy());
        $this->assertSame(EnvResolutionStrategy::CONFIG_ONLY, App::getEnvResolutionStrategy());
    }

    /**
     * @test
     * SYSTEM_FIRST: when the system env is set, it wins over the config value.
     */
    public function testSystemFirstUsesSystemEnvWhenSet(): void {
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::SYSTEM_FIRST);
        putenv('WF_TEST_VAR_408=from-system');

        $resolved = $this->resolveValue('WF_TEST_VAR_408', 'from-config');

        $this->assertSame('from-system', $resolved);
    }

    /**
     * @test
     * SYSTEM_FIRST: when the system env is NOT set, config value is the fallback.
     */
    public function testSystemFirstFallsBackToConfigWhenEnvAbsent(): void {
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::SYSTEM_FIRST);
        putenv('WF_TEST_VAR_408'); // unset

        $resolved = $this->resolveValue('WF_TEST_VAR_408', 'from-config');

        $this->assertSame('from-config', $resolved);
    }

    /**
     * @test
     * CONFIG_ONLY: config value always wins, even when system env is set.
     */
    public function testConfigOnlyIgnoresSystemEnv(): void {
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::CONFIG_ONLY);
        putenv('WF_TEST_VAR_408=from-system');

        $resolved = $this->resolveValue('WF_TEST_VAR_408', 'from-config');

        $this->assertSame('from-config', $resolved);
    }

    /**
     * @test
     * CONFIG_ONLY: config value used even when system env is absent.
     */
    public function testConfigOnlyUsesConfigWhenEnvAbsent(): void {
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::CONFIG_ONLY);
        putenv('WF_TEST_VAR_408');

        $resolved = $this->resolveValue('WF_TEST_VAR_408', 'from-config');

        $this->assertSame('from-config', $resolved);
    }

    /**
     * @test
     * SYSTEM_ONLY: system env is used; config value is ignored.
     */
    public function testSystemOnlyUsesSystemEnv(): void {
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::SYSTEM_ONLY);
        putenv('WF_TEST_VAR_408=from-system');

        $resolved = $this->resolveValue('WF_TEST_VAR_408', 'from-config');

        $this->assertSame('from-system', $resolved);
    }

    /**
     * @test
     * SYSTEM_ONLY: when system env is absent, result is null (config ignored).
     */
    public function testSystemOnlyReturnsNullWhenEnvAbsent(): void {
        Controller::setEnvResolutionStrategy(EnvResolutionStrategy::SYSTEM_ONLY);
        putenv('WF_TEST_VAR_408');

        $resolved = $this->resolveValue('WF_TEST_VAR_408', 'from-config');

        $this->assertNull($resolved);
    }

    /**
     * Applies the current EnvResolutionStrategy to a single name/configValue
     * pair and returns the resolved final value — the same logic used in
     * Controller::updateEnv().
     */
    private function resolveValue(string $name, mixed $configValue): mixed {
        $systemValue = getenv($name);
        $hasSystemValue = $systemValue !== false && $systemValue !== '';

        return match (Controller::getEnvResolutionStrategy()) {
            EnvResolutionStrategy::SYSTEM_FIRST => $hasSystemValue ? $systemValue : $configValue,
            EnvResolutionStrategy::CONFIG_ONLY  => $configValue,
            EnvResolutionStrategy::SYSTEM_ONLY  => $hasSystemValue ? $systemValue : null,
        };
    }
}
