<?php
/**
 * This function is used to include aside navigation menu in the page.
 * @param string $dir 
 * @param int $activeURL The number of the currently open page.
 * @return string HTML code.
 */
function getAsideNavNode($dir,$activeURL=0){
    $menu = new HTMLNode('div');
    $menu->setID('aside-nav-container');
    return $menu;
    $menu = new AsideMenu($dir);
    $menu->addLink('pages/logout',LANGUAGE['aside']['logout']);
    $menu->addLink('pages/home',LANGUAGE['aside']['home']);
    $menu->addLink('pages/profile?user-id='.WebsiteFunctions::get()->getUserID(),LANGUAGE['aside']['profile']);
    $menu->addLink('pages/view-users',LANGUAGE['aside']['view-users']);
    $menu->addLink('pages/register',LANGUAGE['aside']['add-user']);
    $menu->addLink('pages/sys-info',LANGUAGE['aside']['sys-info']);
    $menu->setActive($activeURL);
    return ''.$menu;
}

