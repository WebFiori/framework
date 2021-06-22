<?php

namespace app\langs;

use webfiori\framework\i18n\Language;

/**
 * A class which holds language information for the language which has code 'AR'.
 */
class LanguageAR extends Language {
    /**
     * Creates new instance of the class.
     */
    public function __construct(){
        parent::__construct('rtl', 'AR', true);
        //TODO: Add the language "AR" labels.
    }
}
