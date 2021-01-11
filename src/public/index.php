<?php
namespace webfiori;

use webfiori\framework\cli\CLI;
use webfiori\framework\router\Router;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\WebFiori;
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
        /**
         * The root directory that is used to load all other required system files.
         */
        if (!defined('ROOT_DIR')) {
            $publicFolder = DIRECTORY_SEPARATOR.'public';

            if (substr(__DIR__, strlen(__DIR__) - strlen($publicFolder)) == $publicFolder) {
                //HTTP run
                define('ROOT_DIR', substr(__DIR__,0, strlen(__DIR__) - strlen(DIRECTORY_SEPARATOR.'public')));
            } else {
                //CLI run
                define('ROOT_DIR', __DIR__);
            }
        }
        require_once ROOT_DIR.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'WebFiori.php';
        /**
         * This where magic will start.
         * 
         * Planting application seed into the ground and make your work bloom.
         */
        WebFiori::getAndStart();

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
     * Creates a single instance of the class.
     */
    public static function create() {
        if (self::$instance === null) {
            self::$instance = new Index();
        }
    }
}
Index::create();
