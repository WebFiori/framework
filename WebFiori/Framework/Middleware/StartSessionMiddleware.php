<?php
namespace WebFiori\Framework\Middleware;

use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
/**
 * A middleware which is used to initialize sessions.
 */
class StartSessionMiddleware extends AbstractMiddleware {
    private string $sessionName;
    private array $sessionOptions;
    /**
     * Creates new instance of the class.
     *
     * By default, the middleware is part of the group 'web'.
     * The priority of the middleware is PHP_INT_MAX.
     */
    public function __construct() {
        parent::__construct('start-session');
        $this->setPriority(PHP_INT_MAX);
        $this->addToGroup('web');
        $this->sessionName = 'wf-session';
        $this->sessionOptions = [];
    }
    public function after(Request $request, Response $response) {
        $sessionsCookiesHeaders = SessionsManager::getCookiesHeaders();

        foreach ($sessionsCookiesHeaders as $headerVal) {
            $response->addHeader('set-cookie', $headerVal);
        }
    }

    public function afterSend(Request $request, Response $response) {
        SessionsManager::validateStorage();
    }

    public function before(Request $request, Response $response) {
        SessionsManager::start($this->sessionName, $this->sessionOptions);
    }
    /**
     * Returns the name of the session that will be started by the middleware.
     *
     * @return string
     */
    public function getSessionName(): string {
        return $this->sessionName;
    }
    /**
     * Returns session options array.
     *
     * @return array
     */
    public function getSessionOptions(): array {
        return $this->sessionOptions;
    }
    /**
     * Sets the name of the session that will be started by the middleware.
     *
     * @param string $name The name of the session.
     */
    public function setSessionName(string $name) {
        $this->sessionName = $name;
    }
    /**
     * Sets session options that will be passed to SessionsManager::start().
     *
     * Available options are:
     * - 'duration': Session duration in minutes. 0 means non-persistent.
     * - 'refresh': Boolean. If true, session timeout refreshes on each request.
     *
     * @param array $options An associative array of session options.
     */
    public function setSessionOptions(array $options) {
        $this->sessionOptions = $options;
    }
}
