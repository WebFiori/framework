<?php
namespace webfiori\framework\handlers;

use webfiori\error\AbstractExceptionHandler;
use webfiori\json\Json;
use webfiori\http\Response;
/**
 * Exceptions handler which is used to handle exceptions in case of API call.
 *
 * @author Ibrahim
 */
class APICallExceptionHandler extends AbstractExceptionHandler {
    /**
     * Handles the exception
     */
    public function handle() {
        $j = new Json([
            'message' => '500 - Server Error: Uncaught Exception.',
            'type' => 'error',
            'exception-class' => get_class($this->getException()),
            'exception-message' => $this->getMessage(),
            'exception-code' => $this->getException()->getCode(),
            'line' => $this->getLine()
        ]);
        $stackTrace = new Json();
        $index = 0;
        
        foreach ($this->getTrace() as $traceEntry) {
            $traceEntry instanceof \webfiori\error\TraceEntry;
            $stackTrace->add('#'.$index,$traceEntry->getClass().' (Line '.$traceEntry->getClass().')');
            $index++;
        }
        $j->add('stack-trace',$stackTrace);
        Response::clear();
        Response::setCode(500);
        Response::write($j);
        Response::send();
    }

}
