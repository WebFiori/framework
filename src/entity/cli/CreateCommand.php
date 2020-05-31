<?php
namespace webfiori\entity\cli;

/**
 * A command which is used to automate some of the common tasks such as 
 * creating query classes or controllers.
 *
 * @author Ibrahim
 * @version 1.0
 */
class CreateCommand extends CLICommand {
    
    public function __construct() {
        parent::__construct('create', [], 'Creates a query class, entity, API or a controller');
    }
    public function exec(): int {
        $options = [
            'Query class.',
            'Entity from query class.',
            'Controller which is linked with a query class.',
            'Set of web services.',
            'Nothing.'
        ];
        $answer = $this->select('What would you like to create?', $options, 4);
        if ($answer == 'Nothing.') {
            return 0;
        } else {
            
        }
    }

}
