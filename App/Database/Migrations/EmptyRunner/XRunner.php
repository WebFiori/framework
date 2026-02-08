<?php
namespace App\Database\Migrations\EmptyRunner;

use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\App;

class XRunner extends SchemaRunner {
    
    public function __construct() {
        $conn = App::getConfig()->getDBConnection('default-conn');
        parent::__construct(null);
    }
}
