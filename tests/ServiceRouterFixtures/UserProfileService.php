<?php
namespace WebFiori\Tests\ServiceRouterFixtures;

use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController(path: 'v2/users/profile')]
class UserProfileService extends WebService {
    public function __construct() {
        parent::__construct('user-profile');
    }

    public function processRequest() {
    }
}
