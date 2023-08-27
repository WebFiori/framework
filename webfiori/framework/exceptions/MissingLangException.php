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
namespace webfiori\framework\exceptions;

use Exception;
/**
 * An exception which is thrown when a translation was not found or no object
 * of type 'Lang' was found for a language.
 *
 * @author Ibrahim
 */
class MissingLangException extends Exception {
}
