<?php
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
 * English language definition.
 */
$Language = new Language('ltr', 'en', array(
    'general/week-days',
    'general/months',
    'general/action',
    'general/status',
    'general/error',
    'general/http-codes/200',
    'general/http-codes/201',
    'general/http-codes/400',
    'general/http-codes/403',
    'general/http-codes/404',
    'general/http-codes/405',
    'general/http-codes/408',
    'general/http-codes/415',
    'general/http-codes/500',
    'general/http-codes/501',
    'general/http-codes/505',
));
$Language->setMultiple('general/http-codes/200', array(
    'code'=>200,
    'type'=>'OK',
    'message'=>''
));
$Language->setMultiple('general/http-codes/201', array(
    'code'=>201,
    'type'=>'Created',
    'message'=>''
));
$Language->setMultiple('general/http-codes/400', array(
    'code'=>400,
    'type'=>'Bad Request',
    'message'=>'Server could not understand the request due to invalid syntax.'
));
$Language->setMultiple('general/http-codes/403', array(
    'code'=>403,
    'type'=>'Forbidden',
    'message'=>'You are not allowed to view the content of the requested resource.'
));
$Language->setMultiple('general/http-codes/404', array(
    'code'=>404,
    'type'=>'Not Found',
    'message'=>'The requested resource cannot be found.'
));
$Language->setMultiple('general/http-codes/405', array(
    'code'=>405,
    'type'=>'Method Not Allowed',
    'message'=>'The method that is used to get the resource is not allowed.'
));
$Language->setMultiple('general/http-codes/408', array(
    'code'=>408,
    'type'=>'Request Timeout',
    'message'=>''
));
$Language->setMultiple('general/http-codes/415', array(
    'code'=>415,
    'type'=>'Unsupported Media Type',
    'message'=>'The payload format is not supported by the server.'
));
$Language->setMultiple('general/http-codes/500', array(
    'code'=>500,
    'type'=>'Server Error',
    'message'=>'Internal server error.'
));
$Language->setMultiple('general/http-codes/501', array(
    'code'=>501,
    'type'=>'Not Implemented',
    'message'=>'The request method is not supported.'
));
$Language->setMultiple('general/http-codes/505', array(
    'code'=>505,
    'type'=>'HTTP Version Not Supported',
    'message'=>'The HTTP version used in the request is not supported by the server.'
));

$Language->set('general/status', 'wait', 'Please wait a moment...');
$Language->set('general/status', 'loading', 'Loading...');
$Language->set('general/status', 'checking', 'Checking...');
$Language->set('general/status', 'validating', 'Validating...');
$Language->set('general/status', 'loaded', 'Loaded.');

$Language->set('general/action', 'save', 'Save');
$Language->set('general/status', 'saving', 'Saving...');
$Language->set('general/status', 'saved', 'Saved');
$Language->set('general/error', 'save', 'Unable to save!');

$Language->set('general/action', 'remove', 'Remove');
$Language->set('general/status', 'removing', 'Removing...');
$Language->set('general/status', 'removed', 'Removed.');
$Language->set('general/error', 'remove', 'Unable to remove!');

$Language->set('general/action', 'delete', 'Delete');
$Language->set('general/status', 'deleting', 'Deleting...');
$Language->set('general/status', 'deleted', 'Deleted.');
$Language->set('general/error', 'delete', 'Unable to delete!');

$Language->set('general/action', 'print', 'Print');
$Language->set('general/status', 'printing', 'Printing...');
$Language->set('general/status', 'printed', 'Printed.');
$Language->set('general/error', 'print', 'Unable to print!');

$Language->set('general/status', 'connect', 'Connect');
$Language->set('general/status', 'connecting', 'Connecting...');
$Language->set('general/status', 'connected', 'Connected.');
$Language->set('general/error', 'connect', 'Unable to connect!');

$Language->set('general/status', 'disconnected', 'Disconnected.');

$Language->set('general/action', 'next', 'Next');
$Language->set('general/action', 'previous', 'Previous');
$Language->set('general/action', 'skip', 'Skip');
$Language->set('general/action', 'finish', 'Finish');

$Language->setMultiple('general/week-day', array(
    'd0'=>'Sunday',
    'd1'=>'Monday',
    'd2'=>'Tuesday',
    'd3'=>'Wednesday',
    'd4'=>'Thursday',
    'd5'=>'Friday',
    'd6'=>'Saturday',
));

$Language->setMultiple('general/month', array(
    '0'=>'January',
    '1'=>'February',
    '2'=>'March',
    '3'=>'April',
    '4'=>'May',
    '5'=>'June',
    '6'=>'July',
    '7'=>'August',
    '8'=>'September',
    '9'=>'October',
    '10'=>'November',
    '11'=>'December',
));