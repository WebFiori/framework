<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\HelpCommand;

/**
 * Description of HelpCommandTest
 *
 * @author Ibrahim
 */
class HelpCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        
        $commandRunner->runCommand(new HelpCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'Usage:',
            '    command [arg1 arg2="val" arg3...]',
            '',
            'Available Commands:',
            '    help',
            '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.',
            '',
            '    v',
            '        Display framework version info.',
            '',
            '    show-config',
            '        Display framework configuration.',
            '',
            '    list-themes',
            '        List all registered themes.',
            '',
            '    list-jobs',
            '        List all scheduled CRON jobs.',
            '',
            '    list-routes',
            '        List all created routes and which resource they point to.',
            '',
            '    cron',
            '               Run CRON Scheduler',
            '',
            '    route',
            '        Test the result of routing to a URL',
            '',
            '    create',
            '        Creates a system entity (middleware, web service, background process ...).',
            '',
            '    add',
            '        Add a database connection or SMTP account.',
            '',
            '    update-table',
            '         Update a database table.',
            '',
            '    run-query',
            '        Execute SQL query on specific database.',
            '',
            '    update-settings',
            '        Update application settings which are stored in the class "AppConfig".',
            '',
            '',
        ], $this));
    }
    /**
     * @test
     */
    public function test01() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        $_SERVER['argc'] = 1;
        $commandRunner->runCommand(new HelpCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            '|\                /|                          ',                        
            '| \      /\      / |              |  / \  |',
            '\  \    /  \    /  / __________   |\/   \/|',
            ' \  \  /    \  /  / /  /______ /  | \/ \/ |',
            '  \  \/  /\  \/  / /  /           |  \ /  |',
            '   \    /  \    / /  /______      |\  |  /|',
            '    \  /    \  / /  /______ /       \ | /  ',
            '     \/  /\  \/ /  /                  |    ',
            '      \ /  \ / /  /                   |    ',
            '       ______ /__/                    |    ',
            '',
            'WebFiori Framework  (c) Version 2.4.7 Stable',
            '',
            '',
            'Usage:',
            '    command [arg1 arg2="val" arg3...]',
            '',
            'Available Commands:',
            '    help',
            '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.',
            '',
            '    v',
            '        Display framework version info.',
            '',
            '    show-config',
            '        Display framework configuration.',
            '',
            '    list-themes',
            '        List all registered themes.',
            '',
            '    list-jobs',
            '        List all scheduled CRON jobs.',
            '',
            '    list-routes',
            '        List all created routes and which resource they point to.',
            '',
            '    cron',
            '               Run CRON Scheduler',
            '',
            '    route',
            '        Test the result of routing to a URL',
            '',
            '    create',
            '        Creates a system entity (middleware, web service, background process ...).',
            '',
            '    add',
            '        Add a database connection or SMTP account.',
            '',
            '    update-table',
            '         Update a database table.',
            '',
            '    run-query',
            '        Execute SQL query on specific database.',
            '',
            '    update-settings',
            '        Update application settings which are stored in the class "AppConfig".',
            '',
            '',
        ], $this));
    }
}
