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
 * Middleware that adds HTTP caching headers (ETag, Cache-Control) to GET responses.
 *
 * If the client sends an If-None-Match header matching the current ETag,
 * a 304 Not Modified response is returned with no body.
 */
class HttpCacheMiddleware extends AbstractMiddleware {
    /**
     * @var int Cache-Control max-age in seconds. 0 means no Cache-Control header.
     */
    private int $maxAge;
    /**
     * @var bool Whether the response can be cached by shared caches (CDNs).
     */
    private bool $isPublic;

    /**
     * Creates new instance of the middleware.
     *
     * @param array $options Configuration options:
     *   - 'max-age': int — Cache-Control max-age in seconds (default: 0, no header)
     *   - 'public': bool — If true, adds Cache-Control: public (default: false)
     */
    public function __construct(array $options = []) {
        parent::__construct('http-cache');
        $this->setPriority(50);
        $this->maxAge = $options['max-age'] ?? 0;
        $this->isPublic = $options['public'] ?? false;
    }
    /**
     * Sets the max-age value for Cache-Control header.
     *
     * @param int $seconds Max-age in seconds. 0 disables Cache-Control.
     */
    public function setMaxAge(int $seconds): void {
        $this->maxAge = $seconds;
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
     * Sets whether the response is publicly cacheable.
     *
     * @param bool $isPublic True for public (CDN-cacheable), false for private.
     */
    public function setPublic(bool $isPublic): void {
        $this->isPublic = $isPublic;
    }
    /**
     * Returns whether the response is publicly cacheable.
     *
     * @return bool
     */
    public function isPublic(): bool {
        return $this->isPublic;
    }

    public function before(Request $request, Response $response) {
    }
    /**
     * Adds ETag and Cache-Control headers to GET responses.
     *
     * If the client's If-None-Match header matches the ETag, returns 304.
     */
    public function after(Request $request, Response $response) {
        if ($request->getMethod() !== 'GET' || $response->getCode() !== 200) {
            return;
        }

        $body = $response->getBody();
        $etag = '"'.md5($body).'"';
        $response->addHeader('ETag', $etag);

        if ($this->maxAge > 0) {
            $visibility = $this->isPublic ? 'public' : 'private';
            $response->addHeader('Cache-Control', $visibility.', max-age='.$this->maxAge);
        }

        $headers = $request->getHeadersAssoc();
        $clientEtag = $headers['if-none-match'][0] ?? $headers['If-None-Match'][0] ?? null;

        if ($clientEtag !== null && trim($clientEtag) === $etag) {
            $response->setCode(304);
            $response->clearBody();
        }
    }

    public function afterSend(Request $request, Response $response) {
    }
}
