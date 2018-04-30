<?php
/**
 * This function is used to include aside navigation menu in the page.
 * @param string $dir 
 * @param int $activeURL The number of the currently open page.
 * @return string HTML code.
 */
function staticAsideNav($dir,$activeURL=0){
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
/**
 * Returns a string of PHP code that can be used to include aside navigation in 
 * the page dynamically. 
 * @return string
 */
function dynamicAsideNanv($dir,$active=0){
    return '<?php echo staticAsideNav(\''.$dir.'\','.$active.')?>'; 
}

