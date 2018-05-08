<?php
/**
 * A class that represents aside menu.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class AsideMenu{
    /**
     * An array of the links that will be on the menu.
     * @var array
     * @since 1.0 
     */
    private $links = array();
    /**
     * The number of the link that is currently active.
     * @var int
     * @since 1.0 
     */
    private $active = 0;
    /**
     * The writing direction of the menu ('ltr' or 'rtl').
     * @var string
     * @since 1.0 
     */
    private $dir;
    
    public function __construct($dir=Page::DIR_LTR) {
        $this->dir = $dir;
    }
    
    public function getDir(){
        return $this->dir;
    }
    
    public function setActive($active){
        $count = count($this->links);
        if($active > -1 && $active < $count){
            $this->active = $active;
        }
    }
    
    public function addLink($href,$label='Link Label'){
        $link = new Link($href, $label);
        array_push($this->links, $link);
    }
    
    public function __toString() {
        $tag = new HTMLTag(4);
        $tag->openTag('<aside dir="'.$this->getDir().'" id="side-navigation" itemscope itemtype="http://schema.org/WPSideBar">');
        $tag->openTag('<nav>');
        $tag->openTag('<ul>');
        $count = count($this->links);
        for($i = 0 ; $i < $count ; $i++){
            if($i == $this->active){
                $tag->content('<li class="pa-active-side-link">'.$this->links[$i].'</li>');
            }
            else{
                $tag->content('<li>'.$this->links[$i].'</li>');
            }
        }
        $tag->closeTag('</ul>');
        $tag->closeTag('</nav>');
        $tag->closeTag('</aside>');
        return $tag.'';
    }
}

