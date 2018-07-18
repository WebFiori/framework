<?php

/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * A class that only has a function to create closure routes.
 *
 * @author Ibrahim
 * @version 1.0
 */
class ClosureRoutes {
    /**
     * Create all closure routes. Include your own here.
     * @since 1.0
     */
    public static function create() {
        Router::closure('/sitemap', function(){
            $viewsSiteMap = ViewRoutes::createSiteMap();
            header('content-type: text/xml');
            echo $viewsSiteMap;
        });
        Router::closure('/'.Util::NEED_CONF, function(){
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
        });
    }
}
