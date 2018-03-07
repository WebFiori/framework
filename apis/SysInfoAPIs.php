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
 * An API used to get or information about the system.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class SysInfoAPIs extends API{
    public function __construct() {
        parent::__construct();
        $this->setVirsion('1.0.0');
        $a1 = new APIAction();
        $a1->setActionMethod('GET');
        $a1->setName('get-template-info');
        $this->addAction($a1);
        $a2 = new APIAction();
        $a2->setActionMethod('GET');
        $a2->setName('get-site-info');
        $this->addAction($a2);
        $a3 = new APIAction();
        $a3->setActionMethod('GET');
        $a3->setName('get-sys-version');
        $this->addAction($a3);
    } 
    
    public function processAction(){
        $action = $this->getAction();
        if($action == 'get-sys-version'){
            $json = new JsonX();
            $json->add('version', Config::get()->getSysVersion());
            $json->add('version-type', Config::get()->getVerType());
            $this->sendResponse('Server version Information', FALSE, 200, '"info":'.$json);
        }
        else if($action == 'get-template-info'){
            $json = new JsonX();
            $json->add('version', Config::get()->getTemplateVersion());
            $json->add('version-type', Config::get()->getTemplateVersionType());
            $json->add('template-date', Config::get()->getTemplateDate());
            $this->sendResponse('Server PHP Template Information', FALSE, 200, '"info":'.$json);
        }
        else if($action == 'get-site-info'){
            $json = new JsonX();
            $json->add('name', SiteConfig::get()->getWebsiteName());
            $json->add('description', SiteConfig::get()->getDesc());
            $json->add('base-url', SiteConfig::get()->getBaseURL());
            $json->add('home-page', SiteConfig::get()->getHomePage());
            $json->add('copyright-notice', SiteConfig::get()->getCopyright());
            $json->add('title-sep', SiteConfig::get()->getTitleSep());
            $this->sendResponse('Website Information', FALSE, 200, '"info":'.$json);
        }
    }
}
$SysAPIs = new SysInfoAPIs();
$SysAPIs->process('processAction');
