<?php
namespace webfiori\framework\handlers;

use webfiori\error\AbstractHandler;
use webfiori\framework\cron\Cron;
use webfiori\framework\cli\Runner;
use webfiori\framework\cli\CLICommand;
/**
 * Description of CLIExceptionHandler
 *
 * @author Ibrahim
 */
class CLIErrHandler  extends AbstractHandler {
    public function __construct() {
        parent::__construct();
        $this->setName('CLI Errors Handler');
    }
    public function handle() {
        $stream = CLI::getOutputStream();
        $stream->prints(CLICommand::formatOutput("Uncaught Exception\n", [
            'color' => 'red',
            'bold' => true,
            'blink' => true
        ]));
        $stream->prints(CLICommand::formatOutput('Exception Message: ', [
            'color' => 'yellow',
            'bold' => true,
        ]));
        $stream->prints($this->getMessage()."\n");
        $stream->prints("Exception Class: %s\n", get_class($this->getException()));
        $stream->prints("Exception Code: %s\n", $this->getException()->getCode());
        $stream->prints("Class: %s\n", $this->getClass());
        $stream->prints("Line: %s\n", $this->getLine());
        $stream->prints("Stack Trace:\n");
        $stream->prints($this->getException()->getTraceAsString());
        Cron::log("<Uncaught Exception>\n");
        Cron::log("Exception Message    : ".$this->getMessage()."\n");
        Cron::log("Exception Class      : ".get_class($this->getException())."\n");
        Cron::log("Class                 : ".$this->getClass()."\n");
        Cron::log("Line                 : ".$this->getLine()."\n");
        Cron::log("Stack Trace          : \n");
        $num = 0;

        foreach ($this->getTrace() as $arrEntry) {
            Cron::log($num.' Class '.$arrEntry->getClass().' line '.$arrEntry->getLine());
            $num++;
        }
    }

    public function isActive(): bool {
        return Runner::isCLI();
    }

    public function isShutdownHandler(): bool {
        return true;
    }

}
