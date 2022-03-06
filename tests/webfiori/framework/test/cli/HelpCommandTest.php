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
        $this->assertEquals([
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
        ], $commandRunner->getOutputsArray());
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
    /**
     * @test
     */
    public function test02() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'help'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        '    help',
        '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.',
        '',
        '    Supported Arguments:',
        '               --command-name: [Optional] An optional command name. If provided, help will be specific to the given command only.',
        //'                       --ansi: [Optional] Force the use of ANSI output.',
        //'                    --no-ansi: [Optional] Force the output to not use ANSI.',
        ''
        ], $commandRunner->getOutputsArray());
    }
    /**
     * @test
     */
    public function test03() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'no-command'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        'Error: Command \'no-command\' is not supported.',
        ''
        ], $commandRunner->getOutputsArray());
    }
    /**
     * @test
     */
    public function test04() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'no-command'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        'Error: Command \'no-command\' is not supported.',
        ''
        ], $commandRunner->getOutputsArray());
    }
    /**
     * @test
     */
    public function test05() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input.txt', TESTS_PATH.DS.'output.txt');
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'cron'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        '    cron',
        '               Run CRON Scheduler',
        '',
        '    Supported Arguments:',
        '                            p: [Optional] CRON password. If it is set in CRON, then it must be provided here.',
        '                      --check: [Optional] Run a check aginst all jobs to check if it is time to execute them or not.',
        '                      --force: [Optional] Force a specific job to execute.',
        '                   --job-name: [Optional] The name of the job that will be forced to execute.',
        '              --show-job-args: [Optional] If this one is provided with job name and a job has custom execution args, they will be shown.',
        '                   --show-log: [Optional] If set, execution log will be shown after execution is completed.',
        ''
        ], $commandRunner->getOutputsArray());
    }
}
