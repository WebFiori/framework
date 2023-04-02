<?php

use webfiori\framework\exceptions\MissingLangException;
use webfiori\framework\Language;
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2023 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */


/**
 * This file contains functions that can be used inside PHP templates. The
 * functions are used as shorthand instead of using OOP approach. 
 */

/**
 * Returns the value of a language variable.
 *
 * @param string $path A directory to the language variable (such as 'pages/login/login-label').
 * This also can be a string similar to 'pages.login.login-label'.
 *
 * @param string $langCode An optional language code. If provided, the
 * method will attempt to replace active language with the provided
 * one. If not provided, the method
 * will attempt to load a translation based on the session or default
 * web application language.
 *
 * @return string|array If the given directory represents a label, the
 * method will return its value. If it represents an array, the array will
 * be returned. If nothing was found, the returned value will be the passed
 * value to the function.
 *
 * @throws MissingLangException
 */
function label(string $path, string $langCode = null) {
    return Language::getLabel($path, $langCode);
}

function canonical() {
    return call('getCanonical');
}
function baseURL() {
    return call('getBase');
}
function title() {
    return call('getTitle');
}
function call($methodName) {
    if (isset($page)) {
        return $page->$methodName();
    }
}