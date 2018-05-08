<?php
/**
 * A class that represents &lt;a&gt; tag.
 */
class Link extends HTMLNode{
    /**
     * Constructs a new instance of the class
     * @param string $href The link.
     * @param string $label The label to display.
     * @param string $target [optional] The value to set for the attribute 'target'.
     */
    public function __construct($href,$label,$target='') {
        parent::__construct('a');
        $this->setAttribute('href',$href);
        if($target != ''){
            $this->setAttribute('target', $target);
        }
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText($label);
        $this->addChild($textNode);
    }
}
