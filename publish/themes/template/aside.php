<?php
/**
 * This function is used to generate the content of the aside area. Usually, 
 * this area contain ads, images, links or widgets. Modify the content of 
 * this function to customize the aside area.
 * @return HTMLNode The function should return the aside area as 'HTMLNode' 
 * object.
 */
function getAsideNode(){
    $menu = new HTMLNode('div');
    $menu->addTextNode('Aside');
    return $menu;
}

