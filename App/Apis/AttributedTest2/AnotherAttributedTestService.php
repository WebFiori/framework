<?php
namespace App\Apis\AttributedTest2;

use WebFiori\Http\AbstractWebService;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RestController;

#[RestController('attributed-service-2', 'Another attributed service')]
class AnotherAttributedTestService extends AbstractWebService {
    #[PostMapping]
    public function processRequest() {
    }
}
