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
$pageLbls = LANGUAGE['pages']['setup']['database-setup'];
Page::get()->setWritingDir(LANGUAGE['dir']);
Page::get()->setTitle($pageLbls['title']);
Page::get()->setDescription($pageLbls['description']);
Page::get()->loadAdminTheme();
$jsonx = new JsonX();
$jsonx->add('disconnected', LANGUAGE['general']['disconnected']);
$jsonx->add('success', $pageLbls['labels']['connected']);
$jsonx->add('checking-connection', $pageLbls['status']['checking-connection']);
foreach ($pageLbls['errors'] as $k => $v){
    $jsonx->add(''.$k, $v);
}
$js = new JsCode;
$js->addCode('window.onload = function(){'
        . 'window.messages = '.$jsonx.';'
        . 'document.getElementById(\'database-host-input\').oninput = dbInputChanged;
        document.getElementById(\'username-input\').oninput = dbInputChanged;
        document.getElementById(\'password-input\').oninput = dbInputChanged;
        document.getElementById(\'db-name-input\').oninput = dbInputChanged;'
        . '}');
$document = Page::get()->getDocument();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');
$container = new HTMLNode();
$document->addNode($container);
$container->setClassName('pa-container');
$container->addChild(stepsCounter(LANGUAGE['pages']['setup']['setup-steps'],1));
$container->addChild(pageBody($pageLbls));
$container->addChild(footer());
echo $document->toHTML();

function createDbInfoForm($lbls,$placeholders){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    $hostNode = new HTMLNode();
    $hostNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $hostLabel = new Label($lbls['host']);
    $hostLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $hostInput = new Input();
    $hostInput->setPlaceholder($placeholders['host']);
    $hostInput->setID('database-host-input');
    $hostInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $hostNode->addChild($hostLabel);
    $hostNode->addChild($hostInput);
    
    $usernameNode = new HTMLNode();
    $usernameNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $usernameLabel = new Label($lbls['username']);
    $usernameLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $usernameInput = new Input();
    $usernameInput->setID('username-input');
    $usernameInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $usernameInput->setPlaceholder($placeholders['username']);
    $usernameNode->addChild($usernameLabel);
    $usernameNode->addChild($usernameInput);
    
    $passwordNode = new HTMLNode();
    $passwordNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $passwordLabel = new Label($lbls['password']);
    $passwordLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $passwordInput = new Input('password');
    $passwordInput->setID('password-input');
    $passwordInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $passwordInput->setPlaceholder($placeholders['password']);
    $passwordNode->addChild($passwordLabel);
    $passwordNode->addChild($passwordInput);
    
    $dbNameNode = new HTMLNode();
    $dbNameNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $dbNameLabel = new Label($lbls['database-name']);
    $dbNameLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $dbNameInput = new Input('text');
    $dbNameInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $dbNameInput->setPlaceholder($placeholders['database-name']);
    $dbNameInput->setID('db-name-input');
    $dbNameNode->addChild($dbNameLabel);
    $dbNameNode->addChild($dbNameInput);
    
    $form->addChild($hostNode);
    $form->addChild($usernameNode);
    $form->addChild($passwordNode);
    $form->addChild($dbNameNode);
    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lbls['check-connection']);
    $submit->setAttribute('onclick', 'return checkConectionParams()');
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
function pageBody($pageLang){
    $body = new HTMLNode();
    $body->setClassName('pa-row');
    $col = new HTMLNode();
    $col->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $body->addChild($col);
    $p1 = new PNode();
    $p1->addText($pageLang['help']['h-1']);
    $col->addChild($p1);
    $p2 = new PNode();
    $p2->addText($pageLang['help']['h-2']);
    $col->addChild($p2);
    $p3 = new PNode();
    $p3->addText($pageLang['help']['h-3']);
    $col->addChild($p3);
    $col->addChild(createDbInfoForm($pageLang['labels'], $pageLang['placeholders']));
    return $body;
}

function footer(){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'pages/setup/email-account\'');
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