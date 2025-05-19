<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\CLITestCase;
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
            "    help:                Display CLI Help. To display help for specific command, use the argument \"--command-name\" with this command.\n",
            "    v:                   Display framework version info.\n",
            "    show-settings:       Display application configuration.\n",
            "    scheduler:           Run tasks scheduler.\n",
            "    create:              Creates a system entity (middleware, web service, background process ...).\n",
            "    add:                 Add a database connection or SMTP account.\n",
            "    list-routes:         List all created routes and which resource they point to.\n",
            "    list-themes:         List all registered themes.\n",
            "    run-query:           Execute SQL query on specific database.\n",
            "    update-settings:     Update application settings which are stored in specific configuration driver.\n",
            "    update-table:        Update a database table.\n",
            "    migrations:          Execute database migrations.\n",
        ], $this->executeMultiCommand([
            'help',
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
}
