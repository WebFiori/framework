<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
        $header = new HTMLTag(3);
        $header->openTag('<div id="page-header">');

        $header->closeTag('</div>');
        return ''.$header;
}
/**
 * Returns a string of PHP code that can be used to include the top part of 
 * the page dynamically. Do not modify.
 * @return string
 */
function dynamicPageHeader(){
    $retVal = '<?php echo staticPageHeader()?>';
    return $retVal;
}

