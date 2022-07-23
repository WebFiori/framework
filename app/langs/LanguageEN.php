<?php
namespace app\langs;

use webfiori\framework\Language;

/**
 * A class which holds language information for the language which has code 'EN'.
 */
class LanguageEN extends Language {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('ltr', 'EN', true);
    }
}
