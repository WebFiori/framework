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

use WebFiori\Cli\Command;
use WebFiori\Cli\Argument;
use WebFiori\Cli\Attributes\Group;

/**
 * A command to put the application in maintenance mode.
 */
#[Group('maintenance')]
class DownCommand extends Command {
    public function __construct() {
        parent::__construct('down', [
            new Argument('--allow', 'Comma-separated IPs allowed during maintenance.', true),
            new Argument('--retry', 'Retry-After header value in seconds.', true),
            new Argument('--message', 'Custom maintenance message.', true),
            new Argument('--api-prefix', 'URL prefix for API routes (used for JSON responses).', true),
        ], 'Put the application in maintenance mode.');
    }
    /**
     * Execute the command.
     */
    public function exec(): int {
        $data = [
            'time' => date('c'),
            'allowed' => [],
            'retry_after' => 3600,
            'message' => 'Application is under maintenance.',
            'api_prefix' => '/api',
        ];

        $allow = $this->getArgValue('--allow');

        if ($allow !== null) {
            $data['allowed'] = array_map('trim', explode(',', $allow));
        }

        $retry = $this->getArgValue('--retry');

        if ($retry !== null) {
            $data['retry_after'] = intval($retry);
        }

        $message = $this->getArgValue('--message');

        if ($message !== null) {
            $data['message'] = $message;
        }

        $apiPrefix = $this->getArgValue('--api-prefix');

        if ($apiPrefix !== null) {
            $data['api_prefix'] = $apiPrefix;
        }

        $path = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'.maintenance';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        $this->println('Application is now in maintenance mode.');

        return 0;
    }
}
