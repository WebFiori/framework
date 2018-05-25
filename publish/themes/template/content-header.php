<?php
/**
 * Modify this part to customize the header section of the page.
 * @param string $mainTitle A title to set for h1 tag.
 * @return string A string of HTML code for the header.
 */
function staticSectionHeader($mainTitle='Main Title'){
    $header = new HTMLNode('header');
    return $header;
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

