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
namespace webfiori\framework\i18n;

/**
 * A class that contain some of the common language labels in Arabic.
 * So far, the class has the following variables:
 * <ul>
 * <li>general/week-day: The names of week days. 'd1' for Monday to 'd7' for Sunday.</li>
 * <li>general/g-month: Names of months in Gregorian calendar. 'm1' for January 
 * up to 'm12' for December.</li>
 * <li>general/action: A set of common actions that are usually performed 
 * by users. The actions are:
 * <ul>
 * <li>cancel</li>
 * <li>back</li>
 * <li>save</li>
 * <li>remove</li>
 * <li>delete</li>
 * <li>print</li>
 * <li>next</li>
 * <li>previous</li>
 * <li>skip</li>
 * <li>connect</li>
 * <li>finish</li>
 * </ul>
 * </li>
 * <li>general/status: A set of common statuses for application elements 
 * after performing specific action. The actions are:
 * <ul>
 * <li>wait</li>
 * <li>loading</li>
 * <li>checking</li>
 * <li>validating</li>
 * <li>loaded</li>
 * <li>saving</li>
 * <li>saved</li>
 * <li>removing</li>
 * <li>removed</li>
 * <li>deleting</li>
 * <li>deleted</li>
 * <li>printing</li>
 * <li>printed</li>
 * <li>connecting</li>
 * <li>connected</li>
 * <li>disconnected</li>
 * </ul>
 * </li>
 * <li>general/error: A set of common error messages The errors are:
 * <ul>
 * <li>dbError</li>
 * <li>dbConnectErr</li>
 * <li>save</li>
 * <li>remove</li>
 * <li>delete</li>
 * <li>print</li>
 * <li>connect</li>
 * </ul>
 * </li>
 * <li>general/http-codes: A set that contains most common HTTP codes. 
 * inside each code, there are 3 items:
 * <ul>
 * <li>code: The actual code such as 200 or 404 as an integer.</li>
 * <li>type: The type of the code such as 'Ok' or 'Not Authorized'.</li>
 * <li>message: The meaning of the code in more details.</li>
 * </ul>
 * So far, the available codes are:
 * <ul>
 * <li>200</li>
 * <li>201</li>
 * <li>400</li>
 * <li>401</li>
 * <li>403</li>
 * <li>404</li>
 * <li>405</li>
 * <li>408</li>
 * <li>415</li>
 * <li>500</li>
 * <li>501</li>
 * <li>505</li>
 * </ul>
 * </li>
 * <ul>
 * @version 1.0
 * @author Ibrahim
 */
