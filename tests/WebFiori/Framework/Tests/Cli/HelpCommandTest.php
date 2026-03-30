<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
/**
 * @author Ibrahim
 */
class HelpCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $this->assertEquals([
            "WebFiori Framework  (c) Version ".WF_VERSION." ".WF_VERSION_TYPE."\n\n\n",
            "Usage:\n",
            "    command [arg1 arg2=\"val\" arg3...]\n\n",
            "Global Arguments:\n",
            "    --ansi:[Optional] Force the use of ANSI output.\n",
            "Available Commands:\n",
            "    help:                    Display CLI Help. To display help for specific command, use the argument \"--command\" with this command.\n",
            "    v:                       Display framework version info.\n",

            "    scheduler:               Run tasks scheduler.\n",
            "    scheduler:run:           Run the tasks scheduler check.\n",
            "    add:db-connection:       Add a database connection.\n",
            "    add:smtp-connection:     Add an SMTP account.\n",
            "    add:lang:                Add a website language.\n",
            "    create:middleware:       Create a new middleware class.\n",
            "    create:task:             Create a new scheduler task class.\n",
            "    create:command:          Create a new CLI command class.\n",
            "    create:entity:           Create a new domain entity class.\n",
            "    create:service:          Create a new REST service class.\n",
            "    create:table:            Create a new database table schema class.\n",
            "    create:repository:       Create a new repository class.\n",
            "    create:resource:         Create a complete CRUD resource (entity, table, repository, service).\n",
            "    create:migration:        Create a new database migration class.\n",
            "    create:seeder:           Create a new database seeder class.\n",




            "    migrations:run:          Execute pending database migrations.\n",
            "    migrations:rollback:     Rollback database migrations.\n",
            "    migrations:ini:          Create migrations tracking table.\n",
            "    migrations:dry-run:      Preview pending migrations without executing.\n",
            "    migrations:status:       Show migration status (applied and pending).\n",
            "    migrations:fresh:        Rollback all migrations and run them fresh.\n",
        ], $this->executeMultiCommand([
            'help',
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Covers: printLogo() branch — argsVector set to ['--ansi'] so array_diff count == 0
     */
    public function testPrintsLogoWhenOnlyAnsiInArgV() {
        $runner = $this->getRunner(true);
        $runner->setArgsVector(['--ansi']);
        $runner->setInputs([]);
        $runner->start();
        $output = $runner->getOutput();

        $this->assertEquals("|\                /|\n", $output[0]);
        $this->assertEquals("| \      /\      / |              |  / \  |\n", $output[1]);
        $this->assertEquals("\  \    /  \    /  / __________   |\/   \/|\n", $output[2]);
        $this->assertEquals(" \  \  /    \  /  / /  /______ /  | \/ \/ |\n", $output[3]);
        $this->assertEquals("  \  \/  /\  \/  / /  /           |  \ /  |\n", $output[4]);
        $this->assertEquals("   \    /  \    / /  /______      |\  |  /|\n", $output[5]);
        $this->assertEquals("    \  /    \  / /  /______ /       \ | /  \n", $output[6]);
        $this->assertEquals("     \/  /\  \/ /  /                  |    \n", $output[7]);
        $this->assertEquals("      \ /  \ / /  /                   |    \n", $output[8]);
        $this->assertEquals("       ______ /__/                    |    \n", $output[9]);
    }

    /**
     * @test
     * Covers: exec() with --command arg — logo is NOT printed (array_diff count > 0)
     */
    public function testWithCommandArgSkipsLogo() {
        $output = $this->executeMultiCommand(['help', '--command' => 'help']);
        $this->assertNotContains("|\                /|\n", $output);
        $this->assertEquals(0, $this->getExitCode());
    }
}
