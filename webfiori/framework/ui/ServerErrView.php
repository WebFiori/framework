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
namespace webfiori\framework\ui;

use Throwable;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\Util;
use webfiori\framework\WebFioriApp;
use webfiori\http\Response;
use webfiori\ui\HTMLNode;
use webfiori\error\AbstractHandler;
use webfiori\framework\ui\WebPage;
/**
 * A page which is used to display exception information when it is thrown or 
 * any other errors.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class ServerErrView extends WebPage {
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
        $this->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',[], false);
        $this->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css', [], false);
        $this->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css', [], false);
        $this->addJs('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', [], false);
        $this->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', [], false);
        $this->addBeforeRender(function (WebPage $p) {
            $p->getDocument()->getBody()->addChild('script', [
                'src' => 'https://cdn.jsdelivr.net/gh/webfiori/app@'.WF_VERSION.'/public/assets/js/server-err.js',
                'type' => 'text/javascript'
            ]);
        });
        $container = $this->insert('v-container');
        $row = $container->addChild('v-row');
        $hNode = $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-alert');
        $hNode->setAttributes([
            'prominent',
            'type' => "error"
        ]);
        if (defined('WF_VERBOSE') && WF_VERBOSE === true) {
            $hNode->addChild('v-row', [
                'align' => "center"
            ])->addChild('v-col')->text('500 - Server Error: Uncaught Exception.');
            $card = $row->addChild('v-col', [
                'cols' => 12
            ])->addChild('v-card');
            $card->addChild('v-card-title')->text('Exception Details');
            $detailsList = $card->addChild('v-card-text')->addChild('v-list');
            $this->addDetails($detailsList, 'Exception Message', $throwableOrErr->getMessage());
            $this->addDetails($detailsList, 'Exception Class', get_class($throwableOrErr->getException()));
            $this->addDetails($detailsList, 'At Class', $throwableOrErr->getClass());
            $this->addDetails($detailsList, 'Line', $throwableOrErr->getLine());

            $traceCard = $row->addChild('v-col', [
                'cols' => 12
            ])->addChild('v-card');
            $traceCard->addChild('v-card-title')->text('Stack Trace');
            $traceList = $traceCard->addChild('v-card-text')->addChild('v-list', [
                'dense'
            ]);
            $index = 0;
            foreach ($throwableOrErr->getTrace() as $traceEntry) {
                $traceList->addChild('v-list-item')->addChild('v-list-title')->text('#'.$index.' '.$traceEntry.'');
                $index++;
            }
        } else {
            $hNode->addChild('v-row', [
                'align' => "center"
            ])->addChild('v-col')->text('500 - General Server Error.');
        }
    }
    private function addDetails(HTMLNode $list, $title, $message) {
        $item = $list->addChild('v-list-item')->addChild('v-list-item-content');
        $item->addChild('v-list-item-title')->text($title);
        $item->addChild('v-list-item-subtitle')->text($message);
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
