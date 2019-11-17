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
use phpStructs\html\TableCell;
use phpStructs\html\HTMLNode;
/**
 * A class that represents &lt;tr&gt; node.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class TableRow extends HTMLNode{
    public function __construct() {
        parent::__construct('tr');
    }
    /**
     * Adds new child node to the row.
     * The node will be added only if its an instance of the class 
     * 'TableCell'.
     * @param TableCell $node New table cell.
     * @since 1.0
     */
    public function addChild($node) {
        if($node instanceof TableCell){
            parent::addChild($node);
        }
    }
    /**
     * Adds new cell to the row.
     * @param string|TableCell $cellText The text of cell body. It can have HTML. 
     * Also, it can be an object of type 'TableCell'.
     * @param string $type The type of the cell. This attribute 
     * can have only one of two values, 'td' or 'th'. 'td' If the cell is 
     * in the body of the table and 'th' if the cell is in the header. If 
     * none of the two is given, 'td' will be used by default.
     * @param boolean $escEntities If set to true, the method will replace the 
     * characters '&lt;', '&gt;' and '&' with the following HTML 
     * entities: '&lt;', '&gt;' and '&amp;' in the given text. Default is false.
     * @param array $attrs An associative array of attributes which will be 
     * set for the added list. Applicable only if the first attribute of the 
     * method is a string.
     * @since 1.0
     */
    public function addCell($cellText,$type='td',$escEntities=false,$attrs=[]) {
        if($cellText instanceof TableCell){
            $this->addChild($cellText);
        }
        else{
            $cell = new TableCell($type);
            $cell->addTextNode($cellText,$escEntities);
            if(gettype($attrs) == 'array'){
                foreach ($attrs as $a=>$v){
                    $cell->setAttribute($a, $v);
                }
            }
            $this->addChild($cell);
        }
    }
    /**
     * Returns a table cell given its index.
     * @param int $index Cell index starting from 0.
     * @return TableCell|null If the cell does exist, the method will return 
     * an object of type 'TableCell'. If cell does not exist, the method 
     * will return null.
     * @version 1.0.1
     */
    public function getCell($index) {
        return $this->children()->get($index);
    }
}
