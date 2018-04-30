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
require_once '../root.php';
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
        $a2->setName('update-title-sep');
        $a2->addRequestMethod('post');
        $a2->addParameter(new RequestParameter('sep', 'string'));
        $this->addAction($a2, TRUE);
        
        $a3 = new APIAction();
        $a3->setName('update-home-page');
        $a3->addRequestMethod('post');
        $a3->addParameter(new RequestParameter('home-page', 'string'));
        $this->addAction($a3, TRUE);
        
        $a4 = new APIAction();
        $a4->setName('update-copyright-notice');
        $a4->addRequestMethod('post');
        $a4->addParameter(new RequestParameter('notice', 'string'));
        $this->addAction($a3, TRUE);
        
        $a5 = new APIAction();
        $a5->setName('update-website-description');
        $a5->addRequestMethod('post');
        $a5->addParameter(new RequestParameter('description', 'string'));
        $this->addAction($a5, TRUE);
        
        $a6 = new APIAction();
        $a6->setName('get-website-info');
        $a6->addRequestMethod('get');
        $this->addAction($a6);
        
        $a7 = new APIAction();
        $a7->setName('get-website-session-info');
        $a7->addRequestMethod('get');
        $this->addAction($a7);
    }
    public function isAuthorized() {
        return WebsiteFunctions::get()->getAccessLevel() == 0;
    }

    public function processRequest() {
        $action = $this->getAction();
        if($action == 'get-website-info'){
            $j = new JsonX();
            $j->add('website-name', SiteConfig::get()->getWebsiteName());
            $j->add('base-url', SiteConfig::get()->getBaseURL());
            $j->add('description', SiteConfig::get()->getDesc());
            $j->add('copyright-notice', SiteConfig::get()->getCopyright());
            $j->add('home-page', SiteConfig::get()->getHomePage());
            $j->add('title-sep', SiteConfig::get()->getTitleSep());
            $this->sendResponse('Website Information', FALSE, 200, '"info":'.$j);
        }
        else if($action == 'update-website-description'){
            $this->actionNotImpl();
        }
        else if($action == 'update-copyright-notice'){
            $this->actionNotImpl();
        }
        else if($action == 'update-home-page'){
            $this->actionNotImpl();
        }
        else if($action == 'update-title-sep'){
            $this->actionNotImpl();
        }
        else if($action == 'update-website-name'){
            $this->actionNotImpl();
        }
        else if($action == 'get-website-session-info'){
            $this->sendResponse('Main Session Info', FALSE, 200, '"session":'.PageAttributes::get()->getSession()->toJSON());
        }
    }

}
$api = new WebsiteAPIs();
$api->process();