<?php
function getFooterNode(){
    $page = Page::get();
    $node = new HTMLNode('div');
    $socialMedia = new HTMLNode();
    $socialMedia->setClassName('pa-row');
    $socialMedia->setID('social-media-container');
    $socialMedia->setWritingDir($page->getWritingDir());
    
    $facebookIcon = new HTMLNode('img', FALSE);
    $facebookIcon->setAttribute('src', $page->getThemeImagesDir().'/facebook.png');
    $facebookIcon->setClassName('social-media-icon');
    $facebookLink = new HTMLNode('a');
    $facebookLink->setAttribute('href', '');
    $facebookLink->setAttribute('target', '_blank');
    $facebookLink->addChild($facebookIcon);
    $socialMedia->addChild($facebookLink);
    
    $twtrIcon = new HTMLNode('img', FALSE);
    $twtrIcon->setAttribute('src', $page->getThemeImagesDir().'/tweeter.png');
    $twtrIcon->setClassName('social-media-icon');
    $twtrLink = new HTMLNode('a');
    $twtrLink->setAttribute('href', '');
    $twtrLink->setAttribute('target', '_blank');
    $twtrLink->addChild($twtrIcon);
    $socialMedia->addChild($twtrLink);
    
    $linkedinIcon = new HTMLNode('img', FALSE);
    $linkedinIcon->setAttribute('src', $page->getThemeImagesDir().'/linkedin.png');
    $linkedinIcon->setClassName('social-media-icon');
    $linkedinLink = new HTMLNode('a');
    $linkedinLink->setAttribute('href', '');
    $linkedinLink->setAttribute('target', '_blank');
    $linkedinLink->addChild($linkedinIcon);
    $socialMedia->addChild($linkedinLink);
    
    $snapIcon = new HTMLNode('img', FALSE);
    $snapIcon->setAttribute('src', $page->getThemeImagesDir().'/snapchat.png');
    $snapIcon->setClassName('social-media-icon');
    $snapLink = new HTMLNode('a');
    $snapLink->setAttribute('href', '');
    $snapLink->setAttribute('target', '_blank');
    $snapLink->addChild($snapIcon);
    $socialMedia->addChild($snapLink);
    
    $node->addChild($socialMedia);
    $contactInfo = new HTMLNode();
    $contactInfo->setClassName('pa-row');
    $p = new PNode();
    $p->addText('013 581 5588', array('new-line'=>TRUE));
    $p->addText('info@alyaseenagri.com',array('new-line'=>TRUE));
    $contactInfo->addChild($p);
    $node->addChild($contactInfo);
    if($page->getLang() == 'AR'){
        $p->addText('شركة الياسين الزراعية, جميع الحقوق محفوظة © 2018');
    }
    else{
        $p->addText('Al Yaseen Agricultural Company, All Rights Reserved © 2018');
    }
    return $node;
}