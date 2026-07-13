<?php
namespace WebFiori\Tests\ServiceRouterFixtures;

use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController(name: 'login', path: 'auth/login')]
class AuthLoginService extends WebService {
    public function __construct() {
        parent::__construct('login');
    }

    public function processRequest() {
    }
}
