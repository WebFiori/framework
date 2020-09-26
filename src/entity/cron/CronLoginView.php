<?php
/*
 * The MIT License
 *
 * Copyright 2019, WebFiori Framework.
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

use webfiori\ui\HTMLNode;
use webfiori\ui\Input;
use webfiori\ui\Label;
use webfiori\entity\Page;
use webfiori\WebFiori;
use webfiori\entity\Response;
use webfiori\entity\session\SessionsManager;
/**
 * A page which is used to show login form to enter login information to 
 * access cron web interface.
 *
 * @author Ibrahim
 */
class CronLoginView extends CronView {
    public function __construct() {
        parent::__construct('CRON Web Interface Login', 'Login to CRON Control panel.');
        
        if (SessionsManager::get('cron-login-status')) {
            Response::addHeader('location', WebFiori::getSiteConfig()->getBaseURL().'/cron/jobs');
            Response::send();
        }
        $form = new HTMLNode('form');
        Page::insert($form);
        $form->label('Enter Login Password:', [
            'style' => 'display:block;font-weight:bold;',
            'for' => 'password-input'
        ])->input('password', [
            'id' => 'password-input',
            'placeholder' => 'Enter CRON password here.',
            'style' => 'width:200px'
        ])->br()->br()->addChild(new HTMLNode('button'), [
            'id' => 'submit-button',
            'onclick' => 'login(this);return false;'
        ], false)->text('Login');
        Page::render();
    }
}

return __NAMESPACE__;
