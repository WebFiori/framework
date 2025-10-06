<?php
namespace app\database\migrations\emptyRunner;

use WebFiori\Database\Schema\SchemaRunner;
use webfiori\framework\App;

class XRunner extends SchemaRunner {
    
    public function __construct() {
        $conn = App::getConfig()->getDBConnection('default-conn');
        parent::__construct(null);
    }
}
