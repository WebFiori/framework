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
}
