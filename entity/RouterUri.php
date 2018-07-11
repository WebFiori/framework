<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RouterUri
 *
 * @author ibrah
 */
class RouterUri {
    private $routeTo;
    private $uriBroken;
    public function __construct($requestedUri,$routeTo) {
        $this->setRoute($routeTo);
        $this->uriBroken = Router::splitURI($requestedUri);
        Util::print_r($this->uriBroken);
    }
    
    public function setRoute($routeTo) {
        $this->routeTo = $routeTo;
    }
    
    public function getQueryString() {
        return $this->uriBroken['query-string'];
    }
    
    public function getRequestedUri($includeQueryString=false) {
        $retVal = $includeQueryString == FALSE ? $this->uriBroken['uri'] : $this->uriBroken['uri-without-query-string'];
        return $retVal;
    }
}
