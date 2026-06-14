<?php
namespace WebFiori\Tests\ServiceRouterFixtures\UserAuth;

use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController]
class LoginService extends WebService {
    public function __construct() {
        parent::__construct('login');
    }

    public function processRequest() {
    }
}
