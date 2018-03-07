<?php
/**
 * A class that represents HTML tag.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class HTMLTag{
    /**
     * An array that will hold the tags as a text.
     * @var array 
     */
    private $html = array();
    /**
     * The indentation space that is used to make the tags well formated.
     * @var string 
     */
    private $tapSpace;
    /**
     * A number that represents the number of open tags (0 no tag. 1 one tag. 2 
     * is 2 tags and so on). It is also used as tab indicator.
     * @var int 
     */
    private $tabCount = 0;
    /**
     * Constructs a new tags holder.
     * @param int $tabCount Number of initial tabs (a number greater than -1). 
     * If a negative number is given, 0 will be used.
     * @param int $tabSpacesCount Number of spaces in a tab. It must be a number 
     * between 1 and 8 inclusive. If the given number is out of the range, 4 will be used.
     */
    public function __construct($tabCount=0,$tabSpacesCount=4) {
        if($tabCount > -1){
            $this->tabCount = $tabCount;
        }
        else{
            $this->tabCount = 0;
        }
        $this->tapSpace = '';
        if($tabSpacesCount > 0 && $tabSpacesCount < 9){
            for($x = 0 ; $x < $tabSpacesCount ; $x++){
                $this->tapSpace .= ' ';
            }
        }
        else{
            for($x = 0 ; $x < 4 ; $x++){
                $this->tapSpace .= ' ';
            }
        }
    }
    /**
     * Opens a tag.
     * @param string $tag The tag to open (e.g. &lt;div&gt;).
     */
    public function openTag($tag='<div>'){
        array_push($this->html, $this->getTab().$tag);
        $this->newLine();
        $this->addTab();
    }
    /**
     * Push a normal text inside a tag.
     * @param string $content The text to push.
     */
    public function content($content = 'A text'){
        array_push($this->html, $this->getTab().$content);
        $this->newLine();
    }
    /**
     * Closes a tag.
     * @param string $tag The tag to open (e.g. &lt;div&gt;).
     */
    public function closeTag($tag='</div>'){
        $this->reduceTab();
        array_push($this->html, $this->getTab().$tag);
        $this->newLine();
    }
    /**
     * Convert the tag content into HTML string.
     * @return string HTML string.
     */
    public function __toString() {
        $retVal = '';
        for($i = 0 ; $i<count($this->html) ; $i++){
            if($i == 0){
                $retVal .= trim($this->html[$i]);
            }
            else{
                $retVal .= $this->html[$i];
            }
        }
        return $retVal;
    }
    
    /**
     * Increase tab size by 1.
     */
    public function addTab(){
        $this->tabCount += 1;
    }
    
    /**
     * Reduce tab size by 1.
     * If the tab size is 0, it will not reduce it more.
     */
    public function reduceTab(){
        if($this->tabCount > 0){
            $this->tabCount -= 1;
        }
    }
    
    /**
     * Add '\n' (new line character) to the array.
     */
    private function newLine(){
        array_push($this->html, "\n");
    }
    /**
     * Returns the currently used tag space. 
     * @return string
     */
    private function getTab(){
        if($this->tabCount == 0){
            return '';
        }
        else{
            $tab = '';
            for($i = 0 ; $i < $this->tabCount ; $i++){
                $tab .= $this->tapSpace;
            }
            return $tab;
        }
    }
    /**
     * Returns the array that contains the pushed tags.
     * @return array
     */
    public function getArray(){
        return $this->html;
    }
}

