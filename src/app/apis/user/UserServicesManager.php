<?php
namespace app\apis;

use webfiori\framework\ExtendedWebServicesManager;
/**
 * A services manager which is used to manage user related APIs.
 *
 * @author Ibrahim
 */
class UserServicesManager extends ExtendedWebServicesManager {
    public function __construct() {
        parent::__construct();

        $this->addService(new AddUserService());
    }
}
