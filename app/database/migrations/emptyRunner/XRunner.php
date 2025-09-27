<?php
namespace app\database\migrations\emptyRunner;

use WebFiori\Database\Schema\SchemaRunner;


class XRunner extends MigrationsRunner {
    
    public function __construct() {
        parent::__construct(APP_PATH.'database'.DS.'migrations'.DS.'emptyRunner', '\\app\\database\\migrations\\emptyRunner', null);
    }
}
