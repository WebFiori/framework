<?php
namespace app\database\migrations\noConn;

use WebFiori\Database\Schema\SchemaRunner;


class XRunner extends MigrationsRunner {
    
    public function __construct() {
        parent::__construct(APP_PATH.'database'.DS.'migrations'.DS.'noConn', '\\app\\database\\migrations\\noConn', null);
    }
}
