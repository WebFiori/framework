<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\AddSmtpConnectionCommand;

/**
 * Test cases for AddSmtpConnectionCommand
 *
 * @author Ibrahim
 */
class AddSmtpConnectionCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testAddSMTPConnection00() {
        $connName = 'smtp-connection-'.count(App::getConfig()->getSMTPConnections());

        $output = $this->executeSingleCommand(new AddSmtpConnectionCommand(), [
            'WebFiori',
            'add:smtp-connection'
        ], [
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 25)
            'test@example.com',
            getenv('MYSQL_ROOT_PASSWORD') ?: '12345326',
            'test@example.com',
            'test@example.com',
            "\n", // Hit Enter to pick default value (connection name)
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "SMTP Server address: Enter = '127.0.0.1'\n",
            "Port number: Enter = '25'\n",
            "Username:\n",
            "Password:\n",
            "********\n",
            "Sender email address: Enter = 'test@example.com'\n",
            "Sender name: Enter = 'test@example.com'\n",
            "Give your connection a friendly name: Enter = '$connName'\n",
            "Trying to connect. This can take up to 1 minute...\n",
            "Error: Unable to connect to SMTP server.\n",
            "Error Information: \n",
            "Would you like to store connection information anyway?(y/N)\n",
        ], $output);
    }
}
