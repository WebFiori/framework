<?php
/**
 * A class that represents &lt;a&gt; tag.
 */
class Link{
    private $href;
    private $target;
    private $label;
    /**
     * Constructs a new instance of the class
     * @param string $href The link.
     * @param string $label The label to display.
     * @param string $target [optional] The value to set for the attribute 'target'.
     */
    public function __construct($href,$label,$target='') {
        $this->href = $href;
        $this->label = $label;
        $this->target = $target;
    }
    
    public function __toString() {
        if($this->target != ''){
            return '<a target="'.$this->target.'" href="'.$this->href.'">'.$this->label.'</a>';
        }
        else{
            return '<a href="'.$this->href.'">'.$this->label.'</a>';
        }
    }
}
