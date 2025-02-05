<?php
namespace app\database\migrations\multiErr;

use webfiori\database\ConnectionInfo;
use webfiori\database\migration\MigrationsRunner;
use const APP_PATH;
use const DS;


class MultiErrRunner extends MigrationsRunner {
    
    public function __construct() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        parent::__construct(APP_PATH.'database'.DS.'migrations'.DS.'multiErr', '\\app\\database\\migrations\\multiErr', $conn);
    }
}
