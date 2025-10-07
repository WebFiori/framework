<?php
namespace App\Database\Migrations\NoConn;

use WebFiori\Database\Schema\SchemaRunner;


class XRunner extends SchemaRunner {
    
    public function __construct() {
        parent::__construct(null);
    }
}
