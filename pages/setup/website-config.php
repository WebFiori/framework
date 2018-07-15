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
if(WebsiteFunctions::get()->getMainSession()->validateToken() != TRUE){
    header('location: '.SiteConfig::get()->getBaseURL().'pages/login');
}
SystemFunctions::get()->setSetupStage('website');
$page = Page::get();
$page->setHasHeader(FALSE);
$page->setHasAside(FALSE);

$page->usingTheme(SiteConfig::get()->getAdminThemeName());
$pageLbls = $page->getLanguage()->get('pages/setup/website-config');
$translation = $page->getLanguage();
$page->setTitle($translation->get('pages/setup/website-config/title'));
$page->setDescription($translation->get('pages/setup/website-config/description'));
$page->insertNode(stepsCounter($page->getLanguage()->get('pages/setup/setup-steps'),4), 'main-content-area');

$js = new JsCode;
$jsonx = new JsonX();
$jsonx->add('disconnected', $page->getLanguage()->get('general/disconnected'));
$jsonx->add('saved', $page->getLanguage()->get('general/saved'));
$jsonx->add('saving', $page->getLanguage()->get('general/saving'));
$js->addCode('window.onload = function(){'
        . 'window.messages = '.$jsonx.';'
        . 'document.getElementById(\'site-name-input\').oninput = siteInfoInputsChanged;'
        . 'document.getElementById(\'site-description-input\').oninput = siteInfoInputsChanged;'
        . '}');
$document = Page::get()->getDocument();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');
$page->insertNode(pageBody($pageLbls,$page->getLanguage()),'main-content-area');
$page->insertNode(footer($page->getLanguage()),'main-content-area');
echo $document->toHTML();

function pageBody($pageLabels,$lang){
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
    $body->addChild(createSiteInfoForm($pageLabels['labels'], $pageLabels['placeholders'],$lang));
    return $body;
}

function createSiteInfoForm($lbls,$placeholders,$lang){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    
    $siteNameNode = new HTMLNode();
    $siteNameNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $siteNameLabel = new Label($lbls['site-name']);
    $siteNameLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $siteNameInput = new Input();
    $siteNameInput->setPlaceholder($placeholders['site-name']);
    $siteNameInput->setID('site-name-input');
    $siteNameInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $siteNameNode->addChild($siteNameLabel);
    $siteNameNode->addChild($siteNameInput);
    $form->addChild($siteNameNode);
    
    $descNode = new HTMLNode();
    $descNode->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $descLabel = new Label($lbls['site-description']);
    $descLabel->setClassName('pa-'.Page::get()->getWritingDir().'-col-ten');
    $descInput = new Input();
    $descInput->setPlaceholder($placeholders['site-description']);
    $descInput->setID('site-description-input');
    $descInput->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-seven');
    $descNode->addChild($descLabel);
    $descNode->addChild($descInput);
    $form->addChild($descNode);
    
    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lang->get('general/save'));
    $submit->setAttribute('onclick', 'return updateSiteInfo()');
    $submit->setAttribute('disabled', '');
    $submit->setID('save-input');
    $submit->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-four');
    $messageNode = new PNode();
    $messageNode->setID('message-display');
    $messageNode->setClassName('pa-'.Page::get()->getWritingDir().'-ltr-col-twelve');
    $form->addChild($submit);
    $form->addChild($messageNode);
    
    return $form;
}
/**
 * 
 * @param Language $lang
 * @return \HTMLNode
 */
function footer($lang){
    $node = new HTMLNode();
    $node->setClassName('pa-row');

    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'home\'');
    $nextButton->setAttribute('disabled', '');
    $nextButton->setClassName('pa-'.Page::get()->getWritingDir().'-col-three');
    $nextButton->setID('finish-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextText = new HTMLNode('', FALSE, TRUE);
    $nextText->setText($lang->get('general/finish'));
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