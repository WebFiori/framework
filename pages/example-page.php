<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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

/*
 * The next block of code can be added to every view or .php 
 * file to prevent direct access.
 */
Logger::logName('views-log');
Logger::log('Checking if scripit is accessed directly.');
if(!defined('ROOT_DIR')){
    Logger::log('Direct access. Forbidden',NULL,TRUE);
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
//Logger::log('No Direct access.');
////load UI template components (JS, CSS and others)
////it is optional. to use a theme but recomended
//Logger::log('Loading theme \'Greeny By Ibrahim Ali\'');
//Page::theme($themeName='Greeny By Ibrahim Ali');
//
////sets the title of the page
//Logger::log('Setting view title to \'Example Page\'');
//Page::title('Example Page');
//
////adds a paragraph to the body of the page.
//$p = new PNode();
//$p->addText('Hello from LisksCode Framework!');
//Page::insert($p);
//
////display the view
////Page::render();
//Logger::log('Example view loaded.',NULL,TRUE);
if(!defined('DEBUG')){
    define('debug', '');
}
$s1Name = 'session-1';
$s2Name = 'session-2';
$s3Name = 'session-3';

$m1 = newSession($s1Name);
$m2 = newSession($s2Name);
$m3 = newSession($s3Name);

startSession($m2);
startSession($m3);
startSession($m1);

setLifetime(5, $m1);
setLifetime(10, $m2);
setLifetime(15, $m3);

$j = new JsonX();
$j->add($s1Name, $m1);
$j->add($s2Name, $m2);
$j->add($s3Name, $m3);
echo $j;

function newSession($name){
    Logger::logName($name);
    Logger::clear();
    $m1 = new SessionManager($name);
    Logger::section();
    return $m1;
}

function setLifetime($time,$session){
    Logger::logName($session->getName());
    $session->setLifetime($time);
}

function startSession($session){
    Logger::logName($session->getName());
    $session->initSession();
}



