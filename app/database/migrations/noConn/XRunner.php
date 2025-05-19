<?php
namespace app\database\migrations\noConn;

use webfiori\database\migration\MigrationsRunner;


class XRunner extends MigrationsRunner {
    
    public function __construct() {
        parent::__construct(APP_PATH.'database'.DS.'migrations'.DS.'noConn', '\\app\\database\\migrations\\noConn', null);
    }
}
