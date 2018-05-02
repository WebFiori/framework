<?php
/**
 * A class that represents the content of the &lt;head&gt; tag.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class HeadTag extends HTMLTag{
    /**
     * An array that will contain any other type of head tag.
     * @var array 
     * @since 1.0
     */
    private $headerTags = array();
    /**
     * An array that will contain &lt;link&gt; tags.
     * @var array 
     * @since 1.0
     */
    private $linkTags = array();
    /**
     * An array that will contain &lt;script&gt; tags.
     * @var array 
     */
    private $jsScripts = array();
    /**
     * An array that will contain the meta &lt;alternant&gt; tags.
     * @var array 
     * @since 1.0
     */
    private $altURLs = array();
    /**
     * An array that will contain the meta &lt;keywords&gt; tags.
     * @var array 
     * @since 1.0
     */
    private $keywords = array();
    private $contactMail;
    private $copyright;
    private $canonical;
    private $baseURL;
    private $title;
    private $favIcon;
    private $author;
    private $desc;
    
    public function __construct() {
        parent::__construct(2);
        $this->themeColor = '#ccffcc';
        $this->setTitle('Default Title');
        $this->desc = 'Welcome to my web page.';
        //$this->addCSS('res/css/programming-academia.css');
    }
    /**
     * Sets the value for the meta tag 'description'.
     * @param string $desc The description of the page.
     * @since 1.0
     */
    public function setDescription($desc){
        if($desc != NULL){
            $this->desc = $desc;
        }
    }
    /**
     * Sets the value for the tag 'title'.
     * @param string $title The title of the page.
     * @since 1.0
     */
    public function setTitle($title){
        if($title != NULL){
            $this->title = $title;
        }
    }
    /**
     * Returns the value of the meta tag 'title'.
     * @return string The value of the meta tag 'title'.
     * @since 1.0
     */
    public function getTitle(){
        return $this->title;
    }
    
    public function __toString() {
        $this->build();
        return parent::__toString();
    }
    /**
     * Calling this method will add the default header tags.
     * It must be called before calling the method publish() and after adding 
     * any other tags.
     * @since 1.0
     */
    private function build(){
        array_push($this->headerTags, '<base href="'.$this->getBaseURL().'">');
        if($this->getTitle()){
            array_push($this->headerTags, '<title>'.$this->getTitle().'</title>');
        }
        $this->linkTags();
        $this->metaTags();
        $this->jsTags();
        foreach($this->headerTags as $tag){
            parent::content($tag);
        }
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
     * Process All JavaScript tags.
     * @since 1.0
     */
    private function jsTags(){
        $count = count($this->jsScripts);
        for($i = 0 ; $i < $count ; $i++){
            array_push($this->headerTags, $this->jsScripts[$i]);
        }
    }
    /**
     * Process All link tags.
     * @since 1.0
     */
    private function linkTags(){
        if($this->canonical){
            $this->addLinkTag('canonical', $this->canonical);
        }
        if($this->getFavIcon()){
            $this->addLinkTag('icon', $this->getFavIcon());
        }
        $count1 = count($this->altURLs);
        for($i = 0 ; $i < $count1 ; $i++){
            $alt = $this->altURLs[$i];
            $this->addLinkTag('alternate', $alt->getPath(), 'hreflang="'.$alt->getLang().'"');
        }
        $count = count($this->linkTags);
        for($i = 0 ; $i<$count ; $i++){
            array_push($this->headerTags, $this->linkTags[$i]);
        }
    }
    
    /**
     * Sets the base URL.
     * @param string $url The Base URL (Such as http://www.example.com).
     * @since 1.0
     */
    public function setBaseURL($url){
        $this->baseURL = $url;
    }
    
    /**
     * Returns the base URL.
     * @return string The Base URL (Such as http://www.example.com).
     * @since 1.0
     */
    public function getBaseURL(){
        return $this->baseURL;
    }
    
    /**
     * Gets the value of the property $themeColor
     * @return string A hex number as string (such as #ffffff)
     * @since 1.0
     */
    public function getThemeColor(){
        return $this->themeColor;
    }
    
    /**
     * Adds a CSS file to include in the header of the web page.
     * @param string $pathToCss A path to the CSS file.
     * @since 1.0
     */
    public function addCSS($pathToCss){
        $this->addLinkTag('stylesheet', $pathToCss);
    }
    
    /**
     * Adds a JS file to include in the header of the web page.
     * @param string $pathToJs A path to the javascript file.
     * @since 1.0
     */
    public function addJS($pathToJs){
        array_push($this->jsScripts, '<script type="text/javascript" src="'.$pathToJs.'"></script>');
    }
    
    /**
     * Sets the value of the link tag with the attribute rel='icon'.
     * @param string $favIconDir A path to an image file to set as favicon.
     * @since 1.0
     */
    public function setFavIcon($favIconDir){
        $this->favIcon = $favIconDir;
    }
    
    /**
     * Returns the value of the link tag with the attribute rel='icon'.
     * @return string A path to an image file.
     * @since 1.0
     */
    public function getFavIcon(){
        return $this->favIcon;
    }
    
    /**
     * Returns the canonical URL.
     * @return string
     * @since 1.0
     */
    public function getCanonical(){
        return $this->canonical;
    }
    
    /**
     * Adds a new alternate link of the page has more than one language.
     * @param string $file_path the path to the alternate version of the page.
     * @param string $lang the language code of the alternate version (en, ar, ...).
     * @param string $lang_name The language name (Arabic, English).
     * @since 1.0
     */
    public function addAlternateURL($file_path,$lang, $lang_name='unset'){
        $alternate = new AlternateURL();
        $alternate->setLang($lang);
        $alternate->setLangName($lang_name);
        $alternate->setPath($this->getBaseURL()."\\".$file_path);
        array_push($this->altURLs, $alternate);
    }
    /**
     * Returns the content of the meta tag with name='author'.
     * @return string
     * @since 1.0
     */
    public function getAuthor(){
        return $this->author;
    }
    
    /**
     * Sets the content of the meta tag with name='author'.
     * @param string $val The name of the author.
     * @since 1.0
     */
    public function setAuthor($val){
        $this->author = $val;
    }
    
    /**
     * Returns the content of the meta tag with name='copyright'.
     * @return string
     * @since 1.0
     */
    public function getCopyright(){
        return $this->copyright;
    }
    
    /**
     * Sets the content of the meta tag with name='copyright'.
     * @return string
     * @since 1.0
     */
    public function setCopyright($val){
        $this->copyright = $val;
    }
    
    /**
     * Returns the content of the meta tag with name='contact'.
     * @return string
     * @since 1.0
     */
    public function getContactMail(){
        return $this->contactMail;
    }
    
    /**
     * Sets the content of the meta tag with name='contact'.
     * @return string
     * @since 1.0
     */
    public function setContactMail($val){
        $this->contactMail = $val;
    }
    /**
     * Adds new keyword to the page keywords.
     * @param string $aWord The keyword to add.
     * @since 1.0
     */
    public function addKeyword($aWord){
        if($aWord){
            if($aWord != '' && !in_array($aWord, $this->keywords)){
                array_push($this->keywords, $aWord);
            }
        }
    }
    
    /**
     * Sets the canonical URL.
     * @param string $url
     * @since 1.0
     */
    public function setCanonical($url){
        $this->canonical = $url;
    }
    
    /**
     * This function is used to add a link tag in the header.
     * @param type $rel The relation ship type. It is the value of the attribute 
     * 'rel' of the link.
     * @param type $href The reference to the resource.
     * @since 1.0
     */
    private function addLinkTag($rel,$href,$otherAttriputes=''){
        array_push($this->linkTags, '<link rel="'.$rel.'" '.$otherAttriputes.' href="'.$href.'">');
    }
}