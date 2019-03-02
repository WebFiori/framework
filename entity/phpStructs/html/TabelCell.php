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
/**
 * A class that represents a cell in HTML table.
 * The cell can be of type &lt;th&gt; or &lt;td&gt;.
 *
 * @author Ibrahim
 * @version 1.0
 */
class TabelCell extends HTMLNode{
    /**
     * Creates new instance of the class.
     * @param string $cellType The type of the cell. This attribute 
     * can have only one of two values, 'td' or 'th'. 'td' If the cell is 
     * in the body of the table and 'th' if the cell is in the header. If 
     * none of the two is given, 'td' will be used by default.
     * @since 1.0
     */
    public function __construct($cellType='td') {
        parent::__construct();
        $cType = strtolower($cellType);
        if($cType == 'td' || $cType == 'th'){
            $this->setNodeName($cType);
        }
        else{
            $this->setNodeName('td');
        }
    }
    /**
     * Sets the value of the attribute 'colspan'.
     * This attribute indicates for how many columns the cell extends. This 
     * attribute can have any value from 1 up to 1000. If the given value is 
     * not in the range, the attribute will not set.
     * @param int $colSpan The number of columns that the cell will span.
     * @since 1.0
     */
    public function setColSpan($colSpan) {
        if($colSpan >= 1 && $colSpan <= 1000){
            $this->setAttribute('colspan', $colSpan);
        }
    }
    /**
     * Sets the value of the attribute 'rowspan'.
     * This attribute indicates for how many rows the cell extends. This 
     * attribute can have any value from 0 up to 65534. If the given value is 
     * not in the range, the attribute will not set. If 0 is given, this means 
     * the cell will span till the end of table section.
     * @param int $rowSpan The number of rows that the cell will span.
     * @since 1.0
     */
    public function setRowSpan($rowSpan) {
        if($rowSpan >= 0 && $rowSpan <= 65534){
            $this->setAttribute('rowspan', $rowSpan);
        }
    }
    /**
     * Returns the value of the attribute 'colspan'.
     * This attribute indicates for how many columns the cell extends. If this attribute 
     * is not set, the default value for it will be 1.
     * @return int The number of columns that the cell will span.
     * @since 1.0
     */
    public function getColSpan(){
        $colSpn = $this->getAttributeValue('colspan');
        $retVal = $colSpn === NULL ? 1 : $colSpn;
        return $retVal;
    }
    /**
     * Returns the value of the attribute 'rowspan'.
     * This attribute indicates for how many rows the cell extends. If this attribute 
     * is not set, the default value for it will be 1.
     * @return int The number of rows that the cell will span.
     * @since 1.0
     */
    public function getRowSpan(){
        $colSpn = $this->getAttributeValue('rowspan');
        $retVal = $colSpn === NULL ? 1 : $colSpn;
        return $retVal;
    }
}
