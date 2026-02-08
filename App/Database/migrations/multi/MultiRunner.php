<?php
namespace App\Database\Migrations\Multi;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Schema\SchemaRunner;


class MultiRunner extends SchemaRunner {
    public function __construct() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        parent::__construct($conn);
        $this->register(Migration000::class);
        $this->register(Migration001::class);
        $this->register(Migration002::class);
    }
}
