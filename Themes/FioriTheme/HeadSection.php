<?php
namespace Themes\FioriTheme;

use WebFiori\Ui\HeadNode;

/**
 * A class that represents "head" tag of the theme.
 */
class HeadSection extends HeadNode {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct();
        //TODO: Include meta tags, CSS and JS files of the theme.
    }
}
