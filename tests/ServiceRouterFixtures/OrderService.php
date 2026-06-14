<?php
namespace WebFiori\Tests\ServiceRouterFixtures;

use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController('orders')]
class OrderService extends WebService {
    public function __construct() {
        parent::__construct('orders');
    }

    public function processRequest() {
    }
}
