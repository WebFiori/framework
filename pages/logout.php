<?php
require_once '../root.php';

$lang = WebsiteFunctions::get()->getMainSession()->getLang();
WebsiteFunctions::get()->getMainSession()->kill();
header('location: '.SiteConfig::get()->getBaseURL().'pages/login?lang='.$lang);