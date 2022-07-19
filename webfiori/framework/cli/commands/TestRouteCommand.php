<?php
namespace webfiori\framework\cli\commands;

use webfiori\cli\CLICommand;
use webfiori\framework\router\Router;
/**
 * A CLI Command which is used to test the result of routing to a specific 
 * route.
 *
 * @author Ibrahim
 */
class TestRouteCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--route'. In addition to that, 
     * it will have the following arguments:
     * <ul>
     * <li><b>url</b>: The URL at which its route will be tested.</li>
     * </ul>
     */
    public function __construct() {
        parent::__construct('route', [
            '--url' => [
                'optional' => false,
                'description' => 'The URL that will be tested if it has a '
                .'route or not.'
            ]
        ], 'Test the result of routing to a URL');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $url = $this->getArgValue('--url');
        $this->println("Trying to route to \"".$url."\"...");
        Router::route($url);

        return 0;
    }
}
