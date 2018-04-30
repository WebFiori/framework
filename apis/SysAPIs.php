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
class SysAPIs extends API{
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0.1');
        $a1 = new APIAction();
        $a1->addRequestMethod('GET');
        $a1->setName('get-template-info');
        $this->addAction($a1,TRUE);
        
        $a2 = new APIAction();
        $a2->addRequestMethod('GET');
        $a2->setName('get-site-info');
        $this->addAction($a2,TRUE);
        
        $a3 = new APIAction();
        $a3->addRequestMethod('GET');
        $a3->setName('get-sys-version');
        $this->addAction($a3,TRUE);
        
        $a4 = new APIAction();
        $a4->setName('get-main-session');
        $a4->addRequestMethod('get');
        $this->addAction($a4,TRUE);
        
        $a5 = new APIAction();
        $a5->setName('update-database-attributes');
        $a5->addRequestMethod('post');
        $a5->addParameter(new RequestParameter('host', 'string'));
        $a5->addParameter(new RequestParameter('database-username', 'string'));
        $a5->addParameter(new RequestParameter('database-password', 'string'));
        $a5->addParameter(new RequestParameter('database-name', 'string'));
        $this->addAction($a5, TRUE);
        
        $a6 = new APIAction();
        $a6->setName('get-email-accounts');
        $a6->addRequestMethod('get');
        $this->addAction($a6, TRUE);

    } 
    
    public function processRequest(){
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
        else if($action == 'get-main-session'){
            $this->sendResponse('Main Session Info', FALSE, 200, '"session":'.SystemFunctions::get()->getMainSession()->toJSON());
        }
        else if($action == 'update-database-attributes'){
            $this->actionNotImpl();
        }
        else if($action == 'get-email-accounts'){
            $j = new JsonX();
            $accountNum = 0;
            $accountsKeys = array_keys(MailConfig::get()->getAccounts());
            foreach ($accountsKeys as $key){
                $account = MailConfig::get()->getAccount($key);
                $jAcc = new JsonX();
                $jAcc->add('address', $account->getAddress());
                $jAcc->add('name', $account->getName());
                $j->add('account-'+$accountNum, $jAcc);
                $accountNum++;
            }
            $this->sendResponse('Email Accounts', FALSE, 200, '"accounts":'.$j);
        }
    }

    public function isAuthorized() {
        return SystemFunctions::get()->getAccessLevel() == 0;
    }
}
$SysAPIs = new SysAPIs();
$SysAPIs->process();
