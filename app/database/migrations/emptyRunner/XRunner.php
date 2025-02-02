<?php
namespace app\database\migrations\emptyRunner;

use webfiori\database\migration\MigrationsRunner;


class XRunner extends MigrationsRunner {
    
    public function __construct() {
        parent::__construct(APP_PATH.'database'.DS.'migrations'.DS.'emptyRunner', '\\app\\database\\migrations\\emptyRunner', null);
    }
}
