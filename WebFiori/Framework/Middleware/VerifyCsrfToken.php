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

use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Middleware that provides CSRF (Cross-Site Request Forgery) protection.
 *
 * Generates a token per session and validates it on state-changing requests
 * (POST, PUT, DELETE, PATCH). Token is checked from X-CSRF-TOKEN header
 * or _csrf request parameter.
 *
 * For server-rendered pages, the middleware auto-injects a meta tag in the
 * response head for JavaScript access.
 */
class VerifyCsrfToken extends AbstractMiddleware {
    private const TOKEN_SESSION_KEY = '_csrf_token';
    private const TOKEN_PARAM_NAME = '_csrf';
    private const TOKEN_HEADER_NAME = 'x-csrf-token';
    /**
     * @var array HTTP methods that are exempt from CSRF validation.
     */
    private static array $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
    /**
     * @var array Route paths to exclude from CSRF validation.
     */
    private array $excludedPaths = [];

    public function __construct() {
        parent::__construct('csrf');
        $this->setPriority(40000);
        $this->addToGroup('web');
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
     * Add paths to exclude from CSRF validation.
     *
     * @param array $paths Array of path strings (e.g., ['/webhook', '/stripe/callback']).
     */
    public function setExcludedPaths(array $paths): void {
        $this->excludedPaths = $paths;
    }
    /**
     * Returns excluded paths.
     *
     * @return array
     */
    public function getExcludedPaths(): array {
        return $this->excludedPaths;
    }
    /**
     * Validate CSRF token on state-changing requests.
     */
    public function before(Request $request, Response $response) {
        $this->ensureTokenExists();

        if (in_array($request->getMethod(), self::$safeMethods)) {
            return;
        }

        if ($this->isExcluded($request)) {
            return;
        }

        $sessionToken = SessionsManager::get(self::TOKEN_SESSION_KEY);
        $requestToken = $this->getTokenFromRequest($request);

        if ($sessionToken === null || $requestToken === null || !hash_equals($sessionToken, $requestToken)) {
            $response->setCode(403);
            $response->addHeader('Content-Type', 'application/json');
            $response->write(json_encode(['message' => 'CSRF token mismatch.']));
        }
    }
    /**
     * Inject CSRF meta tag into HTML responses for JavaScript access.
     */
    public function after(Request $request, Response $response) {
        $token = SessionsManager::get(self::TOKEN_SESSION_KEY);

        if ($token === null) {
            return;
        }

        $body = $response->getBody();

        // Inject meta tag before </head> if present
        if (stripos($body, '</head>') !== false) {
            $meta = '<meta name="csrf-token" content="'.$token.'">';
            $body = str_ireplace('</head>', $meta."\n</head>", $body);
            $response->clearBody();
            $response->write($body);
        }
    }

    public function afterSend(Request $request, Response $response) {
    }
    /**
     * Returns the current CSRF token (useful for embedding in forms manually).
     *
     * @return string|null
     */
    public static function getToken(): ?string {
        return SessionsManager::get(self::TOKEN_SESSION_KEY);
    }
    /**
     * Ensures a CSRF token exists in the session.
     */
    private function ensureTokenExists(): void {
        if (SessionsManager::get(self::TOKEN_SESSION_KEY) === null) {
            SessionsManager::set(self::TOKEN_SESSION_KEY, bin2hex(random_bytes(32)));
        }
    }
    /**
     * Extracts the CSRF token from the request (header or parameter).
     *
     * @param Request $request The current request.
     *
     * @return string|null
     */
    private function getTokenFromRequest(Request $request): ?string {
        // Check header first
        $headers = $request->getHeadersAssoc();
        $headerToken = $headers[self::TOKEN_HEADER_NAME][0] ?? null;

        if ($headerToken !== null) {
            return $headerToken;
        }

        // Check request parameter
        $paramToken = $request->getParam(self::TOKEN_PARAM_NAME);

        return $paramToken;
    }
    /**
     * Checks if the current request path is excluded from CSRF validation.
     *
     * @param Request $request The current request.
     *
     * @return bool
     */
    private function isExcluded(Request $request): bool {
        $path = $request->getUri()->getPath();

        foreach ($this->excludedPaths as $excluded) {
            if ($path === $excluded || str_starts_with($path, $excluded)) {
                return true;
            }
        }

        return false;
    }
}
