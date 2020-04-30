<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
use phpStructs\html\Input;
use phpStructs\html\JsCode;
use phpStructs\html\Label;
use webfiori\entity\Page;
use webfiori\WebFiori;
/**
 * Description of CronLoginView
 *
 * @author Ibrahim
 */
class CronLoginView extends CronView{
    public function __construct() {
        parent::__construct('CRON Login', 'Login to CRON Control panel.');
        if (WebFiori::getWebsiteController()->getSessionVar('cron-login-status')) {
            header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron/jobs');
        }
        $form = new HTMLNode('form');
        Page::insert($form);
        $label = new Label('Enter Login Password:');
        $label->setStyle([
            'display' => 'block',
            'font-weight' => 'bold'
        ]);
        $label->setAttribute('for', 'password-input');
        $form->addChild($label);
        $passInput = new Input('password');
        $passInput->setAttribute('placeholder', 'Enter CRON password here.');
        $passInput->setID('password-input');
        $passInput->setStyle([
            'width' => '200px'
        ]);
        $form->addChild($passInput);
        $form->addTextNode('<br/><br/>', false);
        $submit = new HTMLNode('button');
        $submit->addTextNode('Login');
        $submit->setAttribute('onclick', 'login(this);return false;');
        $submit->setID('submit-button');
        $form->addChild($submit);
        Page::render();
    }
}
return __NAMESPACE__;
