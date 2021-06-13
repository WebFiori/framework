<?php
namespace webfiori;

use Exception;
use webfiori\framework\cli\CLI;
use webfiori\framework\router\Router;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\WebFioriApp;
use webfiori\http\Request;
use webfiori\http\Response;
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
        if (!defined('ROOT_DIR')) {
            $publicFolder = $DS.'public';

            if (substr(__DIR__, strlen(__DIR__) - strlen($publicFolder)) == $publicFolder) {
                //HTTP run
                define('ROOT_DIR', substr(__DIR__,0, strlen(__DIR__) - strlen($DS.'public')));
            } else {
                //CLI run
                define('ROOT_DIR', __DIR__);
            }
        }
        $this->loadAppClass();
        /**
         * This where magic will start.
         * 
         * Planting application seed into the ground and make your work bloom.
         */
        WebFioriApp::start();

        if (CLI::isCLI() === true) {
            CLI::registerCommands();
            CLI::runCLI();
        } else {
            //route user request.
            SessionsManager::start('wf-session');
            Router::route(Request::getRequestedURL());
            Response::send();
        }
    }
    /**
     * Try to load the class 'WebFioriApp'.
     * 
     * @throws Exception
     */
    private function loadAppClass() {
        $DS = DIRECTORY_SEPARATOR;
        //Framework is served via downloaded dist
        $corePath = ROOT_DIR.$DS.'framework';
        $rootClass = $DS.'WebFioriApp.php';
        
        if (file_exists($corePath.$rootClass)) {
            define('WF_CORE_PATH', $corePath);
            require_once $corePath.$rootClass;
        } else {
            //Framework is served via composer dist
            $topPath = substr(ROOT_DIR, 0, strlen(ROOT_DIR) - strlen('/src'));
            $corePath = $topPath.$DS.'vendor'.$DS.'webfiori'.$DS.'core'.$DS.'src'.$DS.'webfiori'.$DS.'framework';
            $rootClass = $DS.'WebFioriApp.php';
            
            if (file_exists($corePath.$rootClass)) {
                define('WF_CORE_PATH', $corePath);
                require_once $corePath.$rootClass;
            } else {
                throw new Exception('Unable to locate the class "WebFioriApp".');
            }
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
}
Index::create();
