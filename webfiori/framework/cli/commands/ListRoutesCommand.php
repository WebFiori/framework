<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\cli\commands;

use WebFiori\Cli\Command;
use webfiori\framework\router\Router;
/**
 * A CLI command which is used to show a list of all added routes.
 *
 * @author Ibrahim
 */
class ListRoutesCommand extends Command {
    /**
     * Creates new instance of the class.
     * The command will have name '--list-routes'. This command
     * is used to list all registered routes and at which resource they
     * point to.
     */
    public function __construct() {
        parent::__construct('list-routes', [], 'List all created routes and which resource they point to.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $routesArr = Router::routes();
        $maxRouteLen = 0;

        foreach ($routesArr as $requestedUrl => $routeTo) {
            $len = strlen($requestedUrl);

            if ($len > $maxRouteLen) {
                $maxRouteLen = $len;
            }
        }
        $maxRouteLen += 4;

        foreach ($routesArr as $requestedUrl => $routeTo) {
            $location = $maxRouteLen - strlen($requestedUrl);
            $this->println("$requestedUrl %".$location."s $routeTo"," => ");
        }

        return 0;
    }
}
