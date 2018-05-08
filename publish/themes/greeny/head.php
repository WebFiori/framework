<?php

/**
 * Modify the content of this function to add custom head tags.
 * @return HTMLNode Head tags as HTML string.
 */
function staticHeadTag($canonical='',$lang='en'){
    //must set the language first.
    Page::get()->setLang($lang);
    $headTag = new HeadNode();
    $headTag->setTitle(Page::get()->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
    $headTag->setDescription(Page::get()->getDescription());
    $headTag->setBaseURL(SiteConfig::get()->getBaseURL());
    $headTag->addLink('icon', $GLOBALS['THEME_META']['images-directory'].'/favicon.png');
    if($canonical != '' || $canonical !== FALSE){
        $headTag->addLink('canonical',SiteConfig::get()->getBaseURL().$canonical);
    }
    $headTag->addCSS($GLOBALS['THEME_META']['css-directory'].'\programming-academia.css');
    $headTag->addMeta('copyright', SiteConfig::get()->getCopyright());
    $headTag->addMeta('robots', 'index, follow');
    
    return $headTag;
}
/**
 * Returns a string of PHP code that can be used to include the head tag dynamically.
 * @return string
 */
function dynamicHeadTag($canonical='',$lang=LANG_EN){
    $retVal = '<?php echo staticHeadTag(\''.$canonical.'\',\''.$lang.'\')?>';
    return $retVal;
}

function getHeadNode($dynamic=TRUE,$canonical=''){
    if($dynamic){
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText(dynamicHeadTag($canonical, Page::get()->getLang()));
        return $textNode;
    }
    return staticHeadTag($canonical);
}

