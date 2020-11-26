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
        parent::__construct('sample-middleware');
        $this->setPriority(0);
    }
    public function after() {
        // A routine to execute after sending the response and before terminating 
        // The application.
    }

    public function afterTerminate() {
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