<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
/**
 * Description of WebsiteAPIs
 *
 * @author Ibrahim
 */
class WebsiteAPIs extends API{
    public function __construct() {
        parent::__construct();
        
        $a2 = new APIAction('update-site-info');
        $a2->setDescription('Updates the basic settings of the website.');
        $a2->addRequestMethod('post');
        $a2->addParameter(new RequestParameter('title-sep', 'string', TRUE));
        $a2->getParameterByName('title-sep')->setDescription('Optional parameter. '
                . 'The value of this parameter can be acharacter or a string. It is simply '
                . 'a value that is used to seperate the name of a web page and '
                . 'the name of the website inside the tag \'title\'.');
        $a2->addParameter(new RequestParameter('home-page', 'string',TRUE));
        $a2->getParameterByName('home-page')->setDescription('Optional parameter. The '
                . 'value of this attribute must be a path to the home page. '
                . 'If the given page does not exists, the API will responde with a message '
                . 'that says this parameter is invalid.');
        $a2->getParameterByName('home-page')->setCustomFilterFunction(function($val){
            $homePage = $val['basic-filter-result'];
            $router = Router::get();
            if($router->hasRoute($homePage)){
                return $homePage;
            }
            return FALSE;
        });
        $a2->addParameter(new RequestParameter('site-theme', 'string',TRUE));
        $a2->getParameterByName('site-theme')->setDescription('Optional parameter. The '
                . 'value of this attribute is the name of the theme that is used '
                . 'across website pages. If the given theme does not exists, Website '
                . 'information won\'t update.');
        $a2->addParameter(new RequestParameter('main-language', 'string',TRUE));
        $a2->getParameterByName('main-language')->setDescription('Two characters that represents language code. '
                . 'It is the unique identifier for the language.');
        $a2->getParameterByName('main-language')->setCustomFilterFunction(function($val){
            $langCode = $val['basic-filter-result'];
            if(strlen($langCode) == 2){
                return $langCode;
            }
            return FALSE;
        });
        $this->addAction($a2, TRUE);
        
        $a3 = new APIAction();
        $a3->setName('get-website-info');
        $a3->setDescription('Returns all general website information.');
        $a3->addRequestMethod('get');
        $this->addAction($a3);
        
        $this->setVersion('1.0.1');
        $a4 = new APIAction('add-website');
        $a4->setDescription('Adds new website name and website description.');
        $a4->addRequestMethod('post');
        $a4->addParameter(new RequestParameter('site-name', 'string'));
        $a4->getParameterByName('site-name')->setDescription('The name of the website in '
                . 'the language that will be added.');
        $a4->addParameter(new RequestParameter('language', 'string'));
        $a4->getParameterByName('language')->setDescription('Two characters that represents language code. '
                . 'It is the unique identifier for the language.');
        $a4->getParameterByName('language')->setCustomFilterFunction(function($val){
            $langCode = $val['basic-filter-result'];
            if(strlen($langCode) == 2){
                return $langCode;
            }
            return FALSE;
        });
        $a4->addParameter(new RequestParameter('site-description', 'string'));
        $a4->getParameterByName('site-description')->setDescription('A general description for the website in '
                . 'the language that will be added.');
        $this->addAction($a4, TRUE);
        
        $a5 = new APIAction('update-website');
        $a5->addRequestMethod('post');
        $a5->addParameter(new RequestParameter('site-name', 'string'));
        $a5->getParameterByName('site-name')->setDescription('The name of the website in '
                . 'the language that will be added.');
        $a5->addParameter(new RequestParameter('language', 'string'));
        $a5->getParameterByName('language')->setDescription('Two characters that represents language code. '
                . 'It is the unique identifier for the language.');
        $a5->getParameterByName('language')->setCustomFilterFunction(function($val){
            $langCode = $val['basic-filter-result'];
            if(strlen($langCode) == 2){
                return $langCode;
            }
            return FALSE;
        });
        $a5->addParameter(new RequestParameter('site-description', 'string'));
        $a5->getParameterByName('site-description')->setDescription('A general description for the website in '
                . 'the language that will be updated.');
        $this->addAction($a5, TRUE);
        
        $a6 = new APIAction('remove-website');
        $a6->setDescription('Removes the name of a website and its description given its language code.');
        $a6->addRequestMethod('delete');
        $a6->addParameter(new RequestParameter('language', 'string'));
        $a6->getParameterByName('language')->setCustomFilterFunction(function($val){
            $langCode = $val['basic-filter-result'];
            if(strlen($langCode) == 2){
                return $langCode;
            }
            return FALSE;
        });
        $a6->getParameterByName('language')->setDescription('Two digit language code.');
        $this->addAction($a6, TRUE);
        
        $a7 = new APIAction('get-available-themes');
        $a7->setDescription('Returns all the information of installed themes.');
        $a7->addRequestMethod('get');
        $this->addAction($a7, TRUE);
    }
    public function isAuthorized() {
        return WebsiteFunctions::get()->getAccessLevel() == 0;
    }

