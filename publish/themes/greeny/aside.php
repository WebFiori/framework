<?php
/**
 * This function is used to include aside navigation menu in the page.
 * @param string $dir 
 * @param int $activeURL The number of the currently open page.
 * @return string HTML code.
 */
function getAsideNode(){
    $menu = new HTMLNode('div');
    $menu->addTextNode('Aside');
    return $menu;
}

