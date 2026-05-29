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
 * Interface for health check implementations.
 */
interface HealthCheckInterface {
    /**
     * Returns the name of this health check.
     *
     * @return string
     */
    public function getName(): string;
    /**
     * Perform the health check.
     *
     * @return HealthCheckResult
     */
    public function check(): HealthCheckResult;
}
