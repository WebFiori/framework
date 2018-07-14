<?php
require_once 'root.php';
Util::displayErrors();
$GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
if($GLOBALS['SYS_STATUS'] === Util::MISSING_CONF_FILE){
    SystemFunctions::get()->createConfigFile();
    WebsiteFunctions::get()->createSiteConfigFile();
    MailFunctions::get()->createEmailConfigFile();
    $GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
}
createRoutes();
//at this stage, only check for configuration files
//other errors checked as needed later.
if($GLOBALS['SYS_STATUS'] === Util::NEED_CONF){
    $requestedURI = Util::getRequestedURL();
    if($requestedURI == '/s/welcome' ||
       $requestedURI == '/s/database-setup' ||
       $requestedURI == '/s/smtp-account' || 
       $requestedURI == '/s/admin-account' ||
       $requestedURI == '/s/website-config' ||
       $requestedURI == '/SysAPIs'){
        Router::get()->route($requestedURI);
    }
    else{
        Router::get()->route(SiteConfig::get()->getBaseURL().Util::NEED_CONF);
    }
}
else{
    Router::get()->route(Util::getRequestedURL());
}
/**
 * A function to create routes.
 */
function createRoutes(){
    $router = Router::get();
    
    //routes to configuration views
    $router->addRoute('/s/welcome', 'setup/welcome.php', Router::VIEW_ROUTE);
    $router->addRoute('/s/database-setup', 'setup/database-setup.php', Router::VIEW_ROUTE);
    $router->addRoute('/s/smtp-account', 'setup/email-account.php', Router::VIEW_ROUTE);
    $router->addRoute('/s/admin-account', 'setup/admin-account.php', Router::VIEW_ROUTE);
    $router->addRoute('/s/website-config', 'setup/website-config.php', Router::VIEW_ROUTE);
    $router->addRoute('/'.Util::NEED_CONF, function(){
        if(isset($GLOBALS['SYS_STATUS']) && $GLOBALS['SYS_STATUS'] === Util::NEED_CONF){
            SystemFunctions::get()->initSetupSession();
            $currentStage = SystemFunctions::get()->getSetupStep();
            switch ($currentStage){
                case SystemFunctions::$SETUP_STAGES['w']:{
                    require_once ROOT_DIR.'/pages/setup/welcome.php';
                    break;
                }
                case SystemFunctions::$SETUP_STAGES['db']:{
                    require_once ROOT_DIR.'/pages/setup/database-setup.php';
                    break;
                }
                case SystemFunctions::$SETUP_STAGES['admin']:{
                    require_once ROOT_DIR.'/pages/setup/admin-account.php';
                    break;
                }
                case SystemFunctions::$SETUP_STAGES['smtp']:{
                    require_once ROOT_DIR.'/pages/setup/email-account.php';
                    break;
                }
                case SystemFunctions::$SETUP_STAGES['website']:{
                    require_once ROOT_DIR.'/pages/setup/website-config.php';
                    break;
                }
            }
        }
        else{
            header('location: '.SiteConfig::get()->getBaseURL());
        }
    }, Router::FUNCTION_ROUTE);
    
    //Creating API Routes
    $router->addRoute('/SysAPIs/{action}', 'SysAPIs.php', Router::API_ROUTE);
    $router->addRoute('/AuthAPI/{action}', 'AuthAPI.php', Router::API_ROUTE);
    $router->addRoute('/ExampleAPI/{action}', 'ExampleAPI.php', Router::API_ROUTE);
    $router->addRoute('/NumsAPIs/{action}', 'NumsAPIs.php', Router::API_ROUTE);
    $router->addRoute('/PasswordAPIs/{action}', 'PasswordAPIs.php', Router::API_ROUTE);
    $router->addRoute('/UserAPIs/{action}', 'UserAPIs.php', Router::API_ROUTE);
    $router->addRoute('/WebsiteAPIs/{action}', 'WebsiteAPIs.php', Router::API_ROUTE);
    
    //other views
    //add your own or remove existing ones
    $router->addRoute('/login', 'login.php', Router::VIEW_ROUTE);
    $router->addRoute('/home', 'home.php', Router::VIEW_ROUTE);
    $router->addRoute('/activate-account', 'acctivate-account.php', Router::VIEW_ROUTE);
    $router->addRoute('/logout', 'logout.php', Router::VIEW_ROUTE);
    $router->addRoute('/new-password', 'new-password.php', Router::VIEW_ROUTE);
    $router->addRoute('/alyaseen-home', 'alyaseen-home.php', Router::VIEW_ROUTE);
}