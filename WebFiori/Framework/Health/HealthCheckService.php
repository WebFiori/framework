<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Health;

use WebFiori\Http\AbstractWebService;

/**
 * Web service that handles the health check endpoint.
 */
class HealthCheckService extends AbstractWebService {
    public function __construct() {
        parent::__construct('health-check');
        $this->addRequestMethod('GET');
    }
    /**
     * Process the health check request.
     */
    public function processRequest() {
        $result = HealthCheck::runAll();
        $code = $result['status'] === 'ok' ? 200 : 503;
        $this->getManager()->getResponse()->setCode($code);
        $this->send('application/json', json_encode($result));
    }
}
