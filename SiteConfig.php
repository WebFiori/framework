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
require_once 'root.php';
Util::displayErrors();
/**
 * A configuration file for the presentation part of the system (web pages)
 * 
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class SiteConfig{
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $webSiteName;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $description;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $titleSep;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $copyright;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $homePage;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $baseUrl;
    
    private function __construct() {
        $this->webSiteName = 'Programming Academia';
        $this->baseUrl = 'http://localhost/generic-php/';
        $this->titleSep = ' | ';
        $this->homePage = '<b style="color:red">&lt;Not Set&gt;</b>';
        $this->copyright = 'All rights reserved.';
        $this->description = '<b style="color:red">&lt;Not Set&gt;</b>';
    }
    /**
     * Returns the base URL that is used to fetch resources.
     * @return string the base URL.
     * @since 1.0
     */
    public function getBaseURL(){
        return $this->baseUrl;
    }
    private static $siteCfg;
    /**
     * Returns an instance of the configuration file.
     * @return SiteConfig
     * @since 1.0
     */
    public static function get(){
        if(self::$siteCfg != NULL){
            return self::$siteCfg;
        }
        self::$siteCfg = new SiteConfig();
        return self::$siteCfg;
    }
    /**
     * Returns the copyright notice of the website.
     * @return string The copyright notice of the website.
     * @since 1.0
     */
    public function getCopyright(){
        return $this->copyright;
    }
    /**
     * Returns the description of the website.
     * @return string The description of the website.
     * @since 1.0
     */
    public function getDesc(){
        return $this->description;
    }
    /**
     * Returns the character (or string) that is used to separate page title from website name.
     * @return string
     * @since 1.0
     */
    public function getTitleSep(){
        return $this->titleSep;
    }
    /**
     * Returns the home page name of the website.
     * @return string The home page name of the website.
     * @since 1.0
     */
    public function getHomePage(){
        return $this->homePage;
    }
    /**
     * Returns the name of the website.
     * @return string The name of the website.
     * @since 1.0
     */
    public function getWebsiteName(){
        return $this->webSiteName;
    }
    public function __toString() {
        $retVal = '<b>Website Configuration</b><br/>';
        $retVal .= 'Website Name: '.$this->getWebsiteName().'<br/>';
        $retVal .= 'Home Page: '.$this->getHomePage().'<br/>';
        $retVal .= 'Description: '.$this->getDesc().'<br/>';
        $retVal .= 'Title Separator: '.$this->getTitleSep().'<br/>';
        $retVal .= 'Copyright Notice: '.$this->getCopyright().'<br/><br/>';
        return $retVal;
    }
}
