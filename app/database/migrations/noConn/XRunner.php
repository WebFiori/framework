<?php
namespace app\database\migrations\noConn;

use WebFiori\Database\Schema\SchemaRunner;


class XRunner extends SchemaRunner {
    
    public function __construct() {
        parent::__construct(null);
    }
}
