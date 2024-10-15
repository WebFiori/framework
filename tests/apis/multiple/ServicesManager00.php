<?php
namespace tests\apis\multiple;

use webfiori\http\WebServicesManager;
/**
 *
 * @author Ibrahim
 */
class ServicesManager00 extends WebServicesManager {
    public function __construct(string $version = '1.0.0') {
        parent::__construct($version);
        $this->addService(new WebService00());
        $this->addService(new WebService01());
    }
}
