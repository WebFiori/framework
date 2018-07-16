<?php
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
$lang = WebsiteFunctions::get()->getMainSession()->getLang();
WebsiteFunctions::get()->getMainSession()->kill();
header('location: '.SiteConfig::get()->getBaseURL().'login?lang='.$lang);