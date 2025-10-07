<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Exceptions;

use Exception;
/**
 * An exception which is thrown in case of invalid CRON expression was provided
 * when initializing tasks.
 *
 * @author Ibrahim
 */
class InvalidCRONExprException extends Exception {
}
