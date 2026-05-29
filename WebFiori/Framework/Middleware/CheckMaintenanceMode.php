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
namespace WebFiori\Framework\Middleware;

use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Middleware that checks if the application is in maintenance mode.
 *
 * If the file APP_PATH/Storage/.maintenance exists, all requests are
 * responded to with 503 Service Unavailable unless the client IP is
 * in the allowed list.
 */
class CheckMaintenanceMode extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('maintenance-check');
        $this->setPriority(PHP_INT_MAX - 1);
        $this->addToGroup('web');
        $this->addToGroup('api');
    }
    /**
     * Check maintenance mode before processing the request.
     */
    public function before(Request $request, Response $response) {
        $file = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'.maintenance';

        if (!file_exists($file)) {
            return;
        }

        $config = json_decode(file_get_contents($file), true) ?? [];
        $allowed = $config['allowed'] ?? [];

        if (in_array($request->getClientIP(), $allowed)) {
            return;
        }

        $message = $config['message'] ?? 'Application is under maintenance.';
        $retryAfter = $config['retry_after'] ?? 3600;
        $apiPrefix = $config['api_prefix'] ?? '/api';

        $response->setCode(503);
        $response->addHeader('Retry-After', $retryAfter);

        $isApi = str_starts_with($request->getUri()->getPath(), $apiPrefix)
              || $request->getHeader('accept') === 'application/json'
              || $request->getHeader('content-type') === 'application/json';

        if ($isApi) {
            $response->addHeader('Content-Type', 'application/json');
            $response->write(json_encode([
                'message' => $message,
                'retry_after' => $retryAfter,
            ]));
        } else {
            $customPage = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'maintenance.html';

            if (file_exists($customPage)) {
                $response->write(file_get_contents($customPage));
            } else {
                $response->addHeader('Content-Type', 'text/html');
                $response->write('<html><body><h1>Under Maintenance</h1><p>'.$message.'</p></body></html>');
            }
        }
        $response->send();
        exit;
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }
}
