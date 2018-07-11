<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../root.php';
$r = Router::get();
Util::print_r('Router Test');
$uri = new RouterUri('en/a/{nice}/{word}/do-it?a=b&c=d', '');
printUriDetails($uri);
$uri = new RouterUri('http://www.pa.com/en/a/{nice}/{word}/do-it', '');
printUriDetails($uri);
$uri = new RouterUri('http://pa.com/en/a/{nice}/{word}/do-it/?x=y&z=p', '');
printUriDetails($uri);
$uri = new RouterUri('//www.pa.com/en/a/do-it', '');
printUriDetails($uri);
$uri = new RouterUri('www.pa.com/en/a/do-it?', '');
printUriDetails($uri);
/**
 * 
 * @param RouterUri $uri
 */
function printUriDetails($uri){
    Util::print_r(''
            . 'Requested URI: '.$uri->getRequestedUri()
            . 'URI Without Query String: '.$uri->getRequestedUri()
            . ''
            . '');
}