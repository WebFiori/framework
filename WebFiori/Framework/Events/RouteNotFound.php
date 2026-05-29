<?php

namespace WebFiori\Framework\Events;

use WebFiori\Http\Request;

/**
 * Dispatched when no route matches the requested URI.
 */
class RouteNotFound {
    public function __construct(
        public readonly Request $request
    ) {
    }
}
