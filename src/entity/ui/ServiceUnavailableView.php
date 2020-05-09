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

use webfiori\conf\Config;
use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use phpStructs\html\UnorderedList;

/**
 * A view which is show to tell the user that the framework isn't configured 
 * yet.
 * @author Ibrahim
 */
class ServiceUnavailableView extends View{
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        Page::title('Service Unavailable');
        $hNode = new HTMLNode('h1');
        $hNode->addTextNode('503 - Service Unavailable');
        Page::insert($hNode);
        Page::insert(HTMLNode::createTextNode('<hr>', false));
        $paragraph = new HTMLNode('p');
        $paragraph->addTextNode('This error means that the system is not configured yet. '
            .'Make sure to make the method <a target="_blank" href="https://webfiori.com/docs/webfiori/conf/Config#isConfig">Config::isConfig()</a> return true. There are two ways '
            .'to change return value of this method:', false);
        Page::insert($paragraph);
        $configStepsList = new UnorderedList([
            'Go to the file "conf/Config.php". Change attribute "isConfigured" value to true.',
            'Use the method <a target="_blank" href="https://webfiori.com/docs/webfiori/logic/ConfigController#configured">ConfigController::configured</a>(true). You must supply \'true\' as an attribute.',
            'After that, reload the page and the system will work.'
        ], false);
        Page::insert($configStepsList);
        $changesParag = new HTMLNode('p');
        $changesParag->addTextNode('If you want to make the system do something else if the return value of the '
            .'given method is false, then open the file \'WebFiori.php\' and '
            .'change the code in the \'else\' code block at the end of class constructor (Inside the "if" block).');
        Page::insert($changesParag);
        $poweredByParag = new HTMLNode('p');
        $poweredByParag->addTextNode('System Powerd By: <a href="https://github.com/usernane/webfiori" target="_blank"><b>'
                    .'WebFiori Framework v'.Config::getVersion().' ('.Config::getVersionType().')'
                    .'</b></a>', false);
        Page::insert($poweredByParag);
    }
}
