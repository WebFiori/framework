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
namespace webfiori\entity\ui;

use webfiori\ui\HTMLNode;
use webfiori\framework\Page;
use webfiori\WebFiori;
use webfiori\entity\Response;
/**
 * A basic view which is used to display 404 HTTP error code and 
 * messages.
 *
 * @author Ibrahim
 */
class NotFoundView {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        Page::theme(WebFiori::getSiteConfig()->getBaseThemeName());
        Page::lang(WebFiori::getSysController()->getSessionLang());
        Page::siteName(WebFiori::getSiteConfig()->getWebsiteNames()[Page::lang()]);
        $labels = Page::translation()->get('general/http-codes/404');
        Page::title($labels['code'].' - '.$labels['type']);
        http_response_code($labels['code']);
        $h1 = new HTMLNode('h1');
        $h1->addTextNode(Page::title());
        Page::insert($h1);
        $hr = new HTMLNode('hr');
        Page::insert($hr);
        $paragraph = new HTMLNode('p');
        $paragraph->addTextNode($labels['message']);
        Page::insert($paragraph);
    }
    /**
     * Display the page.
     */
    public function display() {
        Page::render();
        Response::setCode(404);
        Response::send();
    }
}
