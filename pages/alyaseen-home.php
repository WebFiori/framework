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
$document->getBody()->setClassName('container');
$document->getChildByID('page-header')->setClassName('pa-row');
$document->getChildByID('page-body')->setClassName('pa-row');
$document->getChildByID('page-footer')->setClassName('pa-row');
$newsContainer = $document->getBody()->getChildByID('aside-container');
$newsContainer->setClassName('pa-'.$page->getWritingDir().'-col-one');
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
//function createNewsContainer($dir='ltr'){
//    $node = new HTMLNode();
//    $node->setClassName('pa-row');
//    $p = new PNode();
//    $img = new HTMLNode('img');
//    $img->setClassName('news-img');
//    $img->setAttribute('src', 'publish/themes/alyaseen-agri/images/favicon.png');
//    $p->addChild($img);
//    $link = new LinkNode('', 'A_Link_to_article');
//    $p->addChild($link);
//    $node->addChild($p);
//    return $node;
//}
//function createProductsRow($dir='ltr'){
//    $row = new HTMLNode();
//    $row->setClassName('pa-row');
//    for($x = 0 ; $x < 4 ; $x++){
//        $row->addChild(createProductContainer($dir));
//    }
//    return $row;
//}
//function createProductContainer($dir='ltr'){
//    $node = new HTMLNode();
//    $node->setClassName('pa-'.$dir.'-col-three');
//    $prodImg = new HTMLNode('img',FALSE);
//    $prodImg->setClassName('product-img');
//    $prodImg->setAttribute('src', 'publish/themes/alyaseen-agri/images/favicon.png');
//    $prodLink = new LinkNode('', 'A_Product');
//    $p = new PNode();
//    $p->addChild($prodImg);
//    $p2 = new PNode();
//    $p2->addChild($prodLink);
//    $node->addChild($p);
//    $node->addChild($p2);
//    return $node;
//}