<?php
namespace tests\apis\emptyService;

use webfiori\http\WebServicesManager;
/**
 *
 * @author Ibrahim
 */
class EmptyServicesManager extends WebServicesManager {
    public function __construct(string $version = '1.0.0') {
        parent::__construct($version);
    }
}
