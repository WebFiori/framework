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
if(Config::get()->isConfig()){
    header('location: '.SiteConfig::get()->getHomePage());
}
SystemFunctions::get()->setSetupStage('smtp');
Page::header(FALSE);
Page::aside(FALSE);

Page::theme(SiteConfig::get()->getAdminThemeName());
$pageLbls = Page::translation()->get('pages/setup/email-account');
$translation = Page::translation();
Page::title($translation->get('pages/setup/email-account/title'));
Page::description($translation->get('pages/setup/email-account/description'));
Page::insert(stepsCounter(Page::translation()->get('pages/setup/setup-steps'),2), 'main-content-area');
$js = new JsCode;
$jsonx = new JsonX();
$jsonx->add('disconnected', Page::translation()->get('general/disconnected'));
$jsonx->add('success', $pageLbls['labels']['connected']);
$jsonx->add('checking-connection', Page::translation()->get('general/checking'));
$jsonx->add('inv_mail_host_or_port', $pageLbls['errors']['inv_mail_host_or_port']);
$jsonx->add('inv_username_or_pass', $pageLbls['errors']['inv_username_or_pass']);
$js->addCode('window.onload = function(){'
        . 'window.messages = '.$jsonx.';'
        . 'document.getElementById(\'server-address-input\').oninput = emailInputChanged;'
        . 'document.getElementById(\'port-input\').oninput = emailInputChanged;'
        . 'document.getElementById(\'address-input\').oninput = emailInputChanged;'
        . 'document.getElementById(\'username-input\').oninput = emailInputChanged;'
        . 'document.getElementById(\'password-input\').oninput = emailInputChanged;'
        . 'document.getElementById(\'account-name-input\').oninput = emailInputChanged;'
        . '}');
$document = Page::document();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');
Page::insert(pageBody($pageLbls),'main-content-area');
Page::insert(footer(Page::translation()),'main-content-area');
Page::render();

function createEmailForm($lbls,$placeholders){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    
    $hostNode = new HTMLNode();
    $hostNode->setClassName('pa-'.Page::dir().'-col-12');
    $hostLabel = new Label($lbls['server-address']);
    $hostLabel->setClassName('pa-'.Page::dir().'-col-10');
    $hostInput = new Input();
    $hostInput->setPlaceholder($placeholders['server-address']);
    $hostInput->setID('server-address-input');
    $hostInput->setClassName('pa-'.Page::dir().'-ltr-col-4');
    $hostNode->addChild($hostLabel);
    $hostNode->addChild($hostInput);
    $form->addChild($hostNode);
    
    $portNode = new HTMLNode();
    $portNode->setClassName('pa-'.Page::dir().'-col-12');
    $portLabel = new Label($lbls['port']);
    $portLabel->setClassName('pa-'.Page::dir().'-col-10');
    $portInput = new Input();
    $portInput->setPlaceholder($placeholders['port']);
    $portInput->setID('port-input');
    $portInput->setClassName('pa-'.Page::dir().'-col-4');
    $portNode->addChild($portLabel);
    $portNode->addChild($portInput);
    $form->addChild($portNode);
    
    $addressNode = new HTMLNode();
    $addressNode->setClassName('pa-'.Page::dir().'-col-12');
    $addressLabel = new Label($lbls['email-address']);
    $addressLabel->setClassName('pa-'.Page::dir().'-col-10');
    $addressInput = new Input();
    $addressInput->setPlaceholder($placeholders['email-address']);
    $addressInput->setID('address-input');
    $addressInput->setClassName('pa-'.Page::dir().'-col-4');
    $addressNode->addChild($addressLabel);
    $addressNode->addChild($addressInput);
    $form->addChild($addressNode);
    
    $usernameNode = new HTMLNode();
    $usernameNode->setClassName('pa-'.Page::dir().'-col-12');
    $usernameLabel = new Label($lbls['username']);
    $usernameLabel->setClassName('pa-'.Page::dir().'-col-10');
    $usernameInput = new Input();
    $usernameInput->setPlaceholder($placeholders['username']);
    $usernameInput->setID('username-input');
    $usernameInput->setClassName('pa-'.Page::dir().'-col-4');
    $usernameNode->addChild($usernameLabel);
    $usernameNode->addChild($usernameInput);
    $form->addChild($usernameNode);
    
    $passwordNode = new HTMLNode();
    $passwordNode->setClassName('pa-'.Page::dir().'-col-12');
    $passwordLabel = new Label($lbls['password']);
    $passwordLabel->setClassName('pa-'.Page::dir().'-col-10');
    $passwordInput = new Input('password');
    $passwordInput->setPlaceholder($placeholders['password']);
    $passwordInput->setID('password-input');
    $passwordInput->setClassName('pa-'.Page::dir().'-col-4');
    $passwordNode->addChild($passwordLabel);
    $passwordNode->addChild($passwordInput);
    $form->addChild($passwordNode);
    
    $nameNode = new HTMLNode();
    $nameNode->setClassName('pa-'.Page::dir().'-col-12');
    $nameLabel = new Label($lbls['name']);
    $nameLabel->setClassName('pa-'.Page::dir().'-col-10');
    $nameInput = new Input();
    $nameInput->setPlaceholder($placeholders['name']);
    $nameInput->setID('account-name-input');
    $nameInput->setClassName('pa-'.Page::dir().'-col-4');
    $nameNode->addChild($nameLabel);
    $nameNode->addChild($nameInput);
    $form->addChild($nameNode);
    
    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lbls['check-connection']);
    $submit->setAttribute('onclick', 'return checkMailParams()');
    $submit->setAttribute('disabled', '');
    $submit->setID('check-input');
    $submit->setClassName('pa-'.Page::dir().'-col-4');
    $messageNode = new PNode();
    $messageNode->setID('message-display');
    $messageNode->setClassName('pa-'.Page::dir().'-col-12');
    $form->addChild($submit);
    $form->addChild($messageNode);
    
    return $form;
}

