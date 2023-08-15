<?php
namespace app\langs;

use webfiori\framework\Lang;
/**
 * A class which holds language information for the language which has code 'FK'.
 */
class LangFK extends Lang {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('ltr', 'FK', true);
        //TODO: Add the language "FK" labels.
    }
}
