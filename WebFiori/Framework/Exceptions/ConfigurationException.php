<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Exceptions;

use Exception;

/**
 * An exception thrown by the configuration system to indicate a problem
 * with the application configuration, such as a circular inheritance chain
 * in a JSON config file or a missing extended file.
 *
 * @author Ibrahim
 * @since 3.1.0
 */
class ConfigurationException extends Exception {
}
