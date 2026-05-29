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
namespace WebFiori\Framework\Health\Checks;

use WebFiori\Cache\CacheFacade;
use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

/**
 * Checks if the cache system is working (write/read/delete cycle).
 */
class CacheCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'cache';
    }

    public function check(): HealthCheckResult {
        try {
            $key = '__health_check_'.time();
            CacheFacade::set($key, 'ok', 5);
            $val = CacheFacade::get($key);
            CacheFacade::delete($key);

            if ($val === 'ok') {
                return HealthCheckResult::ok();
            }

            return HealthCheckResult::fail('Cache read/write mismatch');
        } catch (\Throwable $e) {
            return HealthCheckResult::fail($e->getMessage());
        }
    }
}
