<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\scheduler\webUI;

use webfiori\framework\scheduler\TasksManager;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\ui\WebPage;
use webfiori\framework\App;
use webfiori\http\Response;
use webfiori\json\Json;
use webfiori\ui\exceptions\InvalidNodeNameException;
use webfiori\ui\HTMLNode;
use webfiori\ui\JsCode;
/**
 * A generic view for scheduler related operations. 
 * 
 * It can be extended to create a page which is used to 
 * perform some operations on scheduled tasks.
 *
 * @author Ibrahim
 */
class BaseTasksPage extends WebPage {
    private $jsonData;
    public function __construct($title, $description = '') {
        parent::__construct();
        $this->jsonData = new Json([
            'title' => $title,
            'base' => $this->getBase()
        ]);
        $loginPageTitle = 'Tasks Scheduler Login';
        
        SessionsManager::start('scheduler-session');

        if (TasksManager::password() != 'NO_PASSWORD' 
                && $title != $loginPageTitle
                && SessionsManager::getActiveSession()->get('scheduler-login-status') !== true) {
            Response::addHeader('location', 'scheduler/login');
            die('gg');
            Response::send();
        } else if ($title == $loginPageTitle && TasksManager::password() == 'NO_PASSWORD') {
            Response::addHeader('location', 'scheduler/tasks');
            Response::send();
        }
        $this->setTitle($title);
        $this->setDescription($description);
        $defaultSiteLang = App::getAppConfig()->getPrimaryLanguage();
        $siteNames = App::getAppConfig()->getWebsiteNames();
        $siteName = $siteNames[$defaultSiteLang] ?? null;

        if ($siteName !== null) {
            $this->setWebsiteName($siteName);
        }
        $this->changePageStructure();
        $this->iniHead();

        $row = $this->insert('v-row');
        $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('h1')->text($title);

        if (TasksManager::password() != 'NO_PASSWORD' && $title != $loginPageTitle) {
            $row = $this->insert('v-row');
            $row->addChild('v-col', [
                'cols' => 12
            ])->addChild('v-btn', [
                '@click' => 'logout',
                'color' => 'primary',
                ':loading' => 'loading'
            ])->text('Logout');
        }
        $this->insert($this->include('job-execution-status-dialog.html'));
        $this->insert($this->include('job-output-dialog.html'));
        $this->addBeforeRender(function (BaseTasksPage $view)
        {
            $code = new JsCode();
            $code->addCode('window.data = '.$view->getJson().';');
            $view->getDocument()->getHeadNode()->addChild($code);
        }, 1000);
    }

   
    /**
     * 
     * @return Json
     */
    public function getJson() {
        return $this->jsonData;
    }
    private function changePageStructure() {
        $this->addBeforeRender(function (WebPage $page)
        {
            $appDiv = new HTMLNode('div', [
                'id' => 'app'
            ]);
            $vApp = new HTMLNode('v-app');
            $appDiv->addChild($vApp);
            $appDiv->addChild($appDiv);
            $body = $page->getChildByID('page-body');
            $body->setNodeName('v-main');

            $header = $page->getChildByID('page-header');
            $footer = $page->getChildByID('page-footer');
            $vApp->addChild($header);
            $vApp->addChild($body);
            $sideMenu = $body->getChildByID('side-content-area');
            $body->removeChild($sideMenu);
            $vApp->addChild($sideMenu);
            $vApp->addChild($footer);
            $page->getDocument()->removeChild($header);
            $page->getDocument()->removeChild($body);
            $page->getDocument()->removeChild($footer);
            $page->getDocument()->addChild($appDiv);
            $page->getDocument()->getChildByID('main-content-area')->setClassName('container');
        }, 100);
        $this->addBeforeRender(function (WebPage $page)
        {
            $page->getDocument()->getBody()->addChild('script', [
                'type' => 'text/javascript',
                'src' => $page->getBase().'/assets/js/scheduler-logic.js',
            ]);
        });
    }
    

    private function iniHead() {
        $this->getDocument()->setHeadNode($this->include('head.php'));
    }
}
