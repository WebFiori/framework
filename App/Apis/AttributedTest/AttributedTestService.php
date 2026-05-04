<?php
namespace App\Apis\AttributedTest;

use WebFiori\Http\AbstractWebService;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RestController;

#[RestController('attributed-service', 'A service registered via attribute')]
class AttributedTestService extends AbstractWebService {
    #[PostMapping]
    public function processRequest() {
    }
}
