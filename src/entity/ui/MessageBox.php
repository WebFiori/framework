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
use phpStructs\html\HTMLNode;
/**
 * A generic class for showing a floating box in web pages that can have any content 
 * in its body.
 *
 * @author Ibrahim
 * @version 1.0
 */
class MessageBox extends HTMLNode{
    /**
     * Used to format boxes.
     * @var int 
     * @since 1.0
     */
    private static $Count = 0;
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
        $this->setClassName('floating-message-box');
        $this->setAttribute('data-box-number', self::getCount());
        $this->setStyle([
            'width'=>'75%',
            'border'=>'1px double white',
            'height'=>'130px',
            'margin'=>'0px',
            'z-index'=>'100',
            'position'=>'fixed',
            'background-color'=>'rgba(0,0,0,0.7)',
            'color'=>'white',
            'height'=>'auto',
            'top'=> (self::getCount()*10).'px',
            'left'=> (self::getCount()*10).'px'
        ]);
        $this->_createHeader();
        $this->_createBody();
        $this->setAttribute('onmouseover', "if(this.getAttribute('dg') === null){addDragSupport(this)}");
        if(self::getCount() == 0){
            $this->_initJS();
        }
        self::$Count++;
    }
    /**
     * Initialize JavaScript code which is used to add logic to the box.
     * @since 1.0
     */
    private function _initJS() {
        $js = new HTMLNode('script');
        $js->setAttribute('type', 'text/javascript');
        $js->addTextNode(""
                    . "function smoothHide(el){"
                    . "var o = 1;"
                    . "var intrvalId = setInterval(function(){"
                    . "if(o > 0){"
                    . "o = o - 0.02;"
                    . "el.style['opacity'] = o;"
                    . "}"
                    . "else{"
                    . "clearInterval(intrvalId);"
                    . "el.style['display'] = 'none';"
                    . "}"
                    . "},15);"
                    . "};"
                    . "function addDragSupport(source){"
                    . "source.setAttribute(\"dg\",true);"
                    . "var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;"
                    . "var boxNum = source.getAttribute(\"data-box-number\");"
                    . "if (boxNum === null) {"
                    . "source.onmousedown = mouseDown;"
                    . "}"
                    . "else{"
                    . "source.children[0].onmousedown = mouseDown"
                    . "}"
                    . ""
                    . "function mouseDown(e) {"
                    . "e = e || window.event;"
                    . "e.preventDefault();"
                    . "pos3 = e.clientX;"
                    . "pos4 = e.clientY;"
                    . "document.onmouseup = dragStopped;"
                    . "document.onmousemove = dragStarted;"
                    . "};"
                    . "function dragStarted(e) {"
                    . "e = e || window.event;"
                    . " e.preventDefault();"
                    . "pos1 = pos3 - e.clientX;"
                    . "pos2 = pos4 - e.clientY;"
                    . "pos3 = e.clientX;"
                    . "pos4 = e.clientY;"
                    . "source.style.top = (source.offsetTop - pos2) + \"px\";"
                    . "source.style.left = (source.offsetLeft - pos1) + \"px\";"
                    . "};"
                    . "function dragStopped(){"
                    . "document.onmouseup = null;"
                    . "document.onmousemove = null;"
                    . "};"
                    . "};", false);
        $this->addChild($js);
    }
    /**
     * Initialize the node that represents the body of the box.
     * @since 1.0
     */
    private function _createBody() {
        $this->messageBody = new HTMLNode();
        $this->messageBody->setClassName('message-box-body');
        $this->messageBody->setStyle([
            'overflow-y'=>'scroll',
            'overflow-x'=>'auto',
            'width'=>'100%',
            'height'=>'100px',
            'padding'=>'10px'
        ]);
        $this->addChild($this->messageBody);
    }
    /**
     * Initialize the node that represent message box header.
     */
    private function _createHeader(){
        $this->messageHeader = new HTMLNode();
        $this->messageHeader->setClassName('message-box-header');
        $this->messageHeader->setStyle([
            'width'=>'100%',
            'cursor'=>'move',
            'background-color'=>'burlywood'
        ]);
        $this->addChild($this->messageHeader);
        $closeButton = new HTMLNode('button');
        $closeButton->setClassName('box-close-button');
        $closeButton->setStyle([
            'border'=>'0px',
            'cursor'=>'pointer'
        ]);
        $closeButton->addTextNode('X');
        $this->messageHeader->addChild($closeButton);
        $closeButton->setAttribute('onclick', "this.setAttribute('disabled','');smoothHide(this.parentElement.parentElement);");
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
}
