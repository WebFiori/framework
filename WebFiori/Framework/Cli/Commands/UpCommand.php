<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Attributes\Group;
use WebFiori\Cli\Command;

/**
 * A command to bring the application out of maintenance mode.
 */
#[Group('maintenance')]
class UpCommand extends Command {
    public function __construct() {
        parent::__construct('up', [], 'Bring the application out of maintenance mode.');
    }
    /**
     * Execute the command.
     */
    public function exec(): int {
        $path = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'.maintenance';

        if (file_exists($path)) {
            unlink($path);
            $this->println('Application is now live.');
        } else {
            $this->println('Application is not in maintenance mode.');
        }

        return 0;
    }
}
