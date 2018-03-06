<?php
/**
 * Error page labels.
 */
const ERR_PAGE_LANG = array(
    'error'=>'Error',
    'go-home'=>'Go to home page',
    'req-url'=>'Requested URL:'
);
/**
 * A constant that represents the error 403.
 */
const ERR_403 = array(
    'code'=>403,
    'type'=>'Forbidden',
    'message'=>'You are not allowed to view the content of the requested URL, Sorry about that.'
);
/**
 * A constant that represents the error 404.
 */
const ERR_404 = array(
    'code'=>404,
    'type'=>'Not Found',
    'message'=>'The requested resource cannot be found. Sorry about that.'
);
/**
 * A constant that represents the error 405.
 */
const ERR_405 = array(
    'code'=>405,
    'type'=>'Method Not Allowed',
    'message'=>'The method that is used to get the resource is not allowed.'
);
/**
 * A constant that represents the error 408.
 */
const ERR_408 = array(
    'code'=>408,
    'type'=>'Timeout',
    'message'=>'The requested took longer than expected and the connection was reset.'
);
/**
 * A constant that represents the error 415.
 */
const ERR_415 = array(
    'code'=>415,
    'type'=>'Unsupported Media Type',
    'message'=>'The payload format is not supported by the server.'
);
/**
 * A constant that represents the error 500.
 */
const ERR_500 = array(
    'code'=>500,
    'type'=>'Server Error',
    'message'=>'Unknown Server Error, Sorry about that.'
);
/**
 * A constant that represents the error 501.
 */
const ERR_501 = array(
    'code'=>501,
    'type'=>'Not Implemented',
    'message'=>'The request method is not supported.'
);
/**
 * A constant that represents the error 505.
 */
const ERR_505 = array(
    'code'=>505,
    'type'=>'HTTP Version Not Supported',
    'message'=>'The HTTP version used in the request is not supported by the server.'
);