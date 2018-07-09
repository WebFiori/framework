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
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * Description of WebsiteAPIs
 *
 * @author Ibrahim
 */
class WebsiteAPIs extends API{
    public function __construct() {
        parent::__construct();
        
        $a1 = new APIAction();
        $a1->setName('update-website-name');
        $a1->addRequestMethod('post');
        $a1->addParameter(new RequestParameter('name', 'string'));
        $this->addAction($a1, TRUE);
        
        $a2 = new APIAction();
        $a2->setName('update-site-info');
        $a2->setDescription('Updates general website information.');
        $a2->addRequestMethod('post');
        $a2->addParameter(new RequestParameter('site-name', 'string',TRUE));
        $a2->addParameter(new RequestParameter('site-description', 'string',TRUE));
        $a2->addParameter(new RequestParameter('title-sep', 'string', TRUE));
        $a2->addParameter(new RequestParameter('home-page', 'string',TRUE));
        $a2->addParameter(new RequestParameter('site-theme', 'string',TRUE));
        $this->addAction($a2, TRUE);
        
        $a3 = new APIAction();
        $a3->setName('get-website-info');
        $a3->addRequestMethod('get');
        $this->addAction($a3);
        
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
    }
    /**
     * A function that is used to update website related info.
     * @since 1.0
     */
    private function updateSiteInfo() {
        $i = $this->getInputs();
        $cfgArr = array(
            'website-name'=>$i['site-name'],
            'description'=>$i['site-description']
        );
        if(isset($i['title-sep'])){
            $cfgArr['title-separator'] = $i['title-sep'];
        }
        if(isset($i['home-page'])){
            $cfgArr['home-page'] = $i['home-page'];
        }
        if(isset($i['site-theme'])){
            $cfgArr['theme-name'] = $i['site-theme'];
        }
        WebsiteFunctions::get()->updateSiteInfo($cfgArr);
        $this->sendResponse('Site info updated.');
            
    }

}
$api = new WebsiteAPIs();
$api->process();