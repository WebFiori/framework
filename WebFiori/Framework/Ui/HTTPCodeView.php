<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\UI;

use WebFiori\Framework\App;
use WebFiori\UI\HTMLNode;
/**
 * A basic view which is used to display HTTP error codes taken from
 * language file.
 *
 * @author Ibrahim
 */
class HTTPCodeView extends WebPage {
    /**
     * Creates new instance of the class.
     */
    public function __construct($errCode) {
        parent::__construct();
        $this->setTheme(App::getConfig()->getTheme());

        $this->setTitle($this->get("general/http-codes/$errCode/code").' - '.$this->get("general/http-codes/$errCode/type"));
        http_response_code(intval($this->get("general/http-codes/$errCode/code")));
        $h1 = new HTMLNode('h1');
        $h1->text($this->getTitle());
        $this->insert($h1);
        $hr = new HTMLNode('hr');
        $this->insert($hr);
        $paragraph = new HTMLNode('p');
        $paragraph->text($this->get("general/http-codes/$errCode/message"));
        $this->insert($paragraph);
    }
}
