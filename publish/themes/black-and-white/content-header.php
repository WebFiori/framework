<?php
/**
 * Modify this part to customize the header section of the page.
 * @param string $mainTitle A title to set for h1 tag.
 * @return string A string of HTML code for the header.
 */
function staticSectionHeader($mainTitle='Main Title'){
    $header = new HTMLTag(5);
    $header->openTag('<header id="header" class="pa-row" itemscope itemtype="http://schema.org/WPHeader">');
    //adding content to the header (usually links and h tags).
    $header->openTag('<h1 id="page-title" itemprop="name">');
    $header->content(''.$mainTitle.'');
    $header->closeTag('</h1>');
    $header->closeTag('</header>');
    return ''.$header;
}

/**
 * Returns a string of PHP code that can be used to include header section in 
 * the page dynamically. 
 * @return string
 */
function dynamicSectionHeader($mainTitle='Main Title'){
    $retVal = '<?php echo staticSectionHeader(\''.$mainTitle.'\')?>';
    return $retVal;
}