    public function processRequest() {
        $action = $this->getAction();
        if($action == 'get-website-info'){
            $j = new JsonX();
            $j->addArray('website', WebsiteFunctions::get()->getSiteConfigVars());
            $this->send('application/json', $j);
        }
        else if($action == 'update-site-info'){
            $this->updateSiteInfo();
        }
        else if($action == 'add-website'){
            $i = $this->getInputs();
            $siteInfoArr = WebsiteFunctions::get()->getSiteConfigVars();
            $langCode = strtoupper($i['language']);
            if(isset($siteInfoArr['website-names'][$langCode])){
                $this->sendResponse('Already Added', FALSE, 200);
            }
            else{
                $websiteName = $i['site-name'];
                $websiteDesc = $i['site-description'];
                $siteInfoArr['website-names'][$langCode] = $websiteName;
                $siteInfoArr['site-descriptions'][$langCode] = $websiteDesc;
                WebsiteFunctions::get()->updateSiteInfo($siteInfoArr);
                $this->sendResponse('New Website Language Added.', FALSE, 201);
            }
        }
        else if($action == 'update-website'){
            $i = $this->getInputs();
            $siteInfoArr = WebsiteFunctions::get()->getSiteConfigVars();
            $langCode = strtoupper($i['language']);
            if(isset($siteInfoArr['website-names'][$langCode])){
                $websiteName = $i['site-name'];
                $websiteDesc = $i['site-description'];
                $siteInfoArr['website-names'][$langCode] = $websiteName;
                $siteInfoArr['site-descriptions'][$langCode] = $websiteDesc;
                WebsiteFunctions::get()->updateSiteInfo($siteInfoArr);
                $this->sendResponse('Updated', FALSE, 200);
            }
            else{
                $this->sendResponse('No such website language: '.$langCode, TRUE, 404);
            }
        }
        else if($action == 'remove-website'){
            $i = $this->getInputs();
            $siteInfoArr = WebsiteFunctions::get()->getSiteConfigVars();
            $langCode = strtoupper($i['language']);
            if(isset($siteInfoArr['website-names'][$langCode])){
                unset($siteInfoArr['website-names'][$langCode]);
                unset($siteInfoArr['site-descriptions'][$langCode]);
                WebsiteFunctions::get()->updateSiteInfo($siteInfoArr);
                $this->sendResponse('Removed', FALSE, 200);
            }
            else{
                $this->sendResponse('No such website language: '.$langCode, TRUE, 404);
            }
        }
        else if($action == 'get-available-themes'){
            $themes = Theme::getAvailableThemes();
            $j = new JsonX();
            $j->addArray('themes', $themes);
            $this->send('application/json', $j);
        }
    }
    /**
     * A function that is used to update website related info.
     * @since 1.0
     */
    private function updateSiteInfo() {
        $i = $this->getInputs();
        
        $cfgArr = WebsiteFunctions::get()->getSiteConfigVars();
        if(isset($i['title-sep'])){
            $cfgArr['title-separator'] = $i['title-sep'];
        }
        if(isset($i['home-page'])){
            $cfgArr['home-page'] = $i['home-page'];
        }
        if(isset($i['main-language'])){
            $cfgArr['primary-language'] = strtoupper($i['main-language']);
        }
        if(isset($i['site-theme'])){
            try{
                Theme::usingTheme($i['site-theme']);
                $cfgArr['theme-name'] = $i['site-theme'];
                WebsiteFunctions::get()->updateSiteInfo($cfgArr);
                $this->sendResponse('Site info updated.');
            } catch (Exception $ex) {
                $this->sendResponse('No theme was found which has the given name.', TRUE, 404);
            }
        }
        else{
            WebsiteFunctions::get()->updateSiteInfo($cfgArr);
            $this->sendResponse('Site info updated.');
        }
            
    }

}
$api = new WebsiteAPIs();
$api->process();