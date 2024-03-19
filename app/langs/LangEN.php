<?php
namespace app\langs;

use webfiori\framework\Lang;
/**
 * A class which holds language information for the language which has code 'EN'.
 */
class LangEN extends Lang {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('ltr', 'EN', true);
        //TODO: Add the language "EN" labels.
        $this->createAndSet('hello', [
            'one' => [
                'cool' => 'Cool'
            ],
            'two' => '2'
        ]);
    }
}
