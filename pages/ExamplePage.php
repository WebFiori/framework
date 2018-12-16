<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
namespace examples\views;
/*
 * The next block of code can be added to every view or .php 
 * file to prevent direct access.
 */
if(!defined('ROOT_DIR')){
    Logger::log('Direct access. Forbidden','error');
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
use webfiori\entity\Page;
use phpStructs\html\PNode;
use phpStructs\html\HTMLNode;
class ExamplePage{
    public function __construct() {
        //load UI template components (JS, CSS and others)
        //it is optional. to use a theme but recomended
        Page::theme($themeName='Greeny By Ibrahim Ali');

        //sets the title of the page
        $lang = Page::lang();
        if($lang == 'AR'){
            Page::title('مثال على صفحة');
            //adds a paragraph to the body of the page.
            $p = new PNode();
            $p->addText('أهلا و سهلا من إطار "ويب فيوري"!');
            Page::insert($p);
        }
        else{
            Page::title('Example Page');
            //adds a paragraph to the body of the page.
            $p = new PNode();
            $p->addText('Hello from "WebFiori Framework"!');
            Page::insert($p);
        }
        $sec = new HTMLNode('section');
        Page::insert($sec);
        $secH = new HTMLNode('h1');
        $secH->addTextNode('Datagram');
        $sec->addChild($secH);
        $p2 = new PNode();
        $p2->addText('A datagram is a basic transfer unit associated with a packet-switched network. Datagrams are typically structured in header and payload sections. Datagrams provide a connectionless communication service across a packet-switched network. The delivery, arrival time, and order of arrival of datagrams need not be guaranteed by the network. ');
        $sec->addChild($p2);
        
        $sec = new HTMLNode('section');
        Page::insert($sec);
        $secH = new HTMLNode('h1');
        $secH->addTextNode('History');
        $sec->addChild($secH);
        $p2 = new PNode();
        $p2->addText('The term datagram appeared first within the project CYCLADES, a packet-switched network created in the early 1970s, and was coined by Louis Pouzin[1] by combining the words data and telegram. CYCLADES was the first network to make the hosts responsible for the reliable delivery of data, rather than the network itself, using unreliable datagrams and associated end-to-end principle.');
        $sec->addChild($p2);
        Page::render();
    }
}
new ExamplePage();