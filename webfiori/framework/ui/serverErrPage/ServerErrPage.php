<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2023 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\ui\serverErrPage;

use webfiori\error\AbstractHandler;
use webfiori\framework\ui\WebPage;
use webfiori\ui\HTMLNode;
/**
 * A page which is used to display exception information when it is thrown or 
 * any other errors.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class ServerErrPage extends WebPage {
    /**
     * Creates a new instance of the class.
     * 
     * @param AbstractHandler $throwableOrErr The handler which is
     * used to handle exceptions.
     * 
     * @since 1.0
     */
    public function __construct(AbstractHandler $throwableOrErr) {
        parent::__construct();

        $this->setTitle('Server Error');
        $this->changeDom();
        $this->getDocument()->setHeadNode($this->include('head.php', [
            'throwableOrErr' => $throwableOrErr
        ]));

        $this->addBeforeRender(function (WebPage $p)
        {
            $p->getDocument()->getBody()->addChild('script', [
                'src' => 'https://cdn.jsdelivr.net/gh/webfiori/framework@'.WF_VERSION.'/assets/js/server-err.js',
                'type' => 'text/javascript'
            ]);
        });
        $container = $this->insert('v-container');
        $row = $container->addChild('v-row');
        
        $templateVars = [
            'throwableOrErr' => $throwableOrErr
        ];
        $row->include('header.php', $templateVars);
        $row->include('error-details.php', $templateVars);
        $row->include('stack-trace.php', $templateVars);
    }
    
    private function changeDom() {
        $topDiv = new HTMLNode('v-app');
        $topDiv->setID('app');
        $headerSec = $this->getChildByID('page-header');
        $this->getDocument()->removeChild($headerSec);
        $bodySec = $this->getChildByID('page-body');
        $this->getDocument()->removeChild($bodySec);
        $footerSec = $this->getChildByID('page-footer');
        $this->getDocument()->removeChild($footerSec);
        $topDiv->addChild($footerSec)->addChild($headerSec)->addChild($bodySec);
        $this->getDocument()->getBody()->addChild($topDiv);
        $this->getDocument()->getChildByID('main-content-area')->setNodeName('v-main');
        $this->getDocument()->getChildByID('main-content-area')->setAttribute('app');
    }
}
