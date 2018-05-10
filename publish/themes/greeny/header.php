<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
    $headerSec = new HTMLNode();
    $headerSec->setClassName('pa-row');
    
    if(isLangAndDirSet()){
        $arMainMenu = array();
        array_push($arMainMenu, new Link('ar\java\datatypes', 'أنواع البيانات'));
        array_push($arMainMenu, new Link('ar\java\strings', 'السلاسل النصية'));
        array_push($arMainMenu, new Link('ar\java\aretmetic-operators', 'المعاملات الحسابية'));
        array_push($arMainMenu, new Link('ar\java', 'المزيد...'));
        array_push($arMainMenu, new Link('ar\strings', 'G-Lib'));

        $enMainMenu = array();
        array_push($enMainMenu, new Link('en\java\datatypes', 'Datatypes'));
        array_push($enMainMenu, new Link('en\java\strings', 'Strings'));
        array_push($enMainMenu, new Link('en\java\aretmetic-operators', 'Arethmetics'));
        array_push($enMainMenu, new Link('en\java', 'More...'));
        array_push($enMainMenu, new Link('en\java\g-lib', 'G-Lib'));
        $header = new HTMLTag(3);
        $header->openTag('<div class="pa-row">');
        $header->openTag('<div class="pa-'.PAGE_DIR.'-col-one">');
        $header->openTag('<a href="https://www.programmingacademia.com">');
        $header->openTag('<div class="pa-row">');
        $header->content('<img itemscope itemtype="http://schema.org/ImageObject" itemprop="primaryImageOfPage" id="pa-logo" dir="'.PAGE_DIR.'" class="pa-'.PAGE_DIR.'-col-twelve" src="res/images/favicon.png" alt="website logo">');
        $header->closeTag('</div>');
        $header->openTag('<div id="website-title" class="pa-row">');
        $header->content('<p style="font-family: monospace; font-size: 9pt; margin: 0;  padding: 0">Programming</p>');
        $header->content('<p style="font-size: 9pt; margin: 0; padding: 0">Academia</p>');
        $header->closeTag('</div>');
        $header->closeTag('</a>');
        $header->closeTag('</div>');
        $header->openTag('<div class="pa-'.PAGE_DIR.'-col-eleven">');
        $header->openTag('<div class="pa-row">');
        $header->openTag('<div dir="'.PAGE_DIR.'" id="main-nav" class="pa-'.PAGE_DIR.'-col-twelve">');
        $header->openTag('<div id="pa-mobile-main-menu-button">');
        //$header->content('<img src="'.$GLOBALS['THEME_META']['images-directory'].'/main-menu.png">');
        $header->closeTag('</div>');
        $header->openTag('<nav itemscope itemtype="http://schema.org/SiteNavigationElement">');
        $header->content('<meta itemprop="name" content="Site Main Navigation">');
        $header->openTag('<ul>');
//        if(GLOBAL_LANG == LANG_AR){
//            foreach ($arMainMenu as $link){
//                $header->content('<li>'.$link.'</li>');
//            }
//        }
//        else if(GLOBAL_LANG == LANG_EN){
//            foreach ($enMainMenu as $link){
//                $header->content('<li>'.$link.'</li>');
//            }
//        }
        $header->closeTag('</ul>');
        $header->closeTag('</nav>');
        $header->closeTag('</div>');
        $header->closeTag('</div>');
        $header->openTag('<div class="pa-row" style="padding: 0;">');
        $header->openTag('<div style="padding: 0;" dir="'.PAGE_DIR.'" id="search-box-container" class="pa-'.PAGE_DIR.'-col-twelve">');
        $header->closeTag('</div>');
        $header->closeTag('</div>');
        $header->closeTag('</div>');
        $header->closeTag('</div>');
        return $headerSec;
    }
}

