<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../root.php';
Util::displayErrors();
$r = Router::get();
Util::print_r('Router Test');
$uri = new RouterUri('https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz', '');
//$uri->setUriVar('some-var', 'en');
$uri->setUriVar('other-var', 'niv');
printUriDetails($uri);
$uri2 = new RouterUri('http://www3.programmingacademia.com:80/{sme-var}/work/{other-var}/?do=dont&y=#xyz', '');
printUriDetails($uri2);
isURIEqualAnother($uri, $uri2);
echo $uri->isAllVarsSet() === TRUE ? 'ALL Set' : 'Not ALL set';
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
            . '<br/>'
            . '<br/>'
            . '<br/>');
    Util::print_r('Query String Params:');
    Util::print_r($uri->getQueryStringVars());
    Util::print_r('URI Params:');
    Util::print_r($uri->getUriVars());
}

function isURIEqualAnother($uri1,$uri2){
    $areEqual = $uri1->equals($uri2) ? 'Equal' : 'NOT Equal';
    Util::print_r($areEqual);
}
