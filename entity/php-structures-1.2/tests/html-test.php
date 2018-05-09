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
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require '../Node.php';
require '../LinkedList.php';
require '../Stack.php';
require '../html/HTMLNode.php';
require '../html/HTMLDoc.php';
require '../html/HeadNode.php';
$doc = new HTMLDoc();
$head = new HeadNode();
$head->addCSS('http://localhost/res/css/x.css');
$head->addMeta('keywords', 'a,b,c,d');
$head->setBase('http://localhost');
$head->addJs('res/js/my-js.js');
$head->addAlternate('http://localhost/ar', 'ar');
$head->addAlternate('http://localhost/en', 'en');
$head->addJs('res/js/my-sec-js.js');
$head->addCSS('http://localhost/res/css/y.css');
$head->setCanonical('http://localhost/canonical');
$doc->setHeadNode($head);
$helloNode = new HTMLNode('', FALSE, TRUE);
$helloNode->setText('Hello World!');
$div = new HTMLNode();
$div->addChild($helloNode);
$doc->addNode(new HTMLNode());
$doc->addNode($helloNode);
$doc->addNode($helloNode);
$doc->addNode($helloNode);
$doc->addNode($div);
$doc->setLanguage('en');
echo $doc->toHTML();
$doc->saveToFile('test-not-formatted',FALSE);
