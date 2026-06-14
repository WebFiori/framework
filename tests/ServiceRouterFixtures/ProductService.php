<?php
namespace WebFiori\Tests\ServiceRouterFixtures;

use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController]
class ProductService extends WebService {
    public function __construct() {
        parent::__construct('products');
    }

    public function processRequest() {
    }
}