function pageBody($pageLabels){
    $body = new HTMLNode();
    $body->setClassName('pa-row');
    $col = new HTMLNode();
    $col->setClassName('pa-'.Page::dir().'-col-12');
    $p1 = new PNode();
    $p1->addText($pageLabels['help']['h-1']);
    $col->addChild($p1);
    $p2 = new PNode();
    $p2->addText($pageLabels['help']['h-2']);
    $col->addChild($p2);
    $body->addChild($col);
    $body->addChild(createEmailForm($pageLabels['labels'], $pageLabels['placeholders']));
    return $body;
}
/**
 * 
 * @param Language $lang
 * @return \HTMLNode
 */
function footer($lang){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    
    $prevButton = new HTMLNode('button');
    $prevButton->setAttribute('onclick', 'window.location.href = \'s/database-setup\'');
    $prevButton->setClassName('pa-'.Page::dir().'-col-3');
    $prevButton->setID('prev-button');
    $prevButton->setAttribute('data-action', 'ok');
    $prevButton->addChild(HTMLNode::createTextNode($lang->get('general/previous')));
    $node->addChild($prevButton);
    
    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'s/admin-account\'');
    $nextButton->setClassName('pa-'.Page::dir().'-col-3');
    $nextButton->setID('next-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextButton->addChild(HTMLNode::createTextNode($lang->get('general/next')));
    $node->addChild($nextButton);
    return $node;
}

function stepsCounter($lang,$active){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    $step1 = new HTMLNode();
    $step1->setClassName('pa-'.Page::dir().'-col-2');
    $step1->addChild(HTMLNode::createTextNode($lang['welcome']));
    $node->addChild($step1);
    
    $step2 = new HTMLNode();
    $step2->setClassName('pa-'.Page::dir().'-col-2');
    $step2->addChild(HTMLNode::createTextNode($lang['database-setup']));
    $node->addChild($step2);
    
    $step3 = new HTMLNode();
    $step3->setClassName('pa-'.Page::dir().'-col-2');
    $step3->addChild(HTMLNode::createTextNode($lang['email-account']));
    $node->addChild($step3);
    
    $step4 = new HTMLNode();
    $step4->setClassName('pa-'.Page::dir().'-col-2');
    $step4->addChild(HTMLNode::createTextNode($lang['admin-account']));
    $node->addChild($step4);
    
    $step5 = new HTMLNode();
    $step5->setClassName('pa-'.Page::dir().'-col-2');
    $step5->addChild(HTMLNode::createTextNode($lang['website-config']));
    $node->addChild($step5);
    
    if($active == 0){
        $step1->setAttribute('style', 'background-color:#efaa32');
    }
    else if($active == 1){
        $step2->setAttribute('style', 'background-color:#efaa32');
    }
    else if($active == 2){
        $step3->setAttribute('style', 'background-color:#efaa32');
    }
    else if($active == 3){
        $step4->setAttribute('style', 'background-color:#efaa32');
    }
    else if($active == 4){
        $step5->setAttribute('style', 'background-color:#efaa32');
    }
    return $node;
}