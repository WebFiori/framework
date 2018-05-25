<?php
/**
 * This function is used to include aside navigation menu in the page.
 * @param string $dir 
 * @param int $activeURL The number of the currently open page.
 * @return string HTML code.
 */
function getAsideNavNode($activeURL=0){
    $menu = new HTMLNode('div');
    $menu->setID('aside-navigation');
    return $menu;
}

