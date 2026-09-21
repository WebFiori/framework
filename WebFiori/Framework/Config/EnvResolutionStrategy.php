<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Config;

/**
 * Defines how the framework resolves the value of an application environment
 * variable when both a config-driver value (from app-config.json or a class
 * driver) and a system environment variable with the same name exist.
 *
 * Set the strategy before calling `App::init()`:
 *
 * ```php
 * use WebFiori\Framework\App;
 * use WebFiori\Framework\Config\EnvResolutionStrategy;
 *
 * // Preserve pre-3.1 behavior (config file is authoritative):
 * App::setEnvResolutionStrategy(EnvResolutionStrategy::CONFIG_ONLY);
 * App::init();
 * ```
 *
 * The default strategy is `SYSTEM_FIRST`, which aligns with the 12-factor app
 * model and is the recommended setting for CI/CD and container deployments.
 *
 * @since 3.1.0
 * @see App::setEnvResolutionStrategy()
 * @see ADR-0051
 */
enum EnvResolutionStrategy {
    /**
     * Config driver value always wins; the system environment is not consulted.
     *
     * This preserves the behavior of versions prior to 3.1. Use this when the
     * config file is the explicit single source of truth and you want to ensure
     * system env vars cannot override it.
     */
    case CONFIG_ONLY;
    /**
     * System environment variable wins; config value is the fallback.
     *
     * If `getenv($name)` returns a non-empty value, that value is used.
     * Otherwise the config driver's value is used.
     *
     * This is the **default** and the recommended setting for CI/CD pipelines,
     * containers, and any environment where runtime env vars are authoritative.
     */
    case SYSTEM_FIRST;

    /**
     * Only the system environment is consulted; the config value is ignored.
     *
     * If `getenv($name)` returns an empty or false value, the constant will be
     * defined as null/empty. Use in locked-down server environments where all
     * configuration must come from the OS or container environment.
     */
    case SYSTEM_ONLY;
}
