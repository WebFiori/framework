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
$document = Page::get()->getDocument();
$container = new HTMLNode();
$document->addNode($container);
$container->setClassName('pa-container');
$container->addChild(stepsCounter(LANGUAGE['pages']['setup']['setup-steps'],2));
$container->addChild(pageBody());
$container->addChild(footer());
echo $document->toHTML();

function pageBody(){
    $body = new HTMLNode();
    $body->setClassName('pa-row');
    
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
    $nextButton->setAttribute('disabled', '');
    $nextButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $nextButton->setID('next-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextText = new HTMLNode('', FALSE, TRUE);
    $nextText->setText(LANGUAGE['general']['next']);
    $nextButton->addChild($nextText);
    $node->addChild($nextButton);
    
    $skipButton = new HTMLNode('button');
    $skipButton->setAttribute('onclick', 'window.location.href = \'pages/setup/admin-account\'');
    $skipButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $skipButton->setID('skip-button');
    $skipButton->setAttribute('data-action', 'ok');
    $skipText = new HTMLNode('', FALSE, TRUE);
    $skipText->setText(LANGUAGE['general']['skip']);
    $skipButton->addChild($skipText);
    $node->addChild($skipButton);
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