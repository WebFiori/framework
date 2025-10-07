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

use webfiori\framework\App;
use webfiori\framework\ui\WebPage;
use WebFiori\Json\Json;
use WebFiori\UI\HTMLNode;
use WebFiori\UI\JsCode;
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
            'base' => $this->getBase(),
            'session' => $this->getActiveSession()
        ]);



        $this->setTitle($title);
        $this->setDescription($description);
        $defaultSiteLang = App::getConfig()->getPrimaryLanguage();
        $siteNames = App::getConfig()->getAppNames();
        $siteName = $siteNames[$defaultSiteLang] ?? null;

        if ($siteName !== null) {
            $this->setWebsiteName($siteName);
        }
        $this->changePageStructure();
        $this->getDocument()->setHeadNode($this->include('templates/scheduler-head.php'));

        $row = $this->insert('v-row');
        $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('h1')->text($title);



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
    public function getJson() : Json {
        return $this->jsonData;
    }
    /**
     * Checks if the user is logged in or not.
     *
     * The method will check if session variable 'scheduler-is-logged-in' is
     * set to true or not. The variable is set when the user successfully
     * logged in.
     *
     * @return bool
     */
    public function isLoggedIn() : bool {
        return $this->getActiveSession()->get('scheduler-is-logged-in') === true;
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
                'src' => 'https://cdn.jsdelivr.net/gh/webfiori/framework@'.WF_VERSION.'/assets/js/scheduler-logic.js',
            ]);
        });
    }
}
