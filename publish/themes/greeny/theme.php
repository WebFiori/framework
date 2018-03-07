<?php
/**
 * Theme meta-data
 */
$GLOBALS['THEME_META'] = array(
    'name'=>'Greeny By Ibrahim Ali',
    'url'=>'',
    'author'=>'Ibrahim Ali',
    'author-url'=>'http://ibrahim-2017.blogspot.com',
    'version'=>'1.0',
    'license'=>'n/a',
    'license-url'=>'',
    'description'=>'First theme ever made. A nice green colored elements That '
    . 'makes you thing about the nature.',
    'directory'=>THEMES_DIR.'/greeny'
);
$GLOBALS['THEME_META']['css-directory'] = $GLOBALS['THEME_META']['directory'].'/css';
$GLOBALS['THEME_META']['js-directory'] = $GLOBALS['THEME_META']['directory'].'/js';
$GLOBALS['THEME_META']['images-directory'] = $GLOBALS['THEME_META']['directory'].'/images';
//load theme components
require_once 'head.php';
require_once 'header.php';
require_once 'footer.php';
