<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\entity\cron;
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
            WebFiori::getWebsiteFunctions()->setSessionVar('cron-login-status', true);
            header('location: /cron/jobs');
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
                . "                source.innerHTML = 'Failed to login';"
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