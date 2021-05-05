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
namespace webfiori\framework\ui;

use webfiori\framework\WebFioriApp;
use webfiori\ui\HTMLNode;
/**
 * A basic view which is used to display HTTP error codes taken from 
 * language file.
 *
 * @author Ibrahim
 */
class HTTPCodeView extends WebPage {
    /**
     * Creates new instance of the class.
     */
    public function __construct($errCode) {
        parent::__construct();
        $this->setTheme(WebFioriApp::getAppConfig()->getBaseThemeName());

        $this->setTitle($this->get("general/http-codes/$errCode/code").' - '.$this->get("general/http-codes/$errCode/type"));
        http_response_code(intval($this->get("general/http-codes/$errCode/code")));
        $h1 = new HTMLNode('h1');
        $h1->text($this->getTitle());
        $this->insert($h1);
        $hr = new HTMLNode('hr');
        $this->insert($hr);
        $paragraph = new HTMLNode('p');
        $paragraph->text($this->get("general/http-codes/$errCode/message"));
        $this->insert($paragraph);
    }
}
