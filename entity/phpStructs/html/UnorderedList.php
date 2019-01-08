<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
/**
 * A class that represents Unordered List HTML element (ul)
 *
 * @author Ibrahim
 * @version 1.0
 */
class UnorderedList extends HTMLNode{
    public function __construct() {
        parent::__construct('ul', TRUE);
    }
    /**
     * Adds new list item.
     * @param ListItem $listItem The list item that will be added.
     * @since 1.0
     */
    public function addListItem($listItem) {
        $this->addChild($listItem);
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
        if($node instanceof ListItem){
            parent::addChild($node);
        }
        else if($node instanceof UnorderedList){
            parent::addChild($node);
        }
    }
}
