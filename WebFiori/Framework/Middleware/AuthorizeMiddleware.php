<?php

namespace WebFiori\Framework\Middleware;

use WebFiori\Framework\Access;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Middleware that checks if the current user has a specific permission.
 *
 * Returns 401 if no user in session, 403 if user lacks the permission.
 */
class AuthorizeMiddleware extends AbstractMiddleware {
    private string $permission;

    /**
     * Creates new instance.
     *
     * @param string $permission The permission required to access the route.
     */
    public function __construct(string $permission) {
        parent::__construct('authorize');
        $this->permission = $permission;
        $this->setPriority(30000);
    }
    /**
     * Returns the permission this middleware checks.
     *
     * @return string
     */
    public function getPermission(): string {
        return $this->permission;
    }
    /**
     * Declares dependency on session middleware.
     *
     * @return array
     */
    public function getDependencies(): array {
        return ['start-session'];
    }

    public function before(Request $request, Response $response) {
        $user = SessionsManager::get('user');

        if ($user === null) {
            $response->setCode(401);
            $response->addHeader('Content-Type', 'application/json');
            $response->write(json_encode(['message' => 'Unauthorized']));

            return;
        }

        if (!Access::can($user, $this->permission)) {
            $response->setCode(403);
            $response->addHeader('Content-Type', 'application/json');
            $response->write(json_encode(['message' => 'Forbidden']));
        }
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }
}
