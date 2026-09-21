<?php
namespace WebFiori\Framework\Middleware;

use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\ReadStrategy;
use WebFiori\Framework\Session\SessionManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * A middleware which is used to initialize sessions.
 */
class StartSessionMiddleware extends AbstractMiddleware {
    private ConflictStrategy $conflictStrategy;
    private SessionManager $manager;
    private ReadStrategy $readStrategy;
    private string $sessionName;
    private array $sessionOptions;
    /**
     * Creates new instance of the class.
     *
     * @param SessionManager|null $manager Optional session manager. Uses default if null.
     * @param ReadStrategy $readStrategy How the session reads values. Default: REALTIME.
     * @param ConflictStrategy $conflictStrategy How concurrent writes are resolved. Default: LAST_WRITE_WINS.
     */
    public function __construct(
        ?SessionManager $manager = null,
        ReadStrategy $readStrategy = ReadStrategy::REALTIME,
        ConflictStrategy $conflictStrategy = ConflictStrategy::LAST_WRITE_WINS
    ) {
        parent::__construct('start-session');
        $this->setPriority(PHP_INT_MAX);
        $this->addToGroup('web');
        $this->sessionName = 'wf-session';
        $this->sessionOptions = [];
        $this->manager = $manager ?? SessionsManager::getInstance();
        $this->readStrategy = $readStrategy;
        $this->conflictStrategy = $conflictStrategy;
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
        $this->manager->start($this->sessionName, $this->sessionOptions, $this->readStrategy, $this->conflictStrategy);
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
    public function setSessionName(string $name): void {
        $this->sessionName = $name;
    }
    /**
     * Sets session options that will be passed to SessionManager::start().
     *
     * @param array $options An associative array of session options.
     */
    public function setSessionOptions(array $options): void {
        $this->sessionOptions = $options;
    }
}
