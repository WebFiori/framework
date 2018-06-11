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

require_once '../root.php';

// check if user is logged in
//if not, go to login page
$activationTok = filter_input(INPUT_GET, 'activation-token');
if($activationTok != NULL && $activationTok != FALSE){
    if(WebsiteFunctions::get()->getMainSession()->validateToken() != TRUE){
        header('location: '.SiteConfig::get()->getBaseURL().'pages/login?activation-token='.$activationTok);
    }
    else{
        $user = WebsiteFunctions::get()->getMainSession()->getUser();
        if($user->getStatusCode() == 'N'){
            createPage($activationTok);
        }
        else{
            header('location: '.SiteConfig::get()->getHomePage());
        }
    }
}
else{
    if(WebsiteFunctions::get()->getMainSession()->validateToken() != TRUE){
        header('location: login');
    }
    else{
        $user = WebsiteFunctions::get()->getMainSession()->getUser();
        if($user->getStatusCode() == 'N'){
            createPage();
        }
        else{
            header('location: '.SiteConfig::get()->getHomePage());
        }
    }
}
function createPage($tok=null){
    $page = Page::get();
    $page->setHasHeader(FALSE);
    $page->setHasFooter(FALSE);
    $page->setHasAside(FALSE);
    $page->usingTheme(SiteConfig::get()->getAdminThemeName());
    $page->usingLanguage();
    $page->getDocument()->getHeadNode()->addCSS('publish/themes/greeny/css/login.css');
    $page->getDocument()->getHeadNode()->addJs('publish/themes/greeny/js/activate.js');
    //$page->getDocument()->getHeadNode()->addJs('publish/themes/greeny/js/AJAX.js');
    $lang = $page->getLanguage();
    $page->insertNode(createSubmitForm($tok,$lang), 'main-content-area');
    $jsonx = new JsonX();
    $jsonx->add('disconnected', $lang->get('general/disconnected'));
    $jsonx->add('activating', $lang->get('pages/activate-account/status/activating'));
    $jsonx->add('activated', $lang->get('pages/activate-account/status/activated'));
    $jsonx->add('inv-tok', $lang->get('pages/activate-account/errors/inncorect-token'));
    $jsonx->add('server-err', $lang->get('general/server-err'));
    $js = new JsCode();
    $js->addCode('window.onload = function(){'
            . 'window.messages = '.$jsonx.';'
            . 'document.getElementById(\'token-input\').oninput = tokInputChange'
            . '}');
    $page->getDocument()->getHeadNode()->addChild($js);
    echo $page->getDocument();
}

function createSubmitForm($tok=null , $lang=NULL){
    $form = new HTMLNode('form');
    $form->setID('login-form');
    $form->setAttribute('method', 'post');
    $form->setWritingDir(Page::get()->getWritingDir());
    $form->setClassName('pa-row');
    $formLabeldiv = new HTMLNode();
    $activateDiv = new HTMLNode();
    //$usernameDiv->setAttribute('style', 'background-color: #2d8659');
    $activateDiv->setClassName('pa-row');
    $activateDiv->addChild(new Label($lang->get('pages/activate-account/labels/activation-token')));
    $activateDiv->addChild(new Br());
    $activateDiv->addChild(new Input('text'));
    $activateDiv->children()->get(2)->setID('token-input');
    $activateDiv->children()->get(2)->setAttribute('required');
    $activateDiv->children()->get(2)->setAttribute('placeholder',$lang->get('pages/activate-account/placeholders/activation-token'));
    if($tok != NULL){
        $activateDiv->children()->get(2)->setAttribute('value',$tok);
    }
    $messageDiv = new HTMLNode();
    $messageDiv->setClassName('pa-row');
    $messageDiv->addChild(new Label(''));
    $messageDiv->children()->get(0)->setID('message');
    $submitDiv = new HTMLNode();
    $submitDiv->setClassName('pa-row');
    $submitDiv->addChild(new Input('submit'));
    $submitDiv->children()->get(0)->setID('activate-button');
    $submitDiv->children()->get(0)->setValue($lang->get('pages/activate-account/actions/activate'));
    $submitDiv->children()->get(0)->setAttribute('onclick','return activateAccount()');
    if($tok == NULL){
        $submitDiv->children()->get(0)->setAttribute('disabled');
    }
    else if(strlen($tok) != 64){
        $submitDiv->children()->get(0)->setAttribute('disabled');
    }
    //$submitDiv->childNodes()->get(0)->setAttribute('style','background-color:rgb(0, 155, 119)');
    $formLabeldiv->setAttribute('style', 'text-align: center;');
    $form->addChild($formLabeldiv);
    $form->addChild($activateDiv);
    $form->addChild($messageDiv);
    $form->addChild($submitDiv);
    $formLabeldiv->addChild(new Label($lang->get('pages/activate-account/title')));
    return $form; 
}