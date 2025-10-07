<?php
namespace Themes\FioriTheme;

use WebFiori\UI\HTMLNode;

/**
 * A class that represents the top section of the theme.
 */
class HeaderSection extends HTMLNode {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('div');
        //TODO: Add header components such as navigation links.
    }
}
