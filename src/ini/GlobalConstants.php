<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\ini;

/**
 * A class which is used to initialize global constants.
 * This class has one static method which is used to define the constants.
 * The class can be used to initialize any constant that the application depends 
 * on. The constants that this class will initialize are the constants which 
 * uses the function <code>define()</code>.
 * Also, the developer can modify existing ones as needed to change some of the 
 * default settings of the framework.
 * @author Ibrahim
 * @version 1.0
 */
class GlobalConstants {
    /**
     * Initialize the constants.
     * Include your own in the body of this method or modify existing ones 
     * to suite your configuration. It is recommended to check if the global 
     * constant is defined or not before defining it using the function 
     * <code>defined</code>.
     * @since 1.0
     */
    public static function defineConstants() {
        /**
         * Fallback for older php versions that does not support the constant 
         * PHP_INT_MIN.
         * @since 1.0
         */
        if (!defined('PHP_INT_MIN')) {
            define('PHP_INT_MIN', ~PHP_INT_MAX);
        }

        if (!defined('LOAD_COMPOSER_PACKAGES')) {
            /**
             * This constant is used to tell the core if the application uses composer 
             * packages or not. If set to true, then composer packages will be loaded.
             * @since 1.0
             */
            define('LOAD_COMPOSER_PACKAGES', true);
        }

        if (!defined('CRON_THROUGH_HTTP')) {
            /**
             * A constant which is used to enable or disable HTTP access to cron.
             * If the constant value is set to true, the framework will add routes to the 
             * components which is used to allow access to cron control panel. The control 
             * panel is used to execute jobs and check execution status. Default value is false.
             * @since 1.0
             */
            define('CRON_THROUGH_HTTP', false);
        }

        if (!defined('VERBOSE')) {
            /**
             * This constant is used to tell the framework if more information should 
             * be displayed if an exception is thrown or an error happens. The main aim 
             * of this constant is to hide some sensitive information from users if the 
             * system is in production environment. Note that the constant will have effect 
             * only if the framework is accessed through HTTP protocol. If used in CLI 
             * environment, everything will appear. Default value of the constant is 
             * false.
             * @since 1.0
             */
            define('VERBOSE', false);
        }

        if (!defined('NO_WWW')) {
            /**
             * This constant is used to redirect a URI with www to non-www.
             * If this constant is defined and is set to true and a user tried to 
             * access a resource using a URI that contains www in the host part,
             * the router will send a 301 - permanent redirect HTTP response code and 
             * send the user to non-www host. For example, if a request is sent to 
             * 'https://www.example.com/my-page', it will be redirected to 
             * 'https://example.com/my-page'. Default value of the constant is false which 
             * means no redirection will be performed.
             * @since 1.0
             */
            define('NO_WWW', false);
        }

        if (!defined('THEMES_PATH')) {
            $themesDirName = 'themes';
            $themesPath = substr(__DIR__, 0, strlen(__DIR__) - strlen('/ini')).DIRECTORY_SEPARATOR.$themesDirName;
            /**
             * This constant represents the directory at which themes exist.
             * @since 1.0
             */
            define('THEMES_PATH', $themesPath);
        }

        if (!defined('MAX_BOX_MESSAGES')) {
            /**
             * The maximum number of message boxes to show in one page.
             * A message box is a box which will be shown in a web page that 
             * contains some information. The 
             * box can be created manually by using the method 'Util::print_r()' or 
             * it can be as a result of an error during execution.
             * Default value is 15. The developer can change the value as needed. Note 
             * that if the constant is not defined, the number of boxes will 
             * be almost unlimited.
             * @since 1.0
             */
            define('MAX_BOX_MESSAGES', 15);
        }
    }
}
