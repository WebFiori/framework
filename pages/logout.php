<?php
defined('ROOT_DIR') or die('Direct Access Not Allowed.');
$lang = WebsiteFunctions::get()->getMainSession()->getLang();
WebsiteFunctions::get()->getMainSession()->kill();
header('location: '.SiteConfig::get()->getBaseURL().'pages/login?lang='.$lang);