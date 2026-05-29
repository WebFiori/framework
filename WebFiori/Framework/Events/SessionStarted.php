<?php

namespace WebFiori\Framework\Events;

use WebFiori\Framework\Session\Session;

/**
 * Dispatched when a session is started or resumed.
 */
class SessionStarted {
    public function __construct(
        public readonly Session $session
    ) {
    }
}
