<?php
namespace webfiori\framework\cli\commands;

use webfiori\cli\CLICommand;
/**
 * Description of VersionCommand
 *
 * @author Ibrahim
 */
class VersionCommand extends CLICommand {
    public function __construct() {
        parent::__construct('v', [], 'Display framework version info.');
    }
    /**
     * Execute the command
     */
    public function exec() : int {
        $formattingOptions = ['color' => 'light-blue', 'bold' => true];
        $this->prints("Framework Version: ", $formattingOptions);
        $this->println(WF_VERSION);
        $this->prints("Release Date: ", $formattingOptions);
        $this->println(WF_RELEASE_DATE);
        $this->prints("Version Type: ", $formattingOptions);
        $this->println(WF_VERSION_TYPE);
        return 0;
    }
}
