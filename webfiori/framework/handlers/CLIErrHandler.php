<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2022 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\handlers;

use webfiori\cli\Formatter;
use webfiori\cli\Runner;
use webfiori\error\AbstractHandler;
use webfiori\framework\cron\Cron;
use webfiori\framework\WebFioriApp;
/**
 * Exceptions handler which is used to handle exceptions in case of running
 * CLI applications.
 * 
 *
 * @author Ibrahim
 */
class CLIErrHandler  extends AbstractHandler {
    /**
     * Creates new instance of the class.
     * 
     * This method will set the name of the handler to 'CLI Errors Handler'.
     */
    public function __construct() {
        parent::__construct();
        $this->setName('CLI Errors Handler');
    }
    /**
     * Handles the exception
     */
    public function handle() {
        $stream = WebFioriApp::getRunner()->getOutputStream();
        $stream->prints(Formatter::format("Uncaught Exception\n", [
            'color' => 'red',
            'bold' => true,
            'blink' => true
        ]));
        $stream->prints(Formatter::format('Exception Message: ', [
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
    /**
     * Checks if the handler is active or not.
     * 
     * The handler will be active only if the framework is running through terminal.
     * 
     * @return bool True if active. false otherwise.
     */
    public function isActive(): bool {
        if (WebFioriApp::getClassStatus() == WebFioriApp::STATUS_INITIALIZING) {
            return true;
        }
        
        return Runner::isCLI();
    }
    /**
     * Checks if the handler is a shutdown handler or not.
     * 
     * @return bool The method will always return true.
     */
    public function isShutdownHandler(): bool {
        return true;
    }

}
