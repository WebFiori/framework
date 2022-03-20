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
        $commandRunner = new CommandRunner();
        
        $commandRunner->runCommand(new HelpCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'Usage:'."\n",
            '    command [arg1 arg2="val" arg3...]'."\n\n",
            'Available Commands:'."\n",
            '    help'."\n",
            '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.'."\n\n",

            '    v'."\n",
            '        Display framework version info.'."\n\n",

            '    show-config'."\n",
            '        Display framework configuration.'."\n\n",

            '    list-themes'."\n",
            '        List all registered themes.'."\n\n",

            '    list-jobs'."\n",
            '        List all scheduled CRON jobs.'."\n\n",

            '    list-routes'."\n",
            '        List all created routes and which resource they point to.'."\n\n",

            '    cron'."\n",
            '               Run CRON Scheduler'."\n\n",

            '    route'."\n",
            '        Test the result of routing to a URL'."\n\n",

            '    create'."\n",
            '        Creates a system entity (middleware, web service, background process ...).'."\n\n",

            '    add'."\n",
            '        Add a database connection or SMTP account.'."\n\n",

            '    update-table'."\n",
            '         Update a database table.'."\n\n",

            '    run-query'."\n",
            '        Execute SQL query on specific database.'."\n\n",

            '    update-settings'."\n",
            '        Update application settings which are stored in the class "AppConfig".'."\n\n",

        ], $this));
    }
    /**
     * @test
     */
    public function test01() {
        $commandRunner = new CommandRunner();
        $_SERVER['argc'] = 1;
        $commandRunner->runCommand(new HelpCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            "|\                /|                          \n"
            ."| \      /\      / |              |  / \  |\n"
            ."\  \    /  \    /  / __________   |\/   \/|\n"
            ." \  \  /    \  /  / /  /______ /  | \/ \/ |\n"
            ."  \  \/  /\  \/  / /  /           |  \ /  |\n"
            ."   \    /  \    / /  /______      |\  |  /|\n"
            ."    \  /    \  / /  /______ /       \ | /  \n"
            ."     \/  /\  \/ /  /                  |    \n"
            ."      \ /  \ / /  /                   |    \n"
            ."       ______ /__/                    |    \n\n",

            'WebFiori Framework  (c) Version 2.4.7 Stable'."\n\n\n",


            'Usage:'."\n",
            '    command [arg1 arg2="val" arg3...]'."\n\n",

            'Available Commands:'."\n",
            '    help'."\n",
            '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.'."\n\n",

            '    v'."\n",
            '        Display framework version info.'."\n\n",

            '    show-config'."\n",
            '        Display framework configuration.'."\n\n",

            '    list-themes'."\n",
            '        List all registered themes.'."\n\n",

            '    list-jobs'."\n",
            '        List all scheduled CRON jobs.'."\n\n",

            '    list-routes'."\n",
            '        List all created routes and which resource they point to.'."\n\n",

            '    cron'."\n",
            '               Run CRON Scheduler'."\n\n",

            '    route'."\n",
            '        Test the result of routing to a URL'."\n\n",

            '    create'."\n",
            '        Creates a system entity (middleware, web service, background process ...).'."\n\n",

            '    add'."\n",
            '        Add a database connection or SMTP account.'."\n\n",

            '    update-table'."\n",
            '         Update a database table.'."\n\n",

            '    run-query'."\n",
            '        Execute SQL query on specific database.'."\n\n",

            '    update-settings'."\n",
            '        Update application settings which are stored in the class "AppConfig".'."\n\n",

        ], $this));
    }
    /**
     * @test
     */
    public function test02() {
        $commandRunner = new CommandRunner();
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'help'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        '    help'."\n",
        '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.'."\n\n",
        '    Supported Arguments:'."\n",
        '               --command-name: [Optional] An optional command name. If provided, help will be specific to the given command only.'."\n",
        ], $commandRunner->getOutputsArray());
    }
    /**
     * @test
     */
    public function test03() {
        $commandRunner = new CommandRunner();
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'no-command'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        'Error: Command \'no-command\' is not supported.'."\n",
        ], $commandRunner->getOutputsArray());
    }
    /**
     * @test
     */
    public function test04() {
        $commandRunner = new CommandRunner();
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'no-command'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertEquals([
        'Error: Command \'no-command\' is not supported.'."\n",
        ], $commandRunner->getOutputsArray());
    }
    /**
     * @test
     */
    public function test05() {
        $commandRunner = new CommandRunner();
        
        $commandRunner->runCommand(new HelpCommand(), [
            '--command-name' => 'cron'
        ]);
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
        '    cron'."\n",
        '               Run CRON Scheduler'."\n\n",
        '    Supported Arguments:'."\n",
        '                            p: [Optional] CRON password. If it is set in CRON, then it must be provided here.'."\n",
        '                      --check: [Optional] Run a check aginst all jobs to check if it is time to execute them or not.'."\n",
        '                      --force: [Optional] Force a specific job to execute.'."\n",
        '                   --job-name: [Optional] The name of the job that will be forced to execute.'."\n",
        '              --show-job-args: [Optional] If this one is provided with job name and a job has custom execution args, they will be shown.'."\n",
        '                   --show-log: [Optional] If set, execution log will be shown after execution is completed.'."\n",
        ], $this));
    }
}
