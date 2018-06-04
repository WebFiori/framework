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
define('SETUP_MODE', '');
require_once '../../root.php';
if(Config::get()->isConfig()){
    header('location: '.SiteConfig::get()->getHomePage());
}
Page::get()->loadTranslation(TRUE);
$pageLbls = LANGUAGE['pages']['setup']['email-account'];
Page::get()->setWritingDir(LANGUAGE['dir']);
Page::get()->setTitle($pageLbls['title']);
Page::get()->setDescription($pageLbls['description']);
Page::get()->loadAdminTheme();
$js = new JsCode;
$jsonx = new JsonX();
$jsonx->add('disconnected', LANGUAGE['general']['disconnected']);
$jsonx->add('success', $pageLbls['labels']['connected']);
$jsonx->add('checking-connection', $pageLbls['status']['checking-connection']);
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
$document = Page::get()->getDocument();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');
$container = new HTMLNode();
$document->addNode($container);
$container->setClassName('pa-container');
$container->addChild(stepsCounter(LANGUAGE['pages']['setup']['setup-steps'],2));
$container->addChild(pageBody($pageLbls));
$container->addChild(footer());
echo $document->toHTML();

function createEmailForm($lbls,$placeholders){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    
    $hostNode = new HTMLNode();
    $hostNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $hostLabel = new Label($lbls['server-address']);
    $hostLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $hostInput = new Input();
    $hostInput->setPlaceholder($placeholders['server-address']);
    $hostInput->setID('server-address-input');
    $hostInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $hostNode->addChild($hostLabel);
    $hostNode->addChild($hostInput);
    $form->addChild($hostNode);
    
    $portNode = new HTMLNode();
    $portNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $portLabel = new Label($lbls['port']);
    $portLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $portInput = new Input();
    $portInput->setPlaceholder($placeholders['port']);
    $portInput->setID('port-input');
    $portInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $portNode->addChild($portLabel);
    $portNode->addChild($portInput);
    $form->addChild($portNode);
    
    $addressNode = new HTMLNode();
    $addressNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $addressLabel = new Label($lbls['email-address']);
    $addressLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $addressInput = new Input();
    $addressInput->setPlaceholder($placeholders['email-address']);
    $addressInput->setID('address-input');
    $addressInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $addressNode->addChild($addressLabel);
    $addressNode->addChild($addressInput);
    $form->addChild($addressNode);
    
    $usernameNode = new HTMLNode();
    $usernameNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $usernameLabel = new Label($lbls['username']);
    $usernameLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $usernameInput = new Input();
    $usernameInput->setPlaceholder($placeholders['username']);
    $usernameInput->setID('username-input');
    $usernameInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $usernameNode->addChild($usernameLabel);
    $usernameNode->addChild($usernameInput);
    $form->addChild($usernameNode);
    
    $passwordNode = new HTMLNode();
    $passwordNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $passwordLabel = new Label($lbls['password']);
    $passwordLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $passwordInput = new Input();
    $passwordInput->setPlaceholder($placeholders['password']);
    $passwordInput->setID('password-input');
    $passwordInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $passwordNode->addChild($passwordLabel);
    $passwordNode->addChild($passwordInput);
    $form->addChild($passwordNode);
    
    $nameNode = new HTMLNode();
    $nameNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $nameLabel = new Label($lbls['name']);
    $nameLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $nameInput = new Input();
    $nameInput->setPlaceholder($placeholders['name']);
    $nameInput->setID('account-name-input');
    $nameInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $nameNode->addChild($nameLabel);
    $nameNode->addChild($nameInput);
    $form->addChild($nameNode);
    
    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lbls['check-connection']);
    $submit->setAttribute('onclick', 'return checkMailParams()');
    $submit->setAttribute('disabled', '');
    $submit->setID('check-input');
    $submit->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $messageNode = new PNode();
    $messageNode->setID('message-display');
    $messageNode->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-twelve');
    $form->addChild($submit);
    $form->addChild($messageNode);
    
    return $form;
}

function pageBody($pageLabels){
    $body = new HTMLNode();
    $body->setClassName('pa-row');
    $col = new HTMLNode();
    $col->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
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

function footer(){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    
    $prevButton = new HTMLNode('button');
    $prevButton->setAttribute('onclick', 'window.location.href = \'pages/setup/database-setup\'');
    $prevButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $prevButton->setID('prev-button');
    $prevButton->setAttribute('data-action', 'ok');
    $prevText = new HTMLNode('', FALSE, TRUE);
    $prevText->setText(LANGUAGE['general']['prev']);
    $prevButton->addChild($prevText);
    $node->addChild($prevButton);
    
    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'pages/setup/admin-account\'');
    $nextButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $nextButton->setID('next-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextText = new HTMLNode('', FALSE, TRUE);
    $nextText->setText(LANGUAGE['general']['next']);
    $nextButton->addChild($nextText);
    $node->addChild($nextButton);
    return $node;
}

function stepsCounter($lang,$active){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    $step1 = new HTMLNode();
    $step1->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $step1Text = new HTMLNode('', FALSE, TRUE);
    $step1Text->setText($lang['welcome']);
    $step1->addChild($step1Text);
    $node->addChild($step1);
    
    $step2 = new HTMLNode();
    $step2->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $step2Text = new HTMLNode('', FALSE, TRUE);
    $step2Text->setText($lang['database-setup']);
    $step2->addChild($step2Text);
    $node->addChild($step2);
    
    $step3 = new HTMLNode();
    $step3->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $step3Text = new HTMLNode('', FALSE, TRUE);
    $step3Text->setText($lang['email-account']);
    $step3->addChild($step3Text);
    $node->addChild($step3);
    
    $step4 = new HTMLNode();
    $step4->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $step4Text = new HTMLNode('', FALSE, TRUE);
    $step4Text->setText($lang['admin-account']);
    $step4->addChild($step4Text);
    $node->addChild($step4);
    
    $step5 = new HTMLNode();
    $step5->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $step5Text = new HTMLNode('', FALSE, TRUE);
    $step5Text->setText($lang['website-config']);
    $step5->addChild($step5Text);
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