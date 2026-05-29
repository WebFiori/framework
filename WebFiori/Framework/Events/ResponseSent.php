<?php

namespace WebFiori\Framework\Events;

use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Dispatched after the HTTP response is sent.
 */
class ResponseSent {
    public function __construct(
        public readonly Request $request,
        public readonly Response $response,
        public readonly float $durationMs
    ) {
    }
}
