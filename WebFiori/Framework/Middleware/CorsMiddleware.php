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
 * Middleware that handles Cross-Origin Resource Sharing (CORS) headers.
 *
 * Handles preflight OPTIONS requests and adds CORS headers to actual responses.
 * Can be configured per-route via constructor options.
 */
class CorsMiddleware extends AbstractMiddleware {
    /**
     * @var array Allowed origins.
     */
    private array $origins;
    /**
     * @var array Allowed HTTP methods.
     */
    private array $methods;
    /**
     * @var array Allowed request headers.
     */
    private array $headers;
    /**
     * @var int Max-Age for preflight cache in seconds.
     */
    private int $maxAge;
    /**
     * @var bool Whether to allow credentials.
     */
    private bool $credentials;
    /**
     * @var array Headers to expose to the client.
     */
    private array $exposeHeaders;

    /**
     * Creates new instance of the middleware.
     *
     * @param array $options Configuration options:
     *   - 'origins': array of allowed origins (default: ['*'])
     *   - 'methods': array of allowed methods (default: ['GET','POST','PUT','DELETE','PATCH','OPTIONS'])
     *   - 'headers': array of allowed headers (default: ['Content-Type','Authorization','X-CSRF-TOKEN'])
     *   - 'max-age': int preflight cache seconds (default: 86400)
     *   - 'credentials': bool allow credentials (default: false)
     *   - 'expose-headers': array of headers to expose (default: [])
     */
    public function __construct(array $options = []) {
        parent::__construct('cors');
        $this->setPriority(45000);
        $this->origins = $options['origins'] ?? ['*'];
        $this->methods = $options['methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        $this->headers = $options['headers'] ?? ['Content-Type', 'Authorization', 'X-CSRF-TOKEN'];
        $this->maxAge = $options['max-age'] ?? 86400;
        $this->credentials = $options['credentials'] ?? false;
        $this->exposeHeaders = $options['expose-headers'] ?? [];
    }
    /**
     * Handle preflight OPTIONS requests and validate origin.
     */
    public function before(Request $request, Response $response) {
        $origin = $this->getRequestOrigin($request);

        if ($origin === null) {
            return;
        }

        if (!$this->isOriginAllowed($origin)) {
            return;
        }

        $this->addCorsHeaders($response, $origin);

        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            $response->setCode(204);
            $response->addHeader('Access-Control-Allow-Methods', implode(', ', $this->methods));
            $response->addHeader('Access-Control-Allow-Headers', implode(', ', $this->headers));
            $response->addHeader('Access-Control-Max-Age', (string) $this->maxAge);
            $response->send();
            exit;
        }
    }
    /**
     * Add CORS headers to actual responses.
     */
    public function after(Request $request, Response $response) {
        $origin = $this->getRequestOrigin($request);

        if ($origin === null || !$this->isOriginAllowed($origin)) {
            return;
        }

        $this->addCorsHeaders($response, $origin);

        if (!empty($this->exposeHeaders)) {
            $response->addHeader('Access-Control-Expose-Headers', implode(', ', $this->exposeHeaders));
        }
    }

    public function afterSend(Request $request, Response $response) {
    }
    /**
     * Returns the configured allowed origins.
     *
     * @return array
     */
    public function getOrigins(): array {
        return $this->origins;
    }
    /**
     * Returns the configured allowed methods.
     *
     * @return array
     */
    public function getMethods(): array {
        return $this->methods;
    }
    /**
     * Returns the configured allowed headers.
     *
     * @return array
     */
    public function getHeaders(): array {
        return $this->headers;
    }
    /**
     * Returns the max-age value.
     *
     * @return int
     */
    public function getMaxAge(): int {
        return $this->maxAge;
    }
    /**
     * Returns whether credentials are allowed.
     *
     * @return bool
     */
    public function isCredentialsAllowed(): bool {
        return $this->credentials;
    }
    /**
     * Adds the core CORS headers to the response.
     *
     * @param Response $response The response object.
     * @param string $origin The request origin.
     */
    private function addCorsHeaders(Response $response, string $origin): void {
        if (in_array('*', $this->origins) && !$this->credentials) {
            $response->addHeader('Access-Control-Allow-Origin', '*');
        } else {
            $response->addHeader('Access-Control-Allow-Origin', $origin);
            $response->addHeader('Vary', 'Origin');
        }

        if ($this->credentials) {
            $response->addHeader('Access-Control-Allow-Credentials', 'true');
        }
    }
    /**
     * Extracts the Origin header from the request.
     *
     * @param Request $request The request.
     *
     * @return string|null The origin or null if not present.
     */
    private function getRequestOrigin(Request $request): ?string {
        $headers = $request->getHeadersAssoc();

        return $headers['origin'][0] ?? $headers['Origin'][0] ?? null;
    }
    /**
     * Checks if the given origin is in the allowed list.
     *
     * @param string $origin The origin to check.
     *
     * @return bool
     */
    private function isOriginAllowed(string $origin): bool {
        if (in_array('*', $this->origins)) {
            return true;
        }

        return in_array($origin, $this->origins);
    }
}
