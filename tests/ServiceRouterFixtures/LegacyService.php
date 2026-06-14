<?php
namespace WebFiori\Tests\ServiceRouterFixtures;

use WebFiori\Http\WebService;

class LegacyService extends WebService {
    public function __construct() {
        parent::__construct('legacy');
    }

    public function processRequest() {
    }
}
