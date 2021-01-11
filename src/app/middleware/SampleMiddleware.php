<?php
namespace webfiori\examples;

use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\http\Response;
/**
 * A sample middleware implementation.
 *
 * @author Ibrahim
 */
class SampleMiddleware extends AbstractMiddleware {
    public function __construct() {
        //Each middleware must have a unique name.
        parent::__construct('sample-middleware');

        //Set the priority to higher number to reach it first.
        $this->setPriority(0);

        //Add the middleware to the global middleware group
        $this->addToGroup('global');
    }
    public function after() {
        // A routine to execute after sending the response and before terminating 
        // The application.
    }

    public function afterSend() {
        // A routine to execute after terminating The application
    }

    public function before() {
        // A routine to execute before entering the application
        //Response::write('Terminate before reach app.');
        //Response::send();
    }
}
//Return namespace to auto-register the middleware
return __NAMESPACE__;
