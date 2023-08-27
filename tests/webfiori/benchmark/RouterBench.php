<?php
namespace webfiori\benchmark;

use webfiori\framework\router\Router;

/**
 * Description of RouterBench
 *
 * @author I.BINALSHIKH
 */
class RouterBench {
    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchAddRoute() {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
            define('APP_DIR','app');
        }
        Router::closure([
            'path' => 'hello',
            'route-to' => function ()
            {
            }
        ]);
    }
}
