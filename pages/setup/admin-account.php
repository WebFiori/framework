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
SystemFunctions::get()->setSetupStage('admin');
Page::header(FALSE);
Page::aside(FALSE);

Page::theme(SiteConfig::get()->getAdminThemeName());
$pageLbls = Page::translation()->get('pages/setup/admin-account');
$translation = Page::translation();
Page::title($translation->get('pages/setup/admin-account/title'));
Page::description($translation->get('pages/setup/admin-account/description'));
Page::insert(stepsCounter(Page::translation()->get('pages/setup/setup-steps'),3), 'main-content-area');
$js = new JsCode;
$jsonx = new JsonX();
$jsonx->add('disconnected', Page::translation()->get('general/disconnected'));
$jsonx->add('account-created', Page::translation()->get('general/saved'));
$jsonx->add('creating-account', Page::translation()->get('general/saving'));
$jsonx->add('password-missmatch', $pageLbls['errors']['password-missmatch']);
$jsonx->add('inv-email', $pageLbls['errors']['inv-email']);
$js->addCode('window.onload = function(){'
        . 'window.messages = '.$jsonx.';'
        . 'document.getElementById(\'address-input\').oninput = adminAccInputsChanged;'
        . 'document.getElementById(\'username-input\').oninput = adminAccInputsChanged;'
        . 'document.getElementById(\'password-input\').oninput = adminAccInputsChanged;'
        . 'document.getElementById(\'conf-pass-input\').oninput = adminAccInputsChanged;'
        . '}');
$document = Page::document();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');

Page::insert(pageBody($pageLbls),'main-content-area');
Page::insert(footer(Page::translation()),'main-content-area');
Page::render();

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
    $body->addChild(createAdminInfoForm($pageLabels['labels'], $pageLabels['placeholders']));
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
    $prevButton->setAttribute('onclick', 'window.location.href = \'s/smtp-account\'');
    $prevButton->setClassName('pa-'.Page::dir().'-col-3');
    $prevButton->setID('prev-button');
    $prevButton->setAttribute('data-action', 'ok');
    $prevButton->addChild(HTMLNode::createTextNode($lang->get('general/previous')));
    $node->addChild($prevButton);
    
    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'s/website-config\'');
    $nextButton->setAttribute('disabled', '');
    $nextButton->setClassName('pa-'.Page::dir().'-col-3');
    $nextButton->setID('next-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextButton->addChild(HTMLNode::createTextNode($lang->get('general/next')));
    $node->addChild($nextButton);
    return $node;
}

function createAdminInfoForm($lbls,$placeholders){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    
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
    
    $confPasswordNode = new HTMLNode();
    $confPasswordNode->setClassName('pa-'.Page::dir().'-col-12');
    $confPasswordLabel = new Label($lbls['conf-password']);
    $confPasswordLabel->setClassName('pa-'.Page::dir().'-col-10');
    $confPasswordInput = new Input('password');
    $confPasswordInput->setPlaceholder($placeholders['conf-password']);
    $confPasswordInput->setID('conf-pass-input');
    $confPasswordInput->setClassName('pa-'.Page::dir().'-col-4');
    $confPasswordNode->addChild($confPasswordLabel);
    $confPasswordNode->addChild($confPasswordInput);
    $form->addChild($confPasswordNode);
    
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

    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lbls['run-setup']);
    $submit->setAttribute('onclick', 'return runSetup()');
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