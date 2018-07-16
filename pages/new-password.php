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
// check if user is logged in
//if not, go to login page
$token = filter_input(INPUT_GET, 'token');
if($token != NULL && $token != FALSE){
    $tokValidity = PasswordFunctions::get()->validateResetToken($token);
    if($tokValidity === TRUE){
        createPage($token);
    }
    else if($tokValidity == MySQLQuery::QUERY_ERR){
        echo 'Database Error';
    }
    else{
       // header('location: '.SiteConfig::get()->getHomePage());
    }
}
else{
    //header('location: login'.SiteConfig::get()->getHomePage());
}
function createPage($tok=null){
    $page = Page::get();
    $page->setHasHeader(FALSE);
    $page->setHasFooter(FALSE);
    $page->setHasAside(FALSE);
    $page->usingTheme(SiteConfig::get()->getAdminThemeName());
    $page->usingLanguage();
    $page->getDocument()->getHeadNode()->addCSS('publish/themes/greeny/css/login.css');
    $page->getDocument()->getHeadNode()->addJs('res/js/login.js');
    $page->getDocument()->getHeadNode()->addJs('res/js/reset.js');
    
    $lang = $page->getLanguage();
    $page->insertNode(createSubmitForm($lang), 'main-content-area');
    $jsonx = new JsonX();
    $jsonx->add('disconnected', $lang->get('general/disconnected'));
    $jsonx->add('resetting', $lang->get('pages/new-password/status/resetting'));
    $jsonx->add('resetted', $lang->get('pages/new-password/status/resetted'));
    $jsonx->add('password-missmatch', $lang->get('pages/new-password/errors/password-missmatch'));
    $jsonx->add('inv-email', $lang->get('pages/new-password/errors/inv-email'));
    $jsonx->add('server-err', $lang->get('general/server-err'));
    $js = new JsCode();
    $js->addCode('window.onload = function(){'
            . 'window.messages = '.$jsonx.';'
            . 'window.token = \''.$tok.'\';'
            . 'document.getElementById(\'email-input\').oninput = resetPassInputChanged;'
            . 'document.getElementById(\'password-input\').oninput = resetPassInputChanged;'
            . 'document.getElementById(\'conf-pass-input\').oninput = resetPassInputChanged;'
            . '}');
    $page->getDocument()->getHeadNode()->addChild($js);
    echo $page->getDocument();
}

function createSubmitForm($lang=NULL){
    $form = new HTMLNode('form');
    $form->setID('login-form');
    $form->setAttribute('method', 'post');
    $form->setWritingDir(Page::get()->getWritingDir());
    $form->setClassName('pa-row');
    $formLabeldiv = new HTMLNode();
    
    $emailDiv = new HTMLNode();
    $emailDiv->setClassName('pa-row');
    $emailDiv->addChild(new Label($lang->get('pages/new-password/labels/email')));
    $emailDiv->addChild(new Br());
    $emailDiv->addChild(new Input('email'));
    $emailDiv->children()->get(2)->setID('email-input');
    $emailDiv->children()->get(2)->setAttribute('required');
    $emailDiv->children()->get(2)->setAttribute('placeholder',$lang->get('pages/reset-passwor/placeholders/email'));
    
    $passDiv = new HTMLNode();
    $passDiv->setClassName('pa-row');
    $passDiv->addChild(new Label($lang->get('pages/new-password/labels/password')));
    $passDiv->addChild(new Br());
    $passDiv->addChild(new Input('password'));
    $passDiv->children()->get(2)->setID('password-input');
    $passDiv->children()->get(2)->setAttribute('required');
    $passDiv->children()->get(2)->setAttribute('placeholder',$lang->get('pages/reset-passwor/placeholders/password'));
    
    $confPassDiv = new HTMLNode();
    $confPassDiv->setClassName('pa-row');
    $confPassDiv->addChild(new Label($lang->get('pages/new-password/labels/conf-pass')));
    $confPassDiv->addChild(new Br());
    $confPassDiv->addChild(new Input('password'));
    $confPassDiv->children()->get(2)->setID('conf-pass-input');
    $confPassDiv->children()->get(2)->setAttribute('required');
    $confPassDiv->children()->get(2)->setAttribute('placeholder',$lang->get('pages/reset-passwor/placeholders/conf-pass'));
    
    $messageDiv = new HTMLNode();
    $messageDiv->setClassName('pa-row');
    $messageDiv->addChild(new Label(''));
    $messageDiv->children()->get(0)->setID('message');
    
    $submitDiv = new HTMLNode();
    $submitDiv->setClassName('pa-row');
    $submitDiv->addChild(new Input('submit'));
    $submitDiv->children()->get(0)->setID('submit-button');
    $submitDiv->children()->get(0)->setValue($lang->get('pages/new-password/actions/reset'));
    $submitDiv->children()->get(0)->setAttribute('onclick','return resetPass()');
    $submitDiv->children()->get(0)->setAttribute('disabled');
    
    $formLabeldiv->setAttribute('style', 'text-align: center;');
    $form->addChild($formLabeldiv);
    $form->addChild($emailDiv);
    $form->addChild($passDiv);
    $form->addChild($confPassDiv);
    $form->addChild($messageDiv);
    $form->addChild($submitDiv);
    $formLabeldiv->addChild(new Label($lang->get('pages/new-password/title')));
    return $form; 
}