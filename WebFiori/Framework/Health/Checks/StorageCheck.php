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

use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

/**
 * Checks if the application storage directory is writable.
 */
class StorageCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'storage';
    }

    public function check(): HealthCheckResult {
        $path = APP_PATH.'Storage';

        if (is_writable($path)) {
            return HealthCheckResult::ok(['writable' => true]);
        }

        return HealthCheckResult::fail('Storage directory not writable');
    }
}
