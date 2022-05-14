<?php
namespace webfiori\framework\handlers;

use webfiori\error\AbstractExceptionHandler;
use webfiori\framework\ui\ServerErrView;
/**
 * Description of HTTPExceptionHandler
 *
 * @author Ibrahim
 */
class HTTPExceptionHandler  extends AbstractExceptionHandler {
    
    public function handle() {
        $exceptionView = new ServerErrView($ex, $useResponsClass);
        $exceptionView->show(500);
    }

}
