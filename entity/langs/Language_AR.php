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
$Language = new Language('rtl', 'ar', array(
    'general/week-day',
    'general/month',
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

$Language->set('general/status', 'wait', 'الرجاء الإنتظار للحظة...');
$Language->set('general/status', 'loading', 'جاري التحميل...');
$Language->set('general/status', 'checking', 'جاري التحقق...');
$Language->set('general/status', 'validating', 'جاري التحقق من الصحة...');
$Language->set('general/status', 'loaded', 'تم التحميل.');

$Language->set('general/action', 'save', 'حفظ');
$Language->set('general/status', 'saving', 'جاري الحفظ...');
$Language->set('general/status', 'saved', 'تم الحفظ.');
$Language->set('general/error', 'save', 'غير قادر على الحفظ!');

$Language->set('general/action', 'remove', 'إزالة');
$Language->set('general/status', 'removing', 'جاري الإزالة...');
$Language->set('general/status', 'removed', 'تمت الإزالة.');
$Language->set('general/error', 'remove', 'غير قادر على الإزالة!');

$Language->set('general/action', 'delete', 'حذف');
$Language->set('general/status', 'deleting', 'جاري الحذف...');
$Language->set('general/status', 'deleted', 'تم الحذف.');
$Language->set('general/error', 'delete', 'غير قادر على الحذف!');

$Language->set('general/action', 'print', 'طباعة');
$Language->set('general/status', 'printing', 'جاري الطباعة...');
$Language->set('general/status', 'printed', 'تمت الطباعة.');
$Language->set('general/error', 'print', 'غير قادر على !');

$Language->set('general/status', 'connect', 'إتصال');
$Language->set('general/status', 'connecting', 'جاري الإتصال...');
$Language->set('general/status', 'connected', 'تم الإتصال.');
$Language->set('general/error', 'connect', 'غير قادر على الإتصال!');

$Language->set('general/status', 'disconnected', 'غير متصل.');

$Language->set('general/action', 'next', 'التالي');
$Language->set('general/action', 'previous', 'السابق');
$Language->set('general/action', 'skip', 'تخطي');
$Language->set('general/action', 'finish', 'إنهاء');

$Language->setMultiple('general/week-day', array(
    '0'=>'الأحد',
    '1'=>'الأثنين',
    '2'=>'الثلاثاء',
    '3'=>'الأربعاء',
    '4'=>'الخميس',
    '5'=>'الجمعة',
    '6'=>'السبت',
));

$Language->setMultiple('general/month', array(
    '0'=>'يناير',
    '1'=>'فبراير',
    '2'=>'مارس',
    '3'=>'أبريل',
    '4'=>'مايو',
    '5'=>'يونيو',
    '6'=>'يوليو',
    '7'=>'اغسطس',
    '8'=>'سبتمبر',
    '9'=>'أكتوبر',
    '10'=>'نوفمبر',
    '11'=>'ديسمبر',
));
