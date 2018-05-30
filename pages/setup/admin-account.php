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
$pageLbls = LANGUAGE['pages']['setup']['admin-account'];
Page::get()->setWritingDir(LANGUAGE['dir']);
Page::get()->setTitle($pageLbls['title']);
Page::get()->setDescription($pageLbls['description']);
Page::get()->loadAdminTheme();
$js = new JsCode;
$jsonx = new JsonX();
$jsonx->add('disconnected', LANGUAGE['general']['disconnected']);
$jsonx->add('account-created', $pageLbls['labels']['acount-created']);
$jsonx->add('creating-account', $pageLbls['status']['creating-acc']);
$jsonx->add('password-missmatch', $pageLbls['errors']['password-missmatch']);
$jsonx->add('inv-email', $pageLbls['errors']['inv-email']);
$js->addCode('window.onload = function(){'
        . 'window.messages = '.$jsonx.';'
        . 'document.getElementById(\'address-input\').oninput = adminAccInputsChanged;'
        . 'document.getElementById(\'username-input\').oninput = adminAccInputsChanged;'
        . 'document.getElementById(\'password-input\').oninput = adminAccInputsChanged;'
        . 'document.getElementById(\'conf-pass-input\').oninput = adminAccInputsChanged;'
        . '}');
$document = Page::get()->getDocument();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');
$container = new HTMLNode();
$document->addNode($container);
$container->setClassName('pa-container');
$container->addChild(stepsCounter(LANGUAGE['pages']['setup']['setup-steps'],3));
$container->addChild(pageBody($pageLbls));
$container->addChild(footer());
echo $document->toHTML();

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
    $body->addChild(createAdminInfoForm($pageLabels['labels'], $pageLabels['placeholders']));
    return $body;
}

function footer(){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    
    $prevButton = new HTMLNode('button');
    $prevButton->setAttribute('onclick', 'window.location.href = \'pages/setup/website-config\'');
    $prevButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $prevButton->setID('prev-button');
    $prevButton->setAttribute('data-action', 'ok');
    $prevText = new HTMLNode('', FALSE, TRUE);
    $prevText->setText(LANGUAGE['general']['prev']);
    $prevButton->addChild($prevText);
    $node->addChild($prevButton);
    
    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'pages/setup/website-config\'');
    $nextButton->setAttribute('disabled', '');
    $nextButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $nextButton->setID('next-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextText = new HTMLNode('', FALSE, TRUE);
    $nextText->setText(LANGUAGE['general']['next']);
    $nextButton->addChild($nextText);
    $node->addChild($nextButton);
    return $node;
}

function createAdminInfoForm($lbls,$placeholders){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    
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
    $passwordInput = new Input('password');
    $passwordInput->setPlaceholder($placeholders['password']);
    $passwordInput->setID('password-input');
    $passwordInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $passwordNode->addChild($passwordLabel);
    $passwordNode->addChild($passwordInput);
    $form->addChild($passwordNode);
    
    $confPasswordNode = new HTMLNode();
    $confPasswordNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $confPasswordLabel = new Label($lbls['conf-password']);
    $confPasswordLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $confPasswordInput = new Input('password');
    $confPasswordInput->setPlaceholder($placeholders['conf-password']);
    $confPasswordInput->setID('conf-pass-input');
    $confPasswordInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $confPasswordNode->addChild($confPasswordLabel);
    $confPasswordNode->addChild($confPasswordInput);
    $form->addChild($confPasswordNode);
    
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

    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lbls['run-setup']);
    $submit->setAttribute('onclick', 'return runSetup()');
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
    
    $step6 = new HTMLNode();
    $step6->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $step6Text = new HTMLNode('', FALSE, TRUE);
    $step6Text->setText($lang['finish']);
    $step6->addChild($step6Text);
    $node->addChild($step6);
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
    else if($active == 5){
        $step6->setAttribute('style', 'background-color:#efaa32');
    }
    return $node;
}