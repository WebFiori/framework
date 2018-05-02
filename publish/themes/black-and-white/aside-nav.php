<?php
/**
 * This function is used to include aside navigation menu in the page.
 * @param string $dir 
 * @param int $activeURL The number of the currently open page.
 * @return string HTML code.
 */
function staticAsideNav($dir,$activeURL=0){
    $menu = new AsideMenu($dir);

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

