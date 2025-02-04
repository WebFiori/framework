<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2023 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */

use webfiori\framework\exceptions\MissingLangException;
use webfiori\framework\Lang;
use webfiori\framework\ui\WebPage;
use webfiori\http\Response;
use webfiori\json\JsonI;
use webfiori\ui\HTMLNode;

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
function label(string $path, ?string $langCode = null) {
    return Lang::getLabel($path, $langCode);
}
/**
 * Returns the canonical URL of the page.
 *
 * @return null|string The method will return the  canonical URL of the page
 * if set. If not, the method will return null.
 *
 */
function canonical() {
    return call('getCanonical');
}
/**
 * Returns the value of the attribute 'href' of the node 'base' of page document.
 *
 * @return string|null If the base URL is set, the method will return its value.
 * If the value of the base URL is not set, the method will return null.
 *
 */
function baseURL() {
    return call('getBase');
}
/**
 * Returns the description of the page.
 *
 * @return string|null The description of the page. If the description is not set,
 * the method will return null.
 *
 */
function description() {
    return call('getDescription');
}
/**
 * Returns the title of the page.
 *
 * @return string The title of the page.
 *
 */
function title() : string {
    return call('getTitle');
}
/**
 * Display a message in web browser's console.
 *
 * @param mixed $message Any variable.
 */
function logVar($message) {
    $js = new HTMLNode('script');
    $type = gettype($message);

    if ($type == 'object' || $type == 'resource') {
        if (is_subclass_of($message, JsonI::class)) {
            $js->text("console.log(".$message->toJSON().")", false);
        } else {
            ob_start();
            var_dump($message);
            $js->text("console.log(`".trim(str_replace('\\', '\\\\', ob_get_clean()))."`)", false);
        }
    } else if ($type == 'string') {
        $js->text("console.log(`".$message."`)", false);
    } else {
        $js->text("console.log(".$message.")", false);
    }
    Response::write($js);
}
function call($methodName) {
    global $page;

    if (isset($page) && $page instanceof WebPage) {
        return $page->$methodName();
    }
}
