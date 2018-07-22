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
if(UserFunctions::get()->getUserID() == -1){
    header('location: '.SiteConfig::get()->getBaseURL());
}
Page::theme('Greeny By Ibrahim Ali');
Page::document()->getHeadNode()->addCSS('res/js/jstable/JSTable.css');
Page::document()->getHeadNode()->addJs('res/js/jstable/JSTable.js');
$users = UserFunctions::get()->getUsers();
$jsonx = new JsonX();
if(gettype($users) == 'array'){
    $jsonx->addArray('users', $users, FALSE);
}
else{
    $jsonx->add('err', $users);
    if($users == MySQLQuery::QUERY_ERR){
        $jsonx->add('details', UserFunctions::get()->getDBLink()->toJSON());
    }
}
$jsCode = new JsCode();
$jsCode->addCode('window.onload = function(){'
        . 'window.users = '.$jsonx.';'
        . 'window.datatable = new JSTable('
        . '{attach:true,header:true,'
        . '"parent-html-id":"main-content-area",'
        . 'cols:['
        . '{title:"User ID",key:"user-id"},'
        . '{title:"Username",key:"username"},'
        . '{title:"Email",key:"email"},'
        . '{title:"Display Name",key:"display-name"},'
        . '{title:"Registration Date",key:"reg-date"},'
        . '{title:"Last Login",key:"last-login"},'
        . '{title:"Status",key:"status"}'
        . '],data:window.users.users}'
        . ');'
        . '};');
Page::document()->getHeadNode()->addChild($jsCode);
Page::render();
