<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Health;

/**
 * Registry and runner for health checks.
 *
 * Supports both class-based checks (HealthCheckInterface) and callable checks.
 */
class HealthCheck {
    /**
     * @var array Registered checks indexed by name.
     */
    private static array $checks = [];
    /**
     * @var array Callbacks to execute after runAll() completes.
     */
    private static array $afterAllCallbacks = [];
    /**
     * Register a health check.
     *
     * Accepts either a HealthCheckInterface instance or a name + callable pair.
     *
     * @param HealthCheckInterface|string $check An instance or a check name.
     * @param callable|null $callable Required if $check is a string.
     */
    public static function register($check, ?callable $callable = null): void {
        if ($check instanceof HealthCheckInterface) {
            self::$checks[$check->getName()] = $check;
        } else if (is_string($check) && $callable !== null) {
            self::$checks[$check] = $callable;
        }
    }
    /**
     * Run all registered health checks.
     *
     * @return array Aggregate result with 'status', 'timestamp', and 'checks'.
     */
    public static function runAll(): array {
        $results = [];
        $allOk = true;

        foreach (self::$checks as $name => $check) {
            if ($check instanceof HealthCheckInterface) {
                $result = $check->check();
            } else {
                $raw = $check();

                if ($raw instanceof HealthCheckResult) {
                    $result = $raw;
                } else {
                    $status = $raw['status'] ?? 'fail';
                    $result = $status === 'ok'
                        ? HealthCheckResult::ok($raw)
                        : HealthCheckResult::fail($raw['reason'] ?? 'Unknown', $raw);
                }
            }

            $results[$name] = $result->toArray();

            if ($result->getStatus() !== 'ok') {
                $allOk = false;
            }
        }

        $aggregate = [
            'status' => $allOk ? 'ok' : 'fail',
            'timestamp' => date('c'),
            'checks' => $results,
        ];

        foreach (self::$afterAllCallbacks as $cb) {
            $cb($aggregate);
        }

        return $aggregate;
    }
    /**
     * Remove all registered checks.
     */
    public static function reset(): void {
        self::$checks = [];
        self::$afterAllCallbacks = [];
    }
    /**
     * Returns the number of registered checks.
     *
     * @return int
     */
    public static function getCheckCount(): int {
        return count(self::$checks);
    }
    /**
     * Returns all registered checks.
     *
     * @return array Associative array keyed by check name. Values are
     * HealthCheckInterface instances or callables.
     */
    public static function getChecks(): array {
        return self::$checks;
    }
    /**
     * Register a callback to execute after all checks complete.
     *
     * The callback receives the aggregate result array with 'status',
     * 'timestamp', and 'checks' keys.
     *
     * @param callable $callback A function that accepts the aggregate results array.
     */
    public static function afterAll(callable $callback): void {
        self::$afterAllCallbacks[] = $callback;
    }
}
