<?php

namespace WebFiori\Framework\Middleware;

use WebFiori\Framework\Session\SessionManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * A middleware which is used to initialize sessions.
 */
class StartSessionMiddleware extends AbstractMiddleware {
    private string $sessionName;
    private array $sessionOptions;
    private SessionManager $manager;
    /**
     * Creates new instance of the class.
     *
     * By default, the middleware is part of the group 'web'.
     * The priority of the middleware is PHP_INT_MAX.
     *
     * @param SessionManager|null $manager Optional session manager instance.
     * If null, uses the default from SessionsManager facade.
     */
    public function __construct(?SessionManager $manager = null) {
        parent::__construct('start-session');
        $this->setPriority(PHP_INT_MAX);
        $this->addToGroup('web');
        $this->sessionName = 'wf-session';
        $this->sessionOptions = [];
        $this->manager = $manager ?? SessionsManager::getInstance();
    }
    /**
     * Returns the session manager used by this middleware.
     *
     * @return SessionManager
     */
    public function getManager(): SessionManager {
        return $this->manager;
    }
    /**
     * Sets the name of the session that will be started by the middleware.
     *
     * @param string $name The name of the session.
     */
    public function setSessionName(string $name): void {
        $this->sessionName = $name;
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
     * Sets session options that will be passed to SessionManager::start().
     *
     * @param array $options An associative array of session options.
     */
    public function setSessionOptions(array $options): void {
        $this->sessionOptions = $options;
    }
    /**
     * Returns session options array.
     *
     * @return array
     */
    public function getSessionOptions(): array {
        return $this->sessionOptions;
    }

    public function after(Request $request, Response $response) {
        foreach ($this->manager->getCookiesHeaders() as $headerVal) {
            $response->addHeader('set-cookie', $headerVal);
        }
    }

    public function afterSend(Request $request, Response $response) {
        $this->manager->validateStorage();
    }

    public function before(Request $request, Response $response) {
        $this->manager->start($this->sessionName, $this->sessionOptions);
    }
}
