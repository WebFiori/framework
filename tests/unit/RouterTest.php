<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../root.php';
Util::displayErrors();
$router = Router::get();
$router->addRoute('/hello/{someone}', function(){
    echo 'Hello Mr. '. filter_var($_GET['someone']);
}, Router::CLOSURE_ROUTE);
$router->addRoute('/add-numbers/{first}/{second}', function(){
    $fNum = filter_var($_GET['first']);
    $sNum = filter_var($_GET['second']);
    Util::print_r('First Number: '.$fNum);
    Util::print_r('Second Number: '.$sNum);
    $sum = $fNum + $sNum;
    Util::print_r('Sum: '.$sum);
}, Router::CLOSURE_ROUTE);
$router->addRoute('/pay/{someone}', function(){
    Util::print_r('Pay Mr. '.$_GET['someone'].' 100 SAR.');
}, Router::CLOSURE_ROUTE);
$router->sendToRoute('http://localhost/generic-php/add-numbers/5/5');
//$router->printRoutes();
/**
 * 
 * @param RouterUri $uri
 */
function printUriDetails($uri){
    echo 'Printing URi';
    Util::print_r(''
            . 'Requested URI: '.$uri->getRequestedUri(TRUE)
            . '<br/>Fragment: '.$uri->getFragment()
            . '<br/>Scheme: '.$uri->getScheme()
            . '<br/>Query String: '.$uri->getQueryString()
            . '<br/>Authority: '.$uri->getAuthority()
            . '<br/>Port: '.$uri->getPort()
            . '<br/>Path: '.$uri->getPath()
            );
    Util::print_r('Query String Params:');
    Util::print_r($uri->getQueryStringVars());
    Util::print_r('URI Params:');
    Util::print_r($uri->getUriVars());
}

function isURIEqualAnother($uri1,$uri2){
    $areEqual = $uri1->equals($uri2) ? 'Equal' : 'NOT Equal';
    Util::print_r($areEqual);
}
