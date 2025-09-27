<?php
namespace app\database\migrations\multi;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Schema\SchemaRunner;
use const APP_PATH;
use const DS;


class MultiRunner extends MigrationsRunner {
    
    public function __construct() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        parent::__construct(APP_PATH.'database'.DS.'migrations'.DS.'multi', '\\app\\database\\migrations\\multi', $conn);
    }
}
