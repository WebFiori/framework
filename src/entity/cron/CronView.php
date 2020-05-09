<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\cron;

use phpStructs\html\HTMLNode;
use phpStructs\html\JsCode;
use webfiori\entity\Page;
use webfiori\WebFiori;
/**
 * A generic view for cron related operations. 
 * 
 * It can be extended to create a view which is used to 
 * perform some operations on cron jobs.
 *
 * @author Ibrahim
 */
class CronView {
    /**
     * A top container that contains all task related controls.
     * @var HTMLNode 
     */
    private $controlsContainer;
    public function __construct($title,$description = '') {
        if (Cron::password() != 'NO_PASSWORD' 
                && $title != 'CRON Login' 
                && WebFiori::getWebsiteController()->getSessionVar('cron-login-status') !== true) {
            header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron/login');
        } else if ($title == 'CRON Login' && Cron::password() == 'NO_PASSWORD') {
            header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron/jobs');
        }
        Page::title($title);
        Page::description($description);
        $defaltSiteLang = WebFiori::getSiteConfig()->getPrimaryLanguage();
        $siteNames = WebFiori::getSiteConfig()->getWebsiteNames();
        $siteName = isset($siteNames[$defaltSiteLang]) ? $siteNames[$defaltSiteLang] : null;

        if ($siteName !== null) {
            Page::siteName($siteName);
        }
        $this->controlsContainer = new HTMLNode();
        $this->controlsContainer->setWritingDir('ltr');
        $this->controlsContainer->setStyle([
            'direction' => 'ltr',
            'width' => '100%',
            'float' => 'left'
        ]);

        $h1 = new HTMLNode('h1');
        $h1->addTextNode($title);
        Page::insert($h1);
        $hr = new HTMLNode('hr',false);
        Page::insert($hr);

        if (Cron::password() != 'NO_PASSWORD' && $title != 'CRON Login') {
            $this->controlsContainer->addTextNode('<button name="input-element" onclick="logout()"><b>Logout</b></button><br/>', false);
        }
        $jsCode = new JsCode();
        $isRefresh = 'false';

        if (isset($_GET['refresh'])) {
            $isRefresh = 'true';
        }
        $jsCode->addCode(''
                .'window.onload = function(){'."\n"
                .'     window.isRefresh = '.$isRefresh.';'."\n"
                ."     "
                .'     window.intervalId = window.setInterval(function(){'."\n"
                .'         if(window.isRefresh){'."\n"
                .'             disableOrEnableInputs();'."\n"
                .'             document.getElementById(\'refresh-label\').innerHTML = \'<b>Refreshing...</b>\';'."\n"
                .'             window.location.href = \'cron/jobs?refresh=yes\';'."\n"
                .'         }'."\n"
                .'     },60000)'."\n"
                .' };'."\n"
                );
        Page::document()->getHeadNode()->addJs('https://cdn.jsdelivr.net/gh/usernane/ajax@1.0.2/AJAX.js', [], false);
        Page::document()->getHeadNode()->addJs('assets/js/cron.js');
        Page::document()->getHeadNode()->addCSS('assets/css/cron.css');
        Page::document()->getHeadNode()->addChild($jsCode);
        Page::insert($this->controlsContainer);
    }
    /**
     * Adds an area which is used to show server output.
     */
    public function createOutputWindow() {
        $outputWindow = new HTMLNode();
        $outputWindow->setID('output-window');
        $outputWindow->addTextNode('<p class="output-window-title">Output Window</p><pre'
                .' id="output-area"></pre>', false);
        Page::insert($outputWindow);
    }
    /**
     * 
     * @return HTMLNode
     */
    public function getControlsContainer() {
        return $this->controlsContainer;
    }
}
