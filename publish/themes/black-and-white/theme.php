<?php
/**
 * Theme meta-data
 */
$GLOBALS['THEME_META'] = array(
    'name'=>'Black and White Ibrahim Ali',
    'url'=>'',
    'author'=>'Ibrahim Ali',
    'author-url'=>'http://ibrahim-2017.blogspot.com',
    'version'=>'1.0',
    'license'=>'n/a',
    'license-url'=>'',
    'description'=>'Second theme ever made. High contrast black and white.',
    'directory'=>THEMES_DIR.'/black-and-white'
);
$GLOBALS['THEME_META']['css-directory'] = $GLOBALS['THEME_META']['directory'].'/css';
$GLOBALS['THEME_META']['js-directory'] = $GLOBALS['THEME_META']['directory'].'/js';
$GLOBALS['THEME_META']['images-directory'] = $GLOBALS['THEME_META']['directory'].'/images';
//load theme components
$GLOBALS['THEME_COMPONENTS'] = array(
    'head.php', 'header.php', 'footer.php',
    'aside-nav.php'
);
