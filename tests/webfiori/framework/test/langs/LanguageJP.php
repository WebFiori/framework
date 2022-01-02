<?php
namespace app\langs;
use webfiori\framework\i18n\Language;

class LanguageJP extends Language {
    public function __construct() {
        parent::__construct('ltr', 'JB', false);
    }
}
