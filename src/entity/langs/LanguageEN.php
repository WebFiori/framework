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
namespace webfiori\entity\langs;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
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
 * <li>db-error</li>
 * <li>db-connect-err</li>
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
class LanguageEN extends Language{
    public function __construct() {
        parent::__construct('ltr', 'EN', array(
            'general/week-day',
            'general/g-month',
            'general/i-month',
            'general/action',
            'general/status',
            'general/error',
            'general/http-codes/200',
            'general/http-codes/201',
            'general/http-codes/400',
            'general/http-codes/401',
            'general/http-codes/403',
            'general/http-codes/404',
            'general/http-codes/405',
            'general/http-codes/408',
            'general/http-codes/415',
            'general/http-codes/500',
            'general/http-codes/501',
            'general/http-codes/505',
        ), TRUE);
        $this->setMultiple('general/http-codes/200', array(
            'code'=>200,
            'type'=>'OK',
            'message'=>''
        ));
        $this->setMultiple('general/http-codes/201', array(
            'code'=>201,
            'type'=>'Created',
            'message'=>''
        ));
        $this->setMultiple('general/http-codes/400', array(
            'code'=>400,
            'type'=>'Bad Request',
            'message'=>'Server could not understand the request due to invalid syntax.'
        ));
        $this->setMultiple('general/http-codes/401', array(
            'code'=>401,
            'type'=>'Not Authorized',
            'message'=>'You are not authorized to view the specified reasource.'
        ));
        $this->setMultiple('general/http-codes/403', array(
            'code'=>403,
            'type'=>'Forbidden',
            'message'=>'You are not allowed to view the content of the requested resource.'
        ));
        $this->setMultiple('general/http-codes/404', array(
            'code'=>404,
            'type'=>'Not Found',
            'message'=>'The requested resource cannot be found.'
        ));
        $this->setMultiple('general/http-codes/405', array(
            'code'=>405,
            'type'=>'Method Not Allowed',
            'message'=>'The method that is used to get the resource is not allowed.'
        ));
        $this->setMultiple('general/http-codes/408', array(
            'code'=>408,
            'type'=>'Request Timeout',
            'message'=>''
        ));
        $this->setMultiple('general/http-codes/415', array(
            'code'=>415,
            'type'=>'Unsupported Media Type',
            'message'=>'The payload format is not supported by the server.'
        ));
        $this->setMultiple('general/http-codes/500', array(
            'code'=>500,
            'type'=>'Server Error',
            'message'=>'Internal server error.'
        ));
        $this->setMultiple('general/http-codes/501', array(
            'code'=>501,
            'type'=>'Not Implemented',
            'message'=>'The request method is not supported.'
        ));
        $this->setMultiple('general/http-codes/505', array(
            'code'=>505,
            'type'=>'HTTP Version Not Supported',
            'message'=>'The HTTP version used in the request is not supported by the server.'
        ));
        
        $this->set('general/action', 'cancel', 'Cancel');
        $this->set('general/action', 'back', 'Back');
        
        $this->set('general/error', 'db-error', 'Database Error.');
        $this->set('general/error', 'db-connect-err', 'Unable to connect to database.');
        
        $this->set('general/status', 'wait', 'Please wait a moment...');
        $this->set('general/status', 'loading', 'Loading...');
        $this->set('general/status', 'checking', 'Checking...');
        $this->set('general/status', 'validating', 'Validating...');
        $this->set('general/status', 'loaded', 'Loaded.');

        $this->set('general/action', 'save', 'Save');
        $this->set('general/status', 'saving', 'Saving...');
        $this->set('general/status', 'saved', 'Saved');
        $this->set('general/error', 'save', 'Unable to save.');

        $this->set('general/action', 'remove', 'Remove');
        $this->set('general/status', 'removing', 'Removing...');
        $this->set('general/status', 'removed', 'Removed');
        $this->set('general/error', 'remove', 'Unable to remove.');

        $this->set('general/action', 'delete', 'Delete');
        $this->set('general/status', 'deleting', 'Deleting...');
        $this->set('general/status', 'deleted', 'Deleted');
        $this->set('general/error', 'delete', 'Unabel to delete.');

        $this->set('general/action', 'print', 'Print');
        $this->set('general/status', 'printing', 'Printing...');
        $this->set('general/status', 'printed', 'Printed.');
        $this->set('general/error', 'print', 'Unable to print.');

        $this->set('general/action', 'connect', 'Connect');
        $this->set('general/status', 'connecting', 'Connecting...');
        $this->set('general/status', 'connected', 'Connected');
        $this->set('general/error', 'connect', 'Unable to connect.');

        $this->set('general/status', 'disconnected', 'Disconnected');

        $this->set('general/action', 'next', 'Next');
        $this->set('general/action', 'previous', 'Previous');
        $this->set('general/action', 'skip', 'Skip');
        $this->set('general/action', 'finish', 'Finish');

        $this->setMultiple('general/week-day', array(
            'd7'=>'Sunday',
            'd1'=>'Monday',
            'd2'=>'Tuesday',
            'd3'=>'Wednesday',
            'd4'=>'Thursday',
            'd5'=>'Friday',
            'd6'=>'Saturday',
        ));

        $this->setMultiple('general/g-month', array(
            'm1'=>'January',
            'm2'=>'February',
            'm3'=>'March',
            'm4'=>'April',
            'm5'=>'May',
            'm6'=>'June',
            'm7'=>'July',
            'm8'=>'August',
            'm9'=>'September',
            'm10'=>'October',
            'm11'=>'November',
            'm12'=>'December',
        ));
        
        $this->setMultiple('general/i-month', array(
            'm1'=>'Muḥarram',
            'm2'=>'Ṣafar',
            'm3'=>'Rabīʿ al-Awwal',
            'm4'=>'Rabīʿ ath-Thānī ',
            'm5'=>'Jumādá al-Ūlá',
            'm6'=>'Jumādá al-Ākhirah',
            'm7'=>'Rajab',
            'm8'=>'Sha‘bān',
            'm9'=>'Ramaḍān',
            'm10'=>'Shawwāl',
            'm11'=>'Dhū al-Qa‘dah',
            'm12'=>'Dhū al-Ḥijjah',
        ));
    }
}
