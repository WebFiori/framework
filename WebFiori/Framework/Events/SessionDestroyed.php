<?php

namespace WebFiori\Framework\Events;

use WebFiori\Framework\Session\Session;

/**
 * Dispatched when a session is destroyed.
 */
class SessionDestroyed {
    public function __construct(
        public readonly Session $session
    ) {
    }
}
