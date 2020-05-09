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
namespace webfiori\entity\ui;

use phpStructs\html\HTMLNode;
use Throwable;
use webfiori\entity\Page;
use webfiori\entity\Util;
/**
 * A page which is used to display exception information when it is thrown or 
 * any other errors.
 *
 * @author Ibrahim
 * @version 1.0
 */
class ServerErrView extends View{
    /**
     * Creates a new instance of the class.
     * @param Throwable|array $throwableOrErr This can be an instance of the 
     * interface 'Throwable' or it can be an array that contains error information 
     * which was returned from the method 'error_get_last()'.
     * @since 1.0
     */
    public function __construct($throwableOrErr) {
        Page::reset();
        Page::title('Uncaught Exception');
        Page::document()->getHeadNode()->addCSS('assets/css/server-err.css',[],false);
        $hNode = new HTMLNode('h1');

        if ($throwableOrErr instanceof Throwable) {
            $hNode->addTextNode('500 - Server Error: Uncaught Exception.');

            Page::insert($hNode);
            Page::insert($this->_createMessageLine('Exception Class:', get_class($throwableOrErr)));
            Page::insert($this->_createMessageLine('Exception Message:', $throwableOrErr->getMessage()));
            Page::insert($this->_createMessageLine('Exception Code:', $throwableOrErr->getCode()));
            Page::insert($this->_createMessageLine('File:', $throwableOrErr->getFile()));
            Page::insert($this->_createMessageLine('Line:', $throwableOrErr->getLine()));
            Page::insert($this->_createMessageLine('Stack Trace:', ''));
            $stackTrace = new HTMLNode('pre');
            $stackTrace->addTextNode($throwableOrErr->getTraceAsString());
            Page::insert($stackTrace);
        } else {
            $hNode->addTextNode('500 - Server Error');
            Page::insert($this->_createMessageLine('Type:', Util::ERR_TYPES[$throwableOrErr["type"]]['type']));
            Page::insert($this->_createMessageLine('Description:', Util::ERR_TYPES[$throwableOrErr["type"]]['description']));
            Page::insert($this->_createMessageLine('Message: ', $throwableOrErr["message"]));
            Page::insert($this->_createMessageLine('File: ', $throwableOrErr["file"]));
            Page::insert($this->_createMessageLine('Line: ', $throwableOrErr["line"]));
        }
    }
    /**
     * 
     * @param string $label
     * @param string $info
     * @return HTMLNode
     * @since 1.0
     */
    private function _createMessageLine($label, $info) {
        $node = new HTMLNode('p');
        $labelNode = new HTMLNode('b');
        $labelNode->setClassName('nice-red mono');
        $labelNode->addTextNode($label.' ');
        $node->addChild($labelNode);
        $infoNode = new HTMLNode('span');
        $infoNode->setClassName('mono');
        $infoNode->addTextNode($info);
        $node->addChild($infoNode);

        return $node;
    }
}
