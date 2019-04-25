<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim, phpStructs Library.
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
 * A class that represents Unordered List HTML element (ul)
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class UnorderedList extends HTMLNode{
    public function __construct() {
        parent::__construct('ul');
    }
    /**
     * Adds new item to the list.
     * @param string $listItemText The text that will be displayed by the 
     * list item.
     * @param boolean $escHtmlEntities If set to TRUE, the method will 
     * replace the characters '&lt;', '&gt;' and 
     * '&amp' with the following HTML entities: '&amp;lt;', '&amp;gt;' and '&amp;amp;' 
     * in the given text. Default is TRUE.
     * @since 1.0
     */
    public function addListItem($listItemText,$escHtmlEntities=true) {
        $li = new ListItem();
        $li->addTextNode($listItemText,$escHtmlEntities);
        $this->addChild($li);
    }
    /**
     * Adds multiple items at once to the list.
     * @param array $arrOfItems An array that contains strings 
     * that represents each list item.
     * @param boolean $escHtmlEntities If set to TRUE, the method will 
     * replace the characters '&lt;', '&gt;' and 
     * '&amp' with the following HTML entities: '&amp;lt;', '&amp;gt;' and '&amp;amp;' 
     * in the given text. Default is TRUE.
     * @since 1.0.1
     */
    public function addListItems($arrOfItems,$escHtmlEntities=true) {
        if(gettype($arrOfItems) == 'array'){
            foreach ($arrOfItems as $listItem){
                $this->addListItem($listItem,$escHtmlEntities);
            }
        }
    }
    /**
     * Adds a sublist to the main list.
     * @param UnorderedList $ul An object of type UnorderedList.
     * @since 1.0
     */
    public function addSubList($ul){
        $this->addChild($ul);
    }
    /**
     * Adds new list item or a sub-list.
     * @param ListItem|UnorderedList $node The node that will be added.
     * @since 1.0
     */
    public function addChild($node) {
        if($node instanceof ListItem || $node instanceof UnorderedList){
            parent::addChild($node);
        }
    }
}
