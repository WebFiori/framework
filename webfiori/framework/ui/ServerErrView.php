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
namespace webfiori\framework\ui;

use Throwable;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\Util;
use webfiori\framework\WebFioriApp;
use webfiori\http\Response;
use webfiori\ui\HTMLNode;
use webfiori\error\AbstractExceptionHandler;
use webfiori\framework\ui\WebPage;
/**
 * A page which is used to display exception information when it is thrown or 
 * any other errors.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class ServerErrView extends WebPage {
    /**
     *
     * @var Throwable|Error
     * @since 1.0 
     */
    private $errOrThrowable;
    /**
     * 
     * @var WebPage
     * 
     * @since 1.0.2
     */
    private $page;
    /**
     * Creates a new instance of the class.
     * 
     * @param AbstractExceptionHandler $throwableOrErr The handler which is
     * used to handle exceptions.
     * 
     * @since 1.0
     */
    public function __construct(AbstractExceptionHandler $throwableOrErr) {
        parent::__construct();
        
        $this->setTitle('Uncaught Exception');
        
        $this->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',[], false);
        $this->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css', [], false);
        $this->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css', [], false);
        $this->addJs('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', [], false);
        $this->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', [], false);
        
        $hNode = $this->insert('v-alert');
        $hNode->setAttributes([
            'prominent',
            'type' => "error"
        ]);
        $hNode->addChild('v-row', [
            'align' => "center"
        ])->addChild('v-col')->text('500 - Server Error: Uncaught Exception.');

    }
}
