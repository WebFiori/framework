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
use webfiori\WebFiori;
use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use phpStructs\html\JsCode;
use phpStructs\html\Label;
use phpStructs\html\Input;
/**
 * Description of CronLoginView
 *
 * @author Ibrahim
 */
class CronLoginView {
    public function __construct() {
        if(Cron::password() == 'NO_PASSWORD'){
            WebFiori::getWebsiteController()->setSessionVar('cron-login-status', true);
            header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron/jobs');
        }
        if(WebFiori::getWebsiteController()->getSessionVar('cron-login-status') == true){
            header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron/jobs');
        }
        Page::title('CRON Login');
        $jsCode = new JsCode();
        $jsCode->addCode(''
                . 'function login(source){'
                . "    source.innerHTML = 'Please wait...';"
                . "    source.setAttribute('disabled','');"
                . "    var pass = document.getElementById('password-input').value;"
                . '    var xhr = new XMLHttpRequest();'
                . "    xhr.open('post','cron/apis/login');"
                . '    xhr.onreadystatechange = function(){'
                . '        if(this.readyState === 4){'
                . "            source.removeAttribute('disabled');"
                . '            if(this.status === 200){'
                . "                window.location.href = 'cron/jobs';"
                . '            }'
                . '            else{'
                . "                source.innerHTML = 'Check your password';"
                . "                source.style['color'] = 'red';"
                . '            }'
                . '        }'
                . '    };'
                . "    xhr.setRequestHeader('content-type','application/x-www-form-urlencoded');"
                . "    xhr.send('password='+pass);"
                . '}');
        Page::document()->getHeadNode()->addChild($jsCode);
        $form = new HTMLNode('form');
        Page::insert($form);
        $label = new Label('Enter Login Password:');
        $label->setStyle([
            'display'=>'block',
            'font-weight'=>'bold'
        ]);
        $label->setAttribute('for', 'password-input');
        $form->addChild($label);
        $passInput = new Input('password');
        $passInput->setAttribute('placeholder', 'Enter CRON password here.');
        $passInput->setID('password-input');
        $passInput->setStyle([
            'width'=>'200px'
        ]);
        $form->addChild($passInput);
        $submit = new HTMLNode('button');
        $submit->addTextNode('Login');
        $submit->setAttribute('onclick', 'login(this);return false;');
        $submit->setID('submit-button');
        $form->addChild($submit);
        
        Page::render();
    }
}
new CronLoginView();