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
namespace webfiori\entity;
use phpStructs\html\HTMLNode;
/**
 * A fixed box which is used to show PHP warnings and notices.
 *
 * @author Ibrahim
 * @version 1.0
 */
class ErrorBox extends HTMLNode{
    /**
     * Used to format errors and warnings messages.
     * @var int 
     * @since 1.3.4
     */
    private static $NoticeAndWarningCount = 0;
    /**
     *
     * @var HTMLNode 
     */
    private $errNode;
    /**
     *
     * @var HTMLNode 
     */
    private $descNode;
    /**
     *
     * @var HTMLNode 
     */
    private $messageNode;
    /**
     *
     * @var HTMLNode 
     */
    private $fileNode;
    /**
     *
     * @var HTMLNode 
     */
    private $lineNode;
    /**
     *
     * @var string 
     */
    private $labelStyle;
    public function __construct() {
        parent::__construct();
        $this->labelStyle = 'style="color:#ff6666;font-family:monospace"';
        $this->setClassName('error-message-box');
        $this->setAttribute('data-err-message-number', self::getWarningsAndNoticesCount());
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
            'top'=> (self::getWarningsAndNoticesCount()*10).'px',
            'left'=> (self::getWarningsAndNoticesCount()*10).'px'
        ]);
        $this->setAttribute('','');
        $closeContainer = new HTMLNode();
        $closeContainer->setClassName('error-message-header');
        $closeContainer->setStyle([
            'width'=>'100%',
            'cursor'=>'move',
            'background-color'=>'crimson'
        ]);
        $this->addChild($closeContainer);
        $closeButton = new HTMLNode('button');
        $closeButton->setClassName('error-close-button');
        $closeButton->addTextNode('X');
        $closeContainer->addChild($closeButton);
        $closeButton->setAttribute('onclick', "this.parentElement.parentElement.style['display'] = 'none'");
        
        $detailsContainer = new HTMLNode();
        $detailsContainer->setStyle([
            'overflow-y'=>'scroll',
            'overflow-x'=>'auto',
            'width'=>'100%',
            'height'=>'100px'
        ]);
        $this->addChild($detailsContainer);
        $this->errNode = new HTMLNode('p');
        $this->errNode->setClassName('message-line');
        $this->errNode->setStyle([
            'margin'=>0,
            'font-family'=>'monospace'
        ]);
        $detailsContainer->addChild($this->errNode);
        $this->errNode->addTextNode('<b '.$this->labelStyle.'>Error: </b>', false);
        $this->descNode  = new HTMLNode('p');
        $this->descNode->setClassName('message-line');
        $this->descNode->setStyle([
            'margin'=>0,
            'font-family'=>'monospace'
        ]);
        $detailsContainer->addChild($this->descNode);
        $this->descNode->addTextNode('<b '.$this->labelStyle.'>Description: </b>', false);
        $this->messageNode = new HTMLNode('p');
        $this->descNode->setClassName('message-line');
        $this->messageNode->setStyle([
            'margin'=>0,
            'font-family'=>'monospace'
        ]);
        $detailsContainer->addChild($this->messageNode);
        $this->messageNode->addTextNode('<b '.$this->labelStyle.'>Message: </b>', false);
        $this->fileNode = new HTMLNode('p');
        $this->fileNode->setClassName('message-line');
        $this->fileNode->setStyle([
            'margin'=>0,
            'font-family'=>'monospace'
        ]);
        $detailsContainer->addChild($this->fileNode);
        $this->fileNode->addTextNode('<b '.$this->labelStyle.'>File: </b>', false);
        $this->lineNode  = new HTMLNode('p');
        $this->lineNode->setClassName('message-line');
        $this->lineNode->setStyle([
            'margin'=>0,
            'font-family'=>'monospace'
        ]);
        $detailsContainer->addChild($this->lineNode);
        $this->lineNode->addTextNode('<b '.$this->labelStyle.'>Line: </b>', false);
        if(self::getWarningsAndNoticesCount() == 0){
            $js = new HTMLNode('script');
            $js->setAttribute('type', 'text/javascript');
            $js->addTextNode(""
                    . "function addDragSupport(source){"
                    . "source.setAttribute(\"dg\",true);"
                    . "var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;"
                    . "var boxNum = source.getAttribute(\"data-err-message-number\");"
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
        $this->setAttribute('onmouseover', "if(this.getAttribute('dg') === null){addDragSupport(this)}");
        self::$NoticeAndWarningCount++;
        $closeContainer->addTextNode('<b style="margin-left:10px;font-family:monospace;">Message ('.self::getWarningsAndNoticesCount().')</b>',false);
    }
    /**
     * Sets error based on error number.
     * @param int $errno A PHP error number such as E_ERROR.
     * @since 1.0
     */
    public function setError($errno) {
        $this->errNode->removeAllChildNodes();
        $this->errNode->addTextNode('<b '.$this->labelStyle.'>Error: </b>'.Util::ERR_TYPES[$errno]['type'], false);
    }
    /**
     * Sets error description based on error number.
     * @param int $errno A PHP error number such as E_ERROR.
     * @since 1.0
     */
    public function setDescription($errno) {
        $this->descNode->removeAllChildNodes();
        $this->descNode->addTextNode('<b '.$this->labelStyle.'>Description: </b>'.Util::ERR_TYPES[$errno]['description'], false);
    }
    /**
     * Sets error message.
     * @param string $msg The message that will be displayed.
     * @since 1.0
     */
    public function setMessage($msg) {
        $this->messageNode->removeAllChildNodes();
        $this->messageNode->addTextNode('<b '.$this->labelStyle.'>Message: </b>'.$msg, false);
    }
    /**
     * Sets the file that caused the error.
     * @param string $file The absolute path of the file that has the error.
     * @since 1.0
     */
    public function setFile($file) {
        $this->fileNode->removeAllChildNodes();
        $this->fileNode->addTextNode('<b '.$this->labelStyle.'>File: </b>'.$file, false);
    }
    /**
     * Sets error line number.
     * @param string $line The line that caused the error.
     * @since 1.0
     */
    public function setLine($line) {
        $this->lineNode->removeAllChildNodes();
        $this->lineNode->addTextNode('<b '.$this->labelStyle.'>Line: </b>'.$line, false);
    }
    /**
     * Returns the number of warning messages and notices which was generated.
     * The count will manly depends on the number of instances that was created 
     * from the class ErrorBox. Every instance will increment the value by 1.
     * @return int Number of warning messages and notices which was generated. by 
     * PHP.
     * @since 1.0
     */
    public static function getWarningsAndNoticesCount() {
        return self::$NoticeAndWarningCount;
    }
}
