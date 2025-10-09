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
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Command;
/**
 * Description of VersionCommand
 *
 * @author Ibrahim
 */
class VersionCommand extends Command {
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
