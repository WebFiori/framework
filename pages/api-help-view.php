<?php

/* 
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
        . 'Login required.'
        . '</p>'
        . '</body>'
        . '</html>');
}
if(isset($_GET['api-name'])){
    if(class_exists($_GET['api-name'])){
        Page::theme(SiteConfig::get()->getAdminThemeName());
        Page::document()->getHeadNode()->addJs('res/js/js-ajax-helper-1.0.0/AJAX.js');
        Page::document()->getHeadNode()->addJs('res/js/api-help.js');
        $js = new JsCode();
        $js->addCode('window.onload = function(){'
                . 'window.apiName = "'.$_GET['api-name'].'";'
                . 'getAPIInfo();'
                . '}');
        Page::document()->getHeadNode()->addChild($js);
        Page::document()->getChildByID('main-content-area')->addChild(HTMLNode::createTextNode('Not Implemented'));
        Page::render();
    }
    else{
        header("HTTP/1.1 404 Not found");
        die(''
            . '<!DOCTYPE html>'
            . '<html>'
            . '<head>'
            . '<title>Not Found</title>'
            . '</head>'
            . '<body>'
            . '<h1>404 - Not Found</h1>'
            . '<hr>'
            . '<p>'
            . 'The resource <b>'.Util::getRequestedURL().'</b> was not found on the server.'
            . '</p>'
            . '</body>'
            . '</html>');
    }
}
else{
    header('location: '.SiteConfig::get()->getBaseURL());
}