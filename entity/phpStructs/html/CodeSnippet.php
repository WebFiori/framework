<?php
/*
 * The MIT License
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh, phpStructs.
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
namespace phpStructs\html;
use phpStructs\html\HTMLNode;
use phpStructs\html\PNode;
/**
 * A class that can be used to display code snippits in good looking way.
 * The class has a set of nodes which defines the following attributes of a 
 * code block:
 * <ul>
 * <li>A title for the code snippit.</li>
 * <li>Line numbers.</li>
 * <li>The code it self.</li>
 * </ul>
 * The developer can use the following CSS selectors (class selector) to customize the snippit 
 * using CSS:
 * <ul>
 * <li>code-snippit: The container that contains all other elements.</li>
 * <li>snippit-title: Can be used to customize the look and feel of snippit title.</li>
 * <li>line-numbers: A container that contains a set of span elements which has 
 * line numbers.</li>
 * <li>line-number: A single span element that contains line number.</li>
 * <li>code-display: An area that contains pre element which wraps a code element.</li>
 * <li>code: The container that contains the code.</li>
 * </ul>
 * @author Ibrahim
 * @version 1.0.1
 */
class CodeSnippet extends HTMLNode{
    /**
     *
     * @var HTMLCode
     * @since 1.0 
     */
    private $lineNumsNode;
    /**
     *
     * @var PNode
     * @since 1.0 
     */
    private $titleNode;
    /**
     *
     * @var HTMLCode
     * @since 1.0 
     */
    private $pre;
    /**
     *
     * @var HTMLCode
     * @since 1.0 
     */
    private $code;
    /**
     *
     * @var type 
     * @since 1.0.1
     */
    private $codeStr;
    /**
     *
     * @var HTMLCode
     * @since 1.0 
     */
    private $codeDisplay;
    private $currentLineNum;
    public function __construct() {
        parent::__construct();
        $this->codeStr = '';
        $this->currentLineNum = 1;
        $this->codeDisplay = new HTMLNode();
        $this->codeDisplay->setClassName('code-display');
        $this->codeDisplay->setStyle(
            array(
                'border-top'=>'1px dotted white',
                'overflow-x'=>'scroll',
                'direction'=>'ltr'
            )
        );
        $this->lineNumsNode = new HTMLNode();
        $this->lineNumsNode->setClassName('line-numbers');
        $this->lineNumsNode->setStyle(
            array(
                'float'=>'left',
                'margin-top'=>'1px',
                'line-height'=>'18px !important;',
                'border'=>'1px dotted black;'
            )
        );
        $this->titleNode = new PNode();
        $this->titleNode->addText('Code');
        $this->titleNode->setClassName('snippit-title');
        $this->titleNode->setStyle(
            array(
                'padding'=>'0',
                'padding-left'=>'10px',
                'padding-right'=>'10px',
                'margin'=>'0',
                'border'=>'1px dotted'
            )
        );
        $this->pre = new HTMLNode('pre');
        $this->pre->setIsFormatted(FALSE);
        $this->pre->setStyle(
            array(
                'margin'=>'0',
                'float'=>'left',
                'border'=>'1px dotted black'
            )
        );
        $this->code = new HTMLNode('code');
        $this->code->addTextNode('');
        $this->code->setClassName('code');
        $this->code->setIsFormatted(FALSE);
        $this->code->setStyle(
            array(
                'line-height'=>'18px !important;',
                'display'=>'block',
                'float'=>'left'
            )    
        );
        
        $this->setClassName('code-snippt');
        $this->setStyle(
            array(
                'padding-bottom'=>'16px',
                'border'=>'1px dotted black',
                'width'=>'100%;',
                'margin-bottom'=>'25px',
                'float'=>'left'
            )
        );
        
        $this->addChild($this->titleNode);
        $this->addChild($this->lineNumsNode);
        $this->addChild($this->codeDisplay);
        //$this->codeDisplay->addChild($this->lineNumsNode);
        $this->codeDisplay->addChild($this->pre);
        $this->pre->addChild($this->code);
        $this->_addLine();
    }
    private function _addLine() {
        $span = new HTMLNode('span');
        $span->setClassName('line-number');
        $span->setAttribute('style', ''
                . 'font-weight: bold;'
                . 'display: block;'
                . 'font-family: monospace;'
                . 'border-right: 1px dotted white;'
                . 'padding-right: 4px;'
                . 'color: #378e80;'
                . '');
        $span->addTextNode($this->currentLineNum);
        $this->currentLineNum++;
        $this->lineNumsNode->addChild($span);
    }
    /**
     * Sets the title of the snippit.
     * This can be used to specify the language the code represents (e.g. 
     * 'Java Code' or 'HTMLCode'. The title will appear at the top of the snippit 
     * block.
     * @param string $title The title of the snippit.
     * @since 1.0
     */
    public function setTitle($title) {
        $this->titleNode->clear();
        $this->titleNode->addText($title);
    }
    /**
     * Sets the code that will be displayed by the snippit block.
     * @param string $code The code.
     * @since 1.0
     */
    public function setCode($code) {
        $xCode = trim($code);
        $len = strlen($xCode);
        if($len !== 0){
            for($x = 0 ; $x < $len ; $x++){
                if($xCode[$x] == "\n"){
                    $this->_addLine();
                }
            }
        }
        //$this->_addLine();
        $this->codeStr = $xCode."\n";
    }
    public function toHTML($formatted = false, $initTab = 0) {
        $this->code->children()->get(0)->setText($this->codeStr);
        return parent::toHTML($formatted, $initTab);
    }
    public function asCode($formattingOptions = HTMLNode::DEFAULT_CODE_FORMAT) {
        $this->code->children()->get(0)->setText($this->codeStr);
        return parent::asCode($formattingOptions);
    }
    /**
     * Adds new line of code to the code snippit.
     * @param string $codeAsTxt The code line. It does not have to include "\n" 
     * character as the method will append it automatically to the string.
     * @since 1.0.1
     */
    public function addCodeLine($codeAsTxt) {
        $this->_addLine();
        $this->codeStr .= trim($codeAsTxt,"\n\r")."\n";
    }
}
