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
if(WebsiteFunctions::get()->getMainSession()->validateToken() != TRUE){
    header('location: '.SiteConfig::get()->getBaseURL().'pages/login');
}
SystemFunctions::get()->setSetupStage('website');
Page::header(FALSE);
Page::aside(FALSE);

Page::theme(SiteConfig::get()->getAdminThemeName());
$pageLbls = Page::translation()->get('pages/setup/website-config');
$translation = Page::translation();
Page::title($translation->get('pages/setup/website-config/title'));
Page::description($translation->get('pages/setup/website-config/description'));
Page::insert(stepsCounter(Page::translation()->get('pages/setup/setup-steps'),4), 'main-content-area');

$js = new JsCode;
$jsonx = new JsonX();
$jsonx->add('disconnected', Page::translation()->get('general/disconnected'));
$jsonx->add('saved', Page::translation()->get('general/saved'));
$jsonx->add('saving', Page::translation()->get('general/saving'));
$langsJson = new JsonX();
$langs = WebsiteFunctions::get()->getSiteConfigVars();
foreach ($langs['website-names'] as $k => $v){
    $arr = array(
        'name'=>$v,
        'description'=>$langs['site-descriptions'][$k]
    );
    $langsJson->addArray($k, $arr);
}
$js->addCode('window.onload = function(){'
        . 'window.messages = '.$jsonx.';'
        . 'window.sites = '.$langsJson.';'
        . 'document.getElementById(\'site-name-input\').oninput = siteInfoInputsChanged;'
        . 'document.getElementById(\'site-description-input\').oninput = siteInfoInputsChanged;'
        . 'document.getElementById(\'language-code-select\').oninput = siteInfoInputsChanged;'
        . 'document.getElementById(\'language-code-select\').oninput();'
        . '}');
$document = Page::document();
$document->getHeadNode()->addChild($js);
$document->getHeadNode()->addJs('res/js/setup.js');
Page::insert(pageBody($pageLbls,Page::translation()),'main-content-area');
Page::insert(footer(Page::translation()),'main-content-area');
echo $document->toHTML();

function pageBody($pageLabels,$lang){
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
    $body->addChild(createSiteInfoForm($pageLabels['labels'], $pageLabels['placeholders'],$lang));
    return $body;
}
/**
 * 
 * @param type $lbls
 * @param type $placeholders
 * @param Language $lang
 * @return \HTMLNode
 */
function createSiteInfoForm($lbls,$placeholders,$lang){
    $form = new HTMLNode('form');
    $form->setClassName('pa-row');
    
    $selectLbl = new Label($lang->get('pages/setup/website-config/labels/select-lang'));
    $selectLbl->setClassName('pa-'.Page::dir().'-col-12');
    $langSelect = new HTMLNode('select');
    $langSelect->setID('language-code-select');
    $langSelect->setClassName('pa-'.Page::dir().'-ltr-col-2');
    $langs = WebsiteFunctions::get()->getSiteConfigVars();
    foreach ($langs['website-names'] as $k => $v){
        $option = new HTMLNode('option');
        $option->setAttribute('value', $k);
        $option->addChild($option::createTextNode($k));
        $langSelect->addChild($option);
    }
    $form->addChild($selectLbl);
    $form->addChild($langSelect);
    $siteNameLabel = new Label($lbls['site-name']);
    $siteNameLabel->setClassName('pa-'.Page::dir().'-col-12');
    $siteNameInput = new Input();
    $siteNameInput->setPlaceholder($placeholders['site-name']);
    $siteNameInput->setID('site-name-input');
    $siteNameInput->setClassName('pa-'.Page::dir().'-ltr-col-5');
    $form->addChild($siteNameLabel);
    $form->addChild($siteNameInput);
    
    $descLabel = new Label($lbls['site-description']);
    $descLabel->setClassName('pa-'.Page::dir().'-col-12');
    $descInput = new Input();
    $descInput->setPlaceholder($placeholders['site-description']);
    $descInput->setID('site-description-input');
    $descInput->setClassName('pa-'.Page::dir().'-ltr-col-5');
    $form->addChild($descLabel);
    $form->addChild($descInput);
    
    $submit = new Input('submit');
    $submit->setAttribute('data-action', 'ok');
    $submit->setValue($lang->get('general/save'));
    $submit->setAttribute('onclick', 'return updateSiteInfo()');
    $submit->setAttribute('disabled', '');
    $submit->setID('save-input');
    $submit->setClassName('pa-'.Page::dir().'-ltr-col-3');
    $messageNode = new PNode();
    $messageNode->setID('message-display');
    $messageNode->setClassName('pa-'.Page::dir().'-ltr-col-12');
    $form->addChild($messageNode);
    $form->addChild($submit);
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
    $nextButton->setClassName('pa-'.Page::dir().'-col-3');
    $nextButton->setID('finish-button');
    $nextButton->setAttribute('data-action', 'ok');
    $nextButton->addChild(HTMLNode::createTextNode($lang->get('general/finish')));
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