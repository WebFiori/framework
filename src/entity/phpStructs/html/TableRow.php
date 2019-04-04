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
use phpStructs\html\TabelCell;
use phpStructs\html\HTMLNode;
/**
 * A class that represents &lt;tr&gt; node.
 *
 * @author Ibrahim
 * @version 1.0
 */
class TableRow extends HTMLNode{
    public function __construct() {
        parent::__construct('tr', TRUE);
    }
    /**
     * Adds new child node to the row.
     * The node will be added only if its an instance of the class 
     * 'TableCell'.
     * @param TabelCell $node New table cell.
     * @since 1.0
     */
    public function addChild($node) {
        if($node instanceof TabelCell){
            parent::addChild($node);
        }
    }
    /**
     * Adds new cell to the row with a text in its body.
     * @param string $cellText The text of cell body.
     * @param string $type The type of the cell. This attribute 
     * can have only one of two values, 'td' or 'th'. 'td' If the cell is 
     * in the body of the table and 'th' if the cell is in the header. If 
     * none of the two is given, 'td' will be used by default.
     * @since 1.0
     */
    public function addCell($cellText,$type='td') {
        $cell = new TabelCell($type);
        $cell->addTextNode($cellText,FALSE);
        $this->addChild($cell);
    }
}
