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
            "WebFiori Framework  (c) Version ". WF_VERSION." ".WF_VERSION_TYPE."\n\n\n",
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





            "    migrations:              Execute database migrations.\n",
        ], $this->executeMultiCommand([
            'help',
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
}
