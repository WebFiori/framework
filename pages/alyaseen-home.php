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
$page = Page::get();
$page->usingTheme('Alyaseen Agri By Ibrahim Ali');
$document = $page->getDocument();
$arr1 = $page->getLanguage()->get('alyaseen-agri/home');
$mainContentArea = $document->getBody()->getChildByID('main-content-area');
$mainContentArea->setClassName('pa-'.$page->getWritingDir().'-col-four');
for($x = 0 ; $x < count($arr1['headers']) ; $x++){
    $mainContentArea->addChild(createSection($arr1['headers']['h'.($x + 1)], $arr1['sections-contents']['p'.($x + 1)]));
}
$document->getBody()->getChildByID('page-body')->addChild(createBranchesMap($page->getLanguage()));
echo $page->getDocument();


/**
 * 
 * @param Language $lang
 */
function createBranchesMap($lang){
    $node = new HTMLNode();
    $node->setClassName('pa-'.$lang->getWritingDir().'-col-six');
    $section = new HTMLNode('section');
    $secHeader = new HTMLNode('h3');
    $secHeadTitle = new HTMLNode('', '', TRUE);
    $secHeadTitle->setText($lang->get('alyaseen-agri/main-nav/branches'));
    $secHeader->addChild($secHeadTitle);
    $section->addChild($secHeader);
    $mapFrame = new HTMLNode('iframe');
    $mapFrame->setAttribute('src', 'https://www.google.com/maps/d/embed?mid=1qcxQ-YBFQ8WRkgjwIwao-OyuG2aUeKu7&hl='. strtolower($lang->getCode()));
    $mapFrame->setAttribute('width', '100%');
    $mapFrame->setAttribute('height', '500px');
    $section->addChild($mapFrame);
    $node->addChild($section);
    return $node;
}

function createSection($title,$paragText){
    $node = new HTMLNode('section');
    $titleNode = new HTMLNode('h3');
    $titleText = new HTMLNode('', '', TRUE);
    $titleText->setText($title);
    $titleNode->addChild($titleText);
    $node->addChild($titleNode);
    
    $paragraph = new PNode();
    $paragraph->addText($paragText);
    $node->addChild($paragraph);
    return $node;
}
