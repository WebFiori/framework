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
 * A generic class for showing a floating box in web pages that can have any content 
 * in its body.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class MessageBox extends HTMLNode {
    /**
     * Used to format boxes.
     * @var int 
     * @since 1.0
     */
    private static $Count = 0;
    /**
     *
     * @var boolean 
     * 
     * @since 1.0
     */
    private $isInit;
    /**
     * The node that represents the body of the message box.
     * @var HTMLNode
     * @since 1.0 
     */
    private $messageBody;
    /**
     * The node that represents the header of the message box.
     * @var HTMLNode
     * @since 1.0 
     */
    private $messageHeader;
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->isInit = false;

        if (defined('MAX_BOX_MESSAGES') && self::getCount() + 1 < MAX_BOX_MESSAGES) {
            $this->_init();
        } else if (!defined('MAX_BOX_MESSAGES')) {
            $this->_init();
        }
        $this->setStyle([
            'font-size' => '8pt'
        ]);
    }
    /**
     * Returns the node that represents the header of the message.
     * The returned node can be used to add extra content to the header. 
     * By default, the header will have a close button.
     * @return HTMLNode a node that represents the header of message box.
     * @since 1.0
     */
    public function &getHeader() {
        return $this->messageHeader;
    }
    /**
     * Returns the node that represents the body of the message.
     * The returned node can be used to display content in box body.
     * @return HTMLNode a node that represents the body of message box.
     * @since 1.0
     */
    public function &getBody() {
        return $this->messageBody;
    }
    /**
     * Returns the number of message boxes which has been created.
     * The count will manly depends on the number of instances that was created. 
     * Every instance will increment the value by 1.
     * @return int The number of message boxes which has been created.
     * @since 1.0
     */
    public static function getCount() {
        return self::$Count;
    }
    /**
     * Checks if the message box is fully initialized.
     * 
     * @return boolean The method will return true if the message box is fully 
     * initialized and its components are ready for use. False if not.
     * 
     * @since 1.0
     */
    public function isInitialized() {
        return $this->isInit;
    }
    /**
     * Initialize the node that represents the body of the box.
     * @since 1.0
     */
    private function _createBody() {
        $this->messageBody = new HTMLNode();
        $this->messageBody->setClassName('message-box-body');
        $this->addChild($this->messageBody);
    }
    /**
     * Initialize the node that represent message box header.
     */
    private function _createHeader() {
        $this->messageHeader = new HTMLNode();
        $this->messageHeader->setClassName('message-box-header');
        $this->addChild($this->messageHeader);
        $closeButton = new HTMLNode('button');
        $closeButton->setClassName('box-close-button');
        $closeButton->addTextNode('X');
        $this->messageHeader->addChild($closeButton);
        $closeButton->setAttribute('onclick', "this.setAttribute('disabled','');smoothHide(this.parentElement.parentElement);");
    }
    private function _init() {
        $this->setClassName('floating-message-box');
        $this->setAttribute('data-box-number', self::getCount());
        $this->setStyle([
            'top' => (self::getCount() * 10).'px',
            'left' => (self::getCount() * 10).'px'
        ]);
        $this->_createHeader();
        $this->_createBody();
        $this->setAttribute('onmouseover', "if(this.getAttribute('dg') === null){addDragSupport(this)}");

        if (self::getCount() == 0) {
            $css = new HTMLNode('link');
            $css->setAttributes([
                'rel' => 'stylesheet',
                'href' => 'https://cdn.jsdelivr.net/gh/webfiori/app@'.WF_VERSION.'/public/assets/css/message-box.css'
            ]);
            $this->addChild($css);
            $js = new HTMLNode('script');
            $js->setAttributes([
                'type' => 'text/javascript',
                'src' => 'https://cdn.jsdelivr.net/gh/webfiori/app@'.WF_VERSION.'/public/assets/js/message-box.js'
            ]);
            $this->addChild($js);
        }
        self::$Count++;
        $this->isInit = true;
    }
}
