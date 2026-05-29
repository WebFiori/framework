<?php

namespace WebFiori\Framework\Events;

use WebFiori\Http\Request;

/**
 * Dispatched when an HTTP request is received, before routing.
 */
class RequestReceived {
    public function __construct(
        public readonly Request $request
    ) {
    }
}
