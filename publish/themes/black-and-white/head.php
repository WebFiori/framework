<?php

/**
 * Modify the content of this function to add custom head tags.
 * @return string Head tags as HTML string.
 */
function staticHeadTag($canonical='',$lang=LANG_EN){
    //must set the language first.
    PageAttributes::get()->setLang($lang);
    
    $headTag = new HeadTag();
    $headTag->setBaseURL(SiteConfig::get()->getBaseURL());
    $headTag->setCopyright(SiteConfig::get()->getCopyright());
    
    $headTag->setFavIcon($GLOBALS['THEME_META']['images-directory'].'/favicon.png');
    $headTag->setTitle(PageAttributes::get()->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
    $headTag->setDescription(PageAttributes::get()->getDescription());
    if($canonical != '' || $canonical !== FALSE){
        $headTag->setCanonical(SiteConfig::get()->getBaseURL().$canonical);
    }
    $headTag->addCSS($GLOBALS['THEME_META']['css-directory'].'/black-and-white.css');
    $retVal = ''.$headTag;
    return $retVal;
}
/**
 * Returns a string of PHP code that can be used to include the head tag dynamically.
 * @return string
 */
function dynamicHeadTag($canonical='',$lang=LANG_EN){
    $retVal = '<?php echo staticHeadTag(\''.$canonical.'\',\''.$lang.'\')?>';
    return $retVal;
}

