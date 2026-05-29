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

use WebFiori\Cache\CacheFacade;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Middleware that limits the number of requests per time window.
 *
 * Uses the cache library as storage backend. Default key strategy:
 * session ID + client IP (if session active), or client IP only.
 */
class RateLimitMiddleware extends AbstractMiddleware {
    /**
     * @var int Maximum requests allowed per window.
     */
    private int $maxRequests;
    /**
     * @var int Time window in seconds.
     */
    private int $windowSeconds;
    /**
     * @var callable|null Custom key resolver.
     */
    private $keyResolver;
    /**
     * @var array IPs that bypass rate limiting.
     */
    private array $trustedIps;

    /**
     * Creates new instance of the middleware.
     *
     * @param int $maxRequests Maximum requests per window (default: 60).
     * @param int $windowSeconds Time window in seconds (default: 60).
     * @param callable|null $keyResolver Custom function to generate the rate limit key.
     *   Receives Request and must return a string. If null, uses session+IP or IP.
     * @param array $trustedIps Array of IPs that bypass rate limiting.
     */
    public function __construct(
        int $maxRequests = 60,
        int $windowSeconds = 60,
        ?callable $keyResolver = null,
        array $trustedIps = []
    ) {
        parent::__construct('rate-limit');
        $this->setPriority(50000);
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->keyResolver = $keyResolver;
        $this->trustedIps = $trustedIps;
    }
    /**
     * Returns the dependencies of this middleware.
     *
     * @return array
     */
    public function getDependencies(): array {
        return ['start-session'];
    }
    /**
     * Check rate limit before processing the request.
     */
    public function before(Request $request, Response $response) {
        if (in_array($request->getClientIP(), $this->trustedIps)) {
            return;
        }

        $key = $this->resolveKey($request);
        $cacheKey = 'rate_limit:'.$key;

        $current = CacheFacade::get($cacheKey);

        if ($current === null) {
            CacheFacade::set($cacheKey, 1, $this->windowSeconds);
            $current = 1;
        } else {
            $current++;
            CacheFacade::set($cacheKey, $current, $this->windowSeconds, true);
        }

        $remaining = max(0, $this->maxRequests - $current);
        $resetTime = time() + $this->windowSeconds;

        $response->addHeader('X-RateLimit-Limit', (string) $this->maxRequests);
        $response->addHeader('X-RateLimit-Remaining', (string) $remaining);
        $response->addHeader('X-RateLimit-Reset', (string) $resetTime);

        if ($current > $this->maxRequests) {
            $response->setCode(429);
            $response->addHeader('Retry-After', (string) $this->windowSeconds);
            $response->addHeader('Content-Type', 'application/json');
            $response->write(json_encode([
                'message' => 'Too many requests.',
                'retry_after' => $this->windowSeconds,
            ]));
            $response->send();
            exit;
        }
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }
    /**
     * Resolves the rate limit key for the current request.
     *
     * @param Request $request The current request.
     *
     * @return string The rate limit key.
     */
    private function resolveKey(Request $request): string {
        if ($this->keyResolver !== null) {
            return ($this->keyResolver)($request);
        }

        $session = SessionsManager::getActiveSession();

        if ($session !== null) {
            return $session->getId().'|'.$request->getClientIP();
        }

        return $request->getClientIP();
    }
}
