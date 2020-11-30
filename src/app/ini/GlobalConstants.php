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
 * @since 1.1.0
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
        if (!defined('SCRIPT_MEMORY_LIMIT')) {
            /**
             * Memory limit per script.
             * This constant represents the maximum amount of memory each script will 
             * consume before showing a fatal error. Default value is 2GB. The 
             * developer can change this value as needed.
             * @since 1.0
             */
            define('SCRIPT_MEMORY_LIMIT', '2048M');
        }
        if (!defined('DATE_TIMEZONE')) {
            /**
             * Define the timezone at which the system will operate in.
             * The value of this constant is passed to the function 'date_default_timezone_set()'. 
             * This one is used to fix some date and time related issues when the 
             * application is deployed in multiple servers.
             * See http://php.net/manual/en/timezones.php for supported time zones.
             * Change this as needed.
             */
            define('DATE_TIMEZONE', 'Asia/Riyadh');
        }
        if (!defined('PHP_INT_MIN')) {
            /**
             * Fallback for older php versions that does not support the constant 
             * PHP_INT_MIN.
             * @since 1.0
             */
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
            define('CRON_THROUGH_HTTP', true);
        }

        if (!defined('WF_VERBOSE')) {
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
            define('WF_VERBOSE', true);
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
        if (!defined('CLI_HTTP_HOST')) {
            /**
             * Host name to use in case the system is executed through CLI.
             * When the application is running throgh CLI, there is no actual 
             * host name. For this reason, the host is set to 127.0.0.1 by default. 
             * If this constant is defined, the host will be changed to the value of 
             * the constant. Default value of the constant is 'example.com'.
             * @since 1.0
             */
            define('CLI_HTTP_HOST', 'example.com');
        }
        if (!defined('DS')) {
            /**
             * Directory separator.
             * This one is is used as a shorthand instead of using PHP 
             * constant 'DIRECTORY_SEPARATOR'. The two will have the same value.
             * @since 1.0
             */
            define('DS', DIRECTORY_SEPARATOR);
        }
        if (!defined('USE_HTTP')) {
            /**
             * Sets the framework to use 'http://' or 'https://' for base URIs.
             * The default behaviour of the framework is to use 'https://'. But 
             * in some cases, there is a need for using 'http://'.
             * If this constant is set to true, the framework will use 'http://' for 
             * base URI of the system. Default value is false.
             */
            define('USE_HTTP', false);
        }
    }
}
