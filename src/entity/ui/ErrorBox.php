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
use webfiori\entity\Util;
/**
 * A fixed box which is used to show PHP warnings and notices.
 *
 * @author Ibrahim
 * @version 1.0
 */
class ErrorBox extends MessageBox {
    /**
     *
     * @var HTMLNode 
     */
    private $descNode;
    /**
     *
     * @var HTMLNode 
     */
    private $errNode;
    /**
     *
     * @var HTMLNode 
     */
    private $fileNode;
    /**
     *
     * @var string 
     */
    private $labelStyle;
    /**
     *
     * @var HTMLNode 
     */
    private $lineNode;
    /**
     *
     * @var HTMLNode 
     */
    private $messageNode;
    public function __construct() {
        parent::__construct();
        $this->labelStyle = 'style="color:#ff6666;font-family:monospace"';
        $this->setClassName('error-message-box');
        $this->setStyle([
            'top' => (self::getCount() * 10).'px',
            'left' => (self::getCount() * 10).'px'
        ]);
        $this->getHeader()->setClassName('error-header', false);
        $detailsContainer = &$this->getBody();
        $this->errNode = new HTMLNode('p');
        $this->errNode->setClassName('message-line');
        $detailsContainer->addChild($this->errNode);
        $this->errNode->addTextNode('<b '.$this->labelStyle.'>Error: </b>', false);
        $this->descNode = new HTMLNode('p');
        $this->descNode->setClassName('message-line');
        $detailsContainer->addChild($this->descNode);
        $this->descNode->addTextNode('<b '.$this->labelStyle.'>Description: </b>', false);
        $this->messageNode = new HTMLNode('p');
        $this->messageNode->setClassName('message-line');
        $detailsContainer->addChild($this->messageNode);
        $this->messageNode->addTextNode('<b '.$this->labelStyle.'>Message: </b>', false);
        $this->fileNode = new HTMLNode('p');
        $this->fileNode->setClassName('message-line');
        $detailsContainer->addChild($this->fileNode);
        $this->fileNode->addTextNode('<b '.$this->labelStyle.'>File: </b>', false);
        $this->lineNode = new HTMLNode('p');
        $this->lineNode->setClassName('message-line');
        $detailsContainer->addChild($this->lineNode);
        $this->lineNode->addTextNode('<b '.$this->labelStyle.'>Line: </b>', false);

        $this->setAttribute('onmouseover', "if(this.getAttribute('dg') === null){addDragSupport(this)}");
        $this->getHeader()->addTextNode('<b style="margin-left:10px;font-family:monospace;">Message ('.self::getCount().')</b>',false);
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
     * Sets error based on error number.
     * @param int $errno A PHP error number such as E_ERROR.
     * @since 1.0
     */
    public function setError($errno) {
        $this->errNode->removeAllChildNodes();
        $this->errNode->addTextNode('<b '.$this->labelStyle.'>Error: </b>'.Util::ERR_TYPES[$errno]['type'], false);
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
     * Sets error message.
     * @param string $msg The message that will be displayed.
     * @since 1.0
     */
    public function setMessage($msg) {
        $this->messageNode->removeAllChildNodes();
        $this->messageNode->addTextNode('<b '.$this->labelStyle.'>Message: </b>'.$msg, false);
    }
}
