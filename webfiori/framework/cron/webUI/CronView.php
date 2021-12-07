<?php
/*
 * The MIT License
 *
 * Copyright 2020, WebFiori Framework.
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
namespace webfiori\framework\cron\webUI;

use webfiori\framework\cron\Cron;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\ui\WebPage;
use webfiori\framework\WebFioriApp;
use webfiori\http\Response;
use webfiori\json\Json;
use webfiori\ui\HTMLNode;
use webfiori\ui\JsCode;
/**
 * A generic view for cron related operations. 
 * 
 * It can be extended to create a view which is used to 
 * perform some operations on cron jobs.
 *
 * @author Ibrahim
 */
class CronView extends WebPage {
    private $jsonData;
    public function __construct($title, $description = '') {
        parent::__construct();
        $this->jsonData = new Json([
            'title' => $title,
            'base' => $this->getBase()
        ]);
        $loginPageTitle = 'CRON Web Interface Login';
        SessionsManager::start('cron-session');

        if (Cron::password() != 'NO_PASSWORD' 
                && $title != $loginPageTitle
                && SessionsManager::getActiveSession()->get('cron-login-status') !== true) {
            Response::addHeader('location', WebFioriApp::getAppConfig()->getBaseURL().'/cron/login');
            Response::send();
        } else {
            if ($title == $loginPageTitle && Cron::password() == 'NO_PASSWORD') {
                Response::addHeader('location', WebFioriApp::getAppConfig()->getBaseURL().'/cron/jobs');
                Response::send();
            }
        }
        $this->setTitle($title);
        $this->setDescription($description);
        $defaltSiteLang = WebFioriApp::getAppConfig()->getPrimaryLanguage();
        $siteNames = WebFioriApp::getAppConfig()->getWebsiteNames();
        $siteName = isset($siteNames[$defaltSiteLang]) ? $siteNames[$defaltSiteLang] : null;

        if ($siteName !== null) {
            $this->setWebsiteName($siteName);
        }
        $this->changePageStructure();
        $this->iniHead();

        $row = $this->insert('v-row');
        $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('h1')->text($title);

        if (Cron::password() != 'NO_PASSWORD' && $title != $loginPageTitle) {
            $row = $this->insert('v-row');
            $row->addChild('v-col', [
                'cols' => 12
            ])->addChild('v-btn', [
                '@click' => 'logout',
                'color' => 'primary',
                ':loading' => 'loading'
            ])->text('Logout');
        }
        $this->createVDialog('dialog.show', 'dialog.title', 'dialog.message', 'dialogClosed');
        $this->createOutputDialog();
        $this->addBeforeRender(function (CronView $view)
        {
            $code = new JsCode();
            $code->addCode('window.data = '.$view->getJson().';');
            $view->getDocument()->getHeadNode()->addChild($code);
        });
    }
    /**
     * Adds a very basic v-dialog that can be used to show status messages and so on.
     * 
     * @param string $model The vue model which is used to make the dialig visible.
     * 
     * @param string $titleModel A string that represents the model which is used 
     * to set the title.
     * 
     * @param string $messageModel The name of the model that will hold dialog
     * message.
     * 
     * @param string $closeAction The name of the method which will be invoked 
     * when close button is clicked.
     * 
     * @param array $iconProps An optional array that holds icon props. the 
     * array can have two indices: 'model' and 'color-model'.
     */
    public function createVDialog($model, $titleModel, $messageModel, $closeAction, $iconProps = []) {
        $dialog = new HTMLNode('v-dialog');
        $dialog->setAttribute('v-model', $model);

        $dialog->setAttributes([
            'v-model' => $model,
            'width' => '500'
        ]);

        $dialogCard = $dialog->addChild('v-card');

        $iconModel = isset($iconProps['model']) ? '{{ '.$iconProps['model'].' }}' : 'mdi-information';
        $propsArr = [
            'style' => [
                'margin' => '10px'
            ],
        ];
        isset($iconProps['color-model']) ? $propsArr[':color'] = $iconProps['color-model'] : $propsArr['color'] = 'green';
        $dialogCard->addChild('v-card-title', [
            'class' => ""
        ])->addChild('v-icon', $propsArr)->text($iconModel)
        ->getParent()->addChild('div', [
            'style' => [
                'display' => 'inline'
            ]
        ])->text("{{ $titleModel }}");
        $dialogCard->addChild('v-divider');
        $dialogCard->addChild('v-card-text')->text("{{ $messageModel }}");
        $dialogCard->addChild('v-divider');
        $dialogActions = $dialogCard->addChild('v-card-actions');
        $dialogActions->addChild('v-spacer');
        $dialogActions->addChild('v-btn', [
            'color' => "primary",
            'text',
            '@click' => "$closeAction"
        ])->text('Close');
        $dialogActions->addChild('v-btn', [
            'color' => "primary",
            'text',
            '@click' => "output_dialog.show = true"
        ])->text('View Job Output');
        $this->insert($dialog);
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
        });
        $this->addBeforeRender(function (WebPage $page)
        {
            $page->getDocument()->getBody()->addChild('script', [
                'type' => 'text/javascript',
                'src' => 'https://cdn.jsdelivr.net/gh/webfiori/app@'.WF_VERSION.'/public/assets/js/cron.js',
            ]);
        });
    }
    private function createOutputDialog() {
        $dialog = $this->insert('v-dialog');
        $dialog->setAttributes([
            'v-model' => 'output_dialog.show',
            'max-width' => '850px',
            'scrollable'
        ]);
        $card = $dialog->addChild('v-card');
        $card->addChild('v-card-title')->text('Job Execution Output');
        $card->addChild('v-divider');
        $card->addChild('v-card-text', [
            'style' => [
                "height" => '400px;',
                'color' => 'white',
                'background-color' => 'black',
                'text-align' => 'justify'
            ],
            'v-html' => 'output_dialog.output'
        ]);
        $card->addChild('v-divider');
        $card->addChild('v-card-actions')
            ->addChild('v-btn', [
                'color' => "primary",
                'text',
                '@click' => "output_dialog.show = false",
            ])->text('Close');
    }

    private function iniHead() {
        $this->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        $this->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css');
        $this->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');
        $this->addJS('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js');
        $this->addJS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js');
        $this->addJS('https://cdn.jsdelivr.net/gh/usernane/AJAXRequestJs@1.x.x/AJAXRequest.js', [], false);
    }
}
