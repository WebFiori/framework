<?php
namespace WebFiori\Benchmark;

use WebFiori\Framework\Router\Router;

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
            define('APP_DIR','App');
        }
        Router::closure([
            'path' => 'hello',
            'route-to' => function ()
            {
            }
        ]);
    }
}
