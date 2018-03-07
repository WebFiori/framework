<?php
/**
 * This function is used to include aside navigation menu in the page.
 * @param string $asidMenuPath A path to .php file that has the method groupAside($active).
 * usually, each group of pages has one aside navigation menu.
 * @param int $activeURL The number of the currently open page.
 * @return string HTML code.
 */
function staticAsideNav($asidMenuPath,$activeURL=0){
    require_once $asidMenuPath.'/aside-menu.php';
    $aside = groupAside($activeURL);
    return $aside.'';
}
/**
 * Returns a string of PHP code that can be used to include aside navigation in 
 * the page dynamically. 
 * @return string
 */
function dynamicAsideNanv($path,$active=0){
    return '<?php echo staticAsideNav(\''.$path.'\','.$active.')?>'; 
}