class LanguageEN extends Language {
    public function __construct() {
        parent::__construct('ltr', 'EN', true);
        $this->createAndSet('general', [
            'framework-name' => 'WebFiori Framework',
        ]);
        $this->createAndSet('general/social-media-names', [
            'linkedin' => 'LinkedIn',
            'github' => 'GitHub',
            'twitter' => 'Twitter',
            'telegram' => 'Telegram',
            'reddit' => 'Reddit',
            'facebook' => 'Facebook',
            'whatsapp' => 'WhatsApp'
        ]);
        $this->createAndSet('general/http-codes/200', [
            'code' => 200,
            'type' => 'OK',
            'message' => ''
        ]);
        $this->createAndSet('general/http-codes/201', [
            'code' => 201,
            'type' => 'Created',
            'message' => ''
        ]);
        $this->createAndSet('general/http-codes/400', [
            'code' => 400,
            'type' => 'Bad Request',
            'message' => 'Server could not understand the request due to invalid syntax.'
        ]);
        $this->createAndSet('general/http-codes/401', [
            'code' => 401,
            'type' => 'Not Authorized',
            'message' => 'You are not authorized to view the specified reasource.'
        ]);
        $this->createAndSet('general/http-codes/403', [
            'code' => 403,
            'type' => 'Forbidden',
            'message' => 'You are not allowed to view the content of the requested resource.'
        ]);
        $this->createAndSet('general/http-codes/404', [
            'code' => 404,
            'type' => 'Not Found',
            'message' => 'The requested resource cannot be found.'
        ]);
        $this->createAndSet('general/http-codes/405', [
            'code' => 405,
            'type' => 'Method Not Allowed',
            'message' => 'The method that is used to get the resource is not allowed.'
        ]);
        $this->createAndSet('general/http-codes/408', [
            'code' => 408,
            'type' => 'Request Timeout',
            'message' => ''
        ]);
        $this->createAndSet('general/http-codes/415', [
            'code' => 415,
            'type' => 'Unsupported Media Type',
            'message' => 'The payload format is not supported by the server.'
        ]);
        $this->createAndSet('general/http-codes/500', [
            'code' => 500,
            'type' => 'Server Error',
            'message' => 'Internal server error.'
        ]);
        $this->createAndSet('general/http-codes/501', [
            'code' => 501,
            'type' => 'Not Implemented',
            'message' => 'The request method is not supported.'
        ]);
        $this->createAndSet('general/http-codes/505', [
            'code' => 505,
            'type' => 'HTTP Version Not Supported',
            'message' => 'The HTTP version used in the request is not supported by the server.'
        ]);

        $this->createAndSet('general/action', [
            'cancel' => 'Cancel',
            'back' => 'Back',
            'save' => 'Save',
            'remove' => 'Remove',
            'delete' => 'Delete',
            'print' => 'Print',
            'connect' => 'Connect',
            'next' => 'Next',
            'previous' => 'Previous',
            'skip' => 'Skip',
            'finish' => 'Finish',
            'add' => 'Add'
        ]);

        $this->createAndSet('general/error', [
            'dbError' => 'Database Error.',
            'dbConnectErr' => 'Unable to connect to database.'
        ]);

        $this->createAndSet('general/status', [
            'wait' => 'Please wait a moment...',
            'loading' => 'Loading...',
            'checking' => 'Checking...',
            'validating' => 'Validating...',
            'loaded' => 'Loaded.',
            'saving' => 'Saving...',
            'saved' => 'Saved.',
            'removing' => 'Removing...',
            'removed' => 'Removed.',
            'deleting' => 'Deleting...',
            'deleted' => 'Deleted.',
            'printing' => 'Printing...',
            'printed' => 'Printed.',
            'connecting' => 'Connecting...',
            'connected' => 'Connected.',
            'disconnected' => 'Disconnected.',
            'adding' => 'Adding...',
            'added' => 'Added.'
        ]);

        $this->createAndSet('general/error', [
            'save' => 'Unable to save!',
            'remove' => 'Unable to remove!',
            'delete' => 'Unable to delete!',
            'print' => 'Unable to print!',
            'connect' => 'Unable to connect!',
            'add' => 'Unable to add!'
        ]);

        $this->createAndSet('general/week-day', [
            'd7' => 'Sunday',
            'd1' => 'Monday',
            'd2' => 'Tuesday',
            'd3' => 'Wednesday',
            'd4' => 'Thursday',
            'd5' => 'Friday',
            'd6' => 'Saturday',
        ]);

        $this->createAndSet('general/g-month', [
            'm1' => 'January',
            'm2' => 'February',
            'm3' => 'March',
            'm4' => 'April',
            'm5' => 'May',
            'm6' => 'June',
            'm7' => 'July',
            'm8' => 'August',
            'm9' => 'September',
            'm10' => 'October',
            'm11' => 'November',
            'm12' => 'December',
        ]);

        $this->createAndSet('general/i-month', [
            'm1' => 'Muḥarram',
            'm2' => 'Ṣafar',
            'm3' => 'Rabīʿ al-Awwal',
            'm4' => 'Rabīʿ ath-Thānī ',
            'm5' => 'Jumādá al-Ūlá',
            'm6' => 'Jumādá al-Ākhirah',
            'm7' => 'Rajab',
            'm8' => 'Sha‘bān',
            'm9' => 'Ramaḍān',
            'm10' => 'Shawwāl',
            'm11' => 'Dhū al-Qa‘dah',
            'm12' => 'Dhū al-Ḥijjah',
        ]);
        
        $this->createAndSet('pages/sample-page', [
            'title' => 'Sample Page',
            'description' => 'This is a sample page which is used to show basic usage of the framework.',
            'question' => 'What is WebFiori Framework?',
            'frameworkDescription' => 'WebFiori Framework is a mini web development framework which is written '
                .'in PHP language. The framework is fully object oriented '
                .'(OOP). It uses a semi-model-view-controller '
                .'(MVC) model but it does not force it. The framework comes '
                .'with many features which can help in making your website '
                .'or web application up and running in no time.',
            'features' => 'Main Features:',
            'featuresList' => [
                'Theming and the ability to create multiple UIs for the same web page using any CSS or JavaScript framework.',
                'Support for routing that makes the ability of creating search-engine-friendly links an easy task.',
                'Creation of web APIs that supports JSON, data filtering and validation.',
                'Basic support for MySQL schema and query building.',
                'Lightweight. The total size of framework core files is less than 3 megabytes.',
                'Access management by assigning system user a set of privileges.',
                'The ability to create and manage multiple sessions at once.',
                'Support for creating and sending nice-looking emails in a simple way by using SMTP protocol.',
                'Autoloading of user defined classes.',
                'The ability to schedule background tasks and let them run in specific time using CRON.',
                'Well-defined file upload and file handling sub-system.',
                'Building and manipulating the DOM of a web page using PHP language.',
                'Basic support for running the framework throgh CLI.'
            ]
        ]);
        $this->createAndSet('main-menu/lang-switch', [
            'AR' => 'العربية',
            'EN' => 'English'
        ]);
    }
}
