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
        $password = getenv('MYSQL_ROOT_PASSWORD') ?: '12345326';

        $output = $this->executeSingleCommand(new AddSmtpConnectionCommand(), [
            'WebFiori',
            'add:smtp-connection'
        ], [
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 25)
            'test@example.com',
            $password,
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
            str_repeat('*', strlen($password))."\n",
            "Sender email address: Enter = 'test@example.com'\n",
            "Sender name: Enter = 'test@example.com'\n",
            "Give your connection a friendly name: Enter = '$connName'\n",
            "Trying to connect. This can take up to 1 minute...\n",
            "Error: Unable to connect to SMTP server.\n",
            "Error Information: \n",
            "Would you like to store connection information anyway?(y/N)\n",
        ], $output);
    }
    /**
     * @test
     * Tests that all args bypass interactive prompts and --no-check skips connection attempt.
     */
    public function testAddSMTPConnection01() {
        $connName = 'my-smtp-conn-'.time();
        $countBefore = count(App::getConfig()->getSMTPConnections());

        $output = $this->executeSingleCommand(new AddSmtpConnectionCommand(), [
            '--host=smtp.example.com',
            '--port=587',
            '--user=user@example.com',
            '--password=secret',
            '--sender-address=user@example.com',
            '--sender-name=Test User',
            '--name='.$connName,
            '--no-check',
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Success: Connection information was stored in application configuration.\n"
        ], $output);

        $connections = App::getConfig()->getSMTPConnections();
        $this->assertCount($countBefore + 1, $connections);
        $this->assertArrayHasKey($connName, $connections);
        $conn = $connections[$connName];
        $this->assertEquals('smtp.example.com', $conn->getServerAddress());
        $this->assertEquals(587, $conn->getPort());
        $this->assertEquals('user@example.com', $conn->getUsername());
        $this->assertEquals('user@example.com', $conn->getAddress());
        $this->assertEquals('Test User', $conn->getSenderName());
    }
    /**
     * @test
     * Tests that --oauth-token is stored on the connection.
     */
    public function testAddSMTPConnection02() {
        $connName = 'oauth-smtp-conn-'.time();
        $token = 'my-oauth-token-xyz';

        $output = $this->executeSingleCommand(new AddSmtpConnectionCommand(), [
            '--host=smtp.example.com',
            '--port=587',
            '--user=user@example.com',
            '--password=secret',
            '--sender-address=user@example.com',
            '--sender-name=Test User',
            '--name='.$connName,
            '--oauth-token='.$token,
            '--no-check',
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $connections = App::getConfig()->getSMTPConnections();
        $this->assertArrayHasKey($connName, $connections);
        $this->assertEquals($token, $connections[$connName]->getAccessToken());
    }
    /**
     * @test
     * Tests that providing some args still prompts for the missing ones.
     */
    public function testAddSMTPConnection03() {
        $connName = 'smtp-connection-'.(count(App::getConfig()->getSMTPConnections()));

        $output = $this->executeSingleCommand(new AddSmtpConnectionCommand(), [
            '--host=127.0.0.1',
            '--port=25',
            '--user=test@example.com',
            '--password=secret',
            '--sender-address=test@example.com',
            '--sender-name=Test',
        ], [
            "\n", // Hit Enter to pick default connection name
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $output = $this->getOutput();

        $this->assertEquals("Give your connection a friendly name: Enter = '$connName'\n", $output[0]);
        $this->assertEquals("Trying to connect. This can take up to 1 minute...\n", $output[1]);
    }
}
