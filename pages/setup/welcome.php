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
$pageLbls = LANGUAGE['pages']['setup']['welcome'];
Page::get()->setWritingDir(LANGUAGE['dir']);
Page::get()->setTitle($pageLbls['title']);
Page::get()->setDescription($pageLbls['description']);
Page::get()->loadAdminTheme();
$document = Page::get()->getDocument();
$container = new HTMLNode();
$document->addNode($container);
$container->setClassName('pa-container');
$container->addChild(stepsCounter(LANGUAGE['pages']['setup']['setup-steps'],0));
$container->addChild(langSwitch());
$container->addChild(pageBody($pageLbls));
$container->addChild(footer());
echo $document->toHTML();

function pageBody($lbls){
    $body = new HTMLNode();
    $body->setClassName('pa-row');
    $col = new HTMLNode();
    $col->setClassName('pa-'.Page::get()->getWritingDir().'-col-twelve');
    $p1 = new PNode();
    $p1->addText($lbls['help']['h-1']);
    $col->addChild($p1);
    $p2 = new PNode();
    $p2->addText($lbls['help']['h-2']);
    $col->addChild($p2);
    $p3 = new PNode();
    $p3->addText($lbls['help']['h-3']);
    $col->addChild($p3);
    $ul = new UnorderedList();
    $li1 = new ListItem();
    $li1Text = new HTMLNode('', FALSE, TRUE);
    $li1Text->setText($lbls['help']['h-4']);
    $li1->addChild($li1Text);
    $ul->addChild($li1);
    $li2 = new ListItem();
    $li2Text = new HTMLNode('', FALSE, TRUE);
    $li2Text->setText($lbls['help']['h-5']);
    $li2->addChild($li2Text);
    $ul->addChild($li2);
    $col->addChild($ul);
    $body->addChild($col);
    return $body;
}

function langSwitch(){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    $arLang = new LinkNode('pages/setup/welcome?lang=ar', 'العربية');
    $arLang->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $node->addChild($arLang);
    $enLang = new LinkNode('pages/setup/welcome?lang=en', 'English');
    $enLang->setClassName('pa-'.Page::get()->getWritingDir().'-col-two');
    $node->addChild($enLang);
    return $node;
}

function footer(){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    $nextButton = new HTMLNode('button');
    $nextButton->setAttribute('onclick', 'window.location.href = \'pages/setup/database-setup\'');
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