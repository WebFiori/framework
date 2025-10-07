<?php
namespace WebFiori;

use Exception;
use WebFiori\Framework\App;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\http\Request;
use WebFiori\http\Response;
/**
 * The name of the directory at which the developer will have his own application
 * code.
 *
 * @since 2.3.0
 */
define('APP_DIR', 'App');


/**
 * The entry point of all requests.
 *
 * @author Ibrahim
 */
class Index {
    private static $instance;
    private function __construct() {
        $DS = DIRECTORY_SEPARATOR;
        /**
         * The root directory that is used to load all other required system files.
         */
        if (!defined('ROOT_PATH')) {
            $publicFolder = $DS.'public';

            if (substr(__DIR__, strlen(__DIR__) - strlen($publicFolder)) == $publicFolder) {
                //HTTP run
                define('ROOT_PATH', substr(__DIR__,0, strlen(__DIR__) - strlen($DS.'public')));
            } else {
                //CLI run
                define('ROOT_PATH', __DIR__);
            }
        }
        $this->loadAppClass();
        /**
         * This where magic will start.
         *
         * Planting application seed into the ground and make your work bloom.
         */
        App::start();

        if (App::getRunner()->isCLI() === true) {
            App::getRunner()->start();
        } else {
            //route user request.
            SessionsManager::start('wf-session');
            Router::route(Request::getRequestedURI());
            Response::send();
        }
    }
    /**
     * Creates a single instance of the class.
     */
    public static function create() {
        if (self::$instance === null) {
            self::$instance = new Index();
        }
    }
    /**
     * Try to load the class 'App'.
     *
     * @throws Exception
     */
    private function loadAppClass() {
        $DS = DIRECTORY_SEPARATOR;
        $frameworkPath = ROOT_PATH.$DS.'WebFiori'.$DS.'framework';
        $corePath = $frameworkPath;
        $rootClass = $DS.'App.php';

        if (file_exists($corePath.$rootClass)) {
            define('WF_CORE_PATH', $corePath);
            require_once $corePath.$rootClass;
        } else {
            throw new Exception('Unable to locate the class "App".');
        }
    }
}
Index::create();
