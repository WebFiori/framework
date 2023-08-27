<?php
namespace webfiori\framework\test\config;

use PHPUnit\Framework\TestCase;
use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
use webfiori\framework\config\JsonDriver;
/**
 *
 * @author Ibrahim
 */
class JsonDriverTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $driver = new JsonDriver();
        $this->assertEquals([
            'AR' => 'تطبيق',
            'EN' => 'Application'
        ],$driver->getAppNames());
        $this->assertEquals(date('Y-m-d'),$driver->getAppReleaseDate());
        $this->assertEquals(date('1.0'),$driver->getAppVersion());
        $this->assertEquals('Stable',$driver->getAppVersionType());
        $this->assertEquals('https://127.0.0.1',$driver->getBaseURL());
        $this->assertEquals([

        ],$driver->getDBConnections());
        $this->assertEquals([
            'AR' => '',
            'EN' => ''
        ],$driver->getDescriptions());
        $this->assertEquals([
            'WF_VERBOSE' => [
                'value' => false,
                'description' => 'Configure the verbosity of error messsages at run-time. This should be set to true in testing and false in production.'
            ],
            'CLI_HTTP_HOST' => [
                'value' => 'example.com',
                'description' => 'Host name that will be used when runing the application as command line utility.'
            ],
        ],$driver->getEnvVars());
        $this->assertEquals('',$driver->getHomePage());
        $this->assertEquals('EN',$driver->getPrimaryLanguage());
        $this->assertEquals([

        ],$driver->getSMTPConnections());
        $this->assertEquals('NO_PASSWORD',$driver->getSchedulerPassword());
        $this->assertEquals('',$driver->getTheme());
        $this->assertEquals('|',$driver->getTitleSeparator());
        $this->assertEquals([
            'AR' => 'افتراضي',
            'EN' => 'Default'
        ],$driver->getTitles());
    }
    /**
     * @test
     */
    public function testAppNames00() {
        $driver = new JsonDriver();
        $this->assertEquals([
            'AR' => 'تطبيق',
            'EN' => 'Application'
        ],$driver->getAppNames());
        $driver->setAppName('Cool App', 'En');
        $this->assertEquals([
            'AR' => 'تطبيق',
            'EN' => 'Cool App'
        ],$driver->getAppNames());
        $driver->initialize();
    }
    /**
     * @test
     * @depends testAppNames00
     */
    public function testAppNames01() {
        $driver = new JsonDriver();
        $this->assertEquals([
            'AR' => 'تطبيق',
            'EN' => 'Application'
        ],$driver->getAppNames());
        $driver->initialize();
        $this->assertEquals([
            'AR' => 'تطبيق',
            'EN' => 'Cool App'
        ],$driver->getAppNames());
        $driver->remove();
    }
    /**
     * @test
     */
    public function testSMTPConnections00() {
        $driver = new JsonDriver();
        $this->assertEquals(0, count($driver->getSMTPConnections()));
        $this->assertNull($driver->getSMTPConnection('olf'));
        $conn = new SMTPAccount([
            'port' => 6,
            'server-address' => 'smtp@example.com',
            'user' => 'me@example.com',
            'pass' => 'some_pass',
            'sender-name' => 'WebFiori',
            'sender-address' => 'addr@example.com',
            'account-name' => 'Cool'
        ]);
        $driver->addOrUpdateSMTPAccount($conn);
        $this->assertEquals(1, count($driver->getSMTPConnections()));
    }
    /**
     * @test
     * @depends testSMTPConnections00
     */
    public function testSMTPConnections01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $account =$driver->getSMTPConnection('Cool');
        $this->assertEquals(6, $account->getPort());
        $this->assertEquals('Cool', $account->getAccountName());
        $this->assertEquals('addr@example.com', $account->getAddress());
        $this->assertEquals('WebFiori', $account->getSenderName());
        $this->assertEquals('smtp@example.com', $account->getServerAddress());
        $this->assertEquals('me@example.com', $account->getUsername());
        $account->setPort(990);
        $driver->addOrUpdateSMTPAccount($account);
    }
    /**
     * @test
     * @depends testSMTPConnections01
     */
    public function testSMTPConnections02() {
        $driver = new JsonDriver();
        $driver->initialize();
        $account =$driver->getSMTPConnection('Cool');
        $this->assertEquals(990, $account->getPort());
        $this->assertEquals('Cool', $account->getAccountName());
        $this->assertEquals('addr@example.com', $account->getAddress());
        $this->assertEquals('WebFiori', $account->getSenderName());
        $this->assertEquals('smtp@example.com', $account->getServerAddress());
        $this->assertEquals('me@example.com', $account->getUsername());
    }
    /**
     * @test
     */
    public function testDatabaseConnections00() {
        $driver = new JsonDriver();
        $this->assertEquals(0, count($driver->getDBConnections()));
        $this->assertNull($driver->getDBConnection('olf'));
        $conn = new ConnectionInfo('mysql', 'root', 'test@222', 'my_db', 'localhost', 3306);
        $driver->addOrUpdateDBConnection($conn);
        $this->assertEquals(1, count($driver->getDBConnections()));
    }
    /**
     * @test
     * @depends testDatabaseConnections00
     */
    public function testDatabaseConnections01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $account =$driver->getDBConnection('New_Connection');
        $this->assertEquals(3306, $account->getPort());
        $this->assertEquals('my_db', $account->getDBName());
        $this->assertEquals('mysql', $account->getDatabaseType());
        $this->assertEquals('localhost', $account->getHost());
        $this->assertEquals('test@222', $account->getPassword());
        $this->assertEquals('root', $account->getUsername());

    }
    
}
