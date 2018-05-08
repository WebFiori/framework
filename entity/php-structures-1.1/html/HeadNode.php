<?php
/**
 * A class that represents the content of the &lt;head&gt; tag.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class HeadNode extends HTMLNode{
    public function __construct() {
        parent::__construct('head',TRUE);
        $charset = new HTMLNode('meta', FALSE);
        $charset->setAttribute('charset', 'UTF-8');
        $this->addChild($charset);
        $tNode = new HTMLNode('title');
        $this->addChild($tNode);
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText('Page Title');
        $base = new HTMLNode('base', FALSE);
        $base->setAttribute('href', '');
        $this->addChild($base);
        $tNode->addChild($textNode);
        $this->addMeta('description', 'x');
        $this->addMeta('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
    }
    /**
     * Sets the value for the meta tag 'description'.
     * @param string $desc The description of the page.
     * @since 1.0
     */
    public function setDescription($desc){
        $this->getChildByAttributeValue('name', 'description')->setAttribute('content', $desc);
    }
    /**
     * Sets the value for the tag 'title'.
     * @param string $title The title of the page.
     * @since 1.0
     */
    public function setTitle($title){
        $chNodes = $this->childNodes();
        for($x = 0 ; $x < $chNodes->size() ; $x++){
            if($chNodes->get($x)->getName() == 'title'){
                $chNodes->get($x)->childNodes()->get(0)->setText($title);
            }
        }
    }
    /**
     * Adds new meta node to the head tag.
     * @param string $name The value of the attribute 'name' of the meta tag.
     * @param string $content The value of the attribute 'content' of the meta tag.
     * @since 1.1
     */
    public function addMeta($name,$content){
        $metaNode = new HTMLNode('meta', FALSE, FALSE);
        $metaNode->setAttribute('name', $name);
        $metaNode->setAttribute('content', $content);
        $this->addChild($metaNode);
    }
    
    /**
     * Include the default meta tags.
     * @since 1.0
     */
    private function metaTags(){
        array_push($this->headerTags, '<meta charset="UTF-8">');
        array_push($this->headerTags, '<meta name="description" content="'.$this->desc.'">');
        array_push($this->headerTags, '<meta name="generator" content="PA CMS '.Config::get()->getSysVersion().'">');
        array_push($this->headerTags, '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">');
        array_push($this->headerTags, '<meta name="theme-color" content="'.$this->getThemeColor().'">');
        array_push($this->headerTags, '<meta name="robots" content="index, follow">');
        if($this->getAuthor()){
            array_push($this->headerTags, '<meta name="author" content="'.$this->getAuthor().'">');
        }
        if($this->getContactMail()){
            array_push($this->headerTags, '<meta name="contact" content="'.$this->getContactMail().'">');
        }
        if($this->getCopyright()){
            array_push($this->headerTags, '<meta name="copyright" content="'.$this->getCopyright().'">');
        }
        $keywords = '';
        $count = count($this->keywords);
        if($count !== 0){
            for($i = 0 ; $i < $count ; $i++){
                if($i + 1 == $count){
                    $keywords .= $this->keywords[$i];
                }
                else{
                    $keywords .= $this->keywords[$i].',';
                }
            }
            array_push($this->headerTags, '<meta name="keywords" content="'.$keywords.'">');
        }
    }


    /**
     * Sets the base URL.
     * @param string $url The Base URL (Such as http://www.example.com).
     * @since 1.0
     */
    public function setBaseURL($url){
        $chNodes = $this->childNodes();
        for($x = 0 ; $x < $chNodes->size() ; $x++){
            if($chNodes->get($x)->getName() == 'base'){
                $chNodes->get($x)->setAttribute('href',$url);
            }
        }
    }
    /**
     * Adds a link node to include in the header of the web page.
     * @param string $pathToCss A path to the CSS file.
     * @since 1.1
     */
    public function addLink($rel,$href){
        $node = new HTMLNode('link',FALSE);
        $node->setAttribute('rel', $rel);
        $node->setAttribute('href', $href);
        $this->addChild($node);
    }
    /**
     * Adds a CSS file to include in the header of the web page.
     * @param string $pathToCss A path to the CSS file.
     * @since 1.0
     */
    public function addCSS($pathToCss){
        $node = new HTMLNode('link',FALSE);
        $node->setAttribute('rel', 'stylesheet');
        $node->setAttribute('href', $pathToCss);
        $this->addChild($node);
    }
    
    /**
     * Adds a JS file to include in the header of the web page.
     * @param string $pathToJs A path to the javascript file.
     * @since 1.0
     */
    public function addJS($pathToJs){
        $node = new HTMLNode('script', TRUE);
        $node->setAttribute('type', 'text/javascript');
        $node->setAttribute('src', $pathToJs);
        $this->addChild($node);
    }

    /**
     * Adds a new alternate link of the page has more than one language.
     * @param string $file_path the path to the alternate version of the page.
     * @param string $lang the language code of the alternate version (en, ar, ...).
     * @param string $lang_name The language name (Arabic, English).
     * @since 1.0
     */
    public function addAlternateURL($file_path,$lang){
        $node = new HTMLNode('link', FALSE);
        $node->setAttribute('rel', 'alternate');
        $node->setAttribute('hreflang', $lang);
        $node->setAttribute('href', $file_path);
        $this->addChild($node);
    }
}