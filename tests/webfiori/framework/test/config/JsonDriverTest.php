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
        $this->assertEquals('https://127.0.0.1',$driver->getHomePage());
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
    public function testSetConfigFileName00() {
        $this->assertEquals('app-config', JsonDriver::getConfigFileName());
        JsonDriver::setConfigFileName('super-conf.json');
        $this->assertEquals('super-conf', JsonDriver::getConfigFileName());
        JsonDriver::setConfigFileName('super-confx.kkp');
        $this->assertEquals('super-confx', JsonDriver::getConfigFileName());
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
    public function testSetPrimaryLanguage00() {
        $driver = new JsonDriver();
        $this->assertEquals('EN', $driver->getPrimaryLanguage());
        $driver->setPrimaryLanguage('ar');
    }
    /**
     * @test
     * @depends testSetPrimaryLanguage00
     */
    public function testSetPrimaryLanguage01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals('AR', $driver->getPrimaryLanguage());
        $driver->setPrimaryLanguage('');
    }
    /**
     * @test
     * @depends testSetPrimaryLanguage01
     */
    public function testSetPrimaryLanguage02() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals('AR', $driver->getPrimaryLanguage());
    }
    /**
     * @test
     */
    public function testAddEnvVar00() {
        $driver = new JsonDriver();
        $this->assertEquals([
            'WF_VERBOSE' => [
                'value' => false,
                'description' => 'Configure the verbosity of error messsages at run-time. This should be set to true in testing and false in production.'
            ],
            "CLI_HTTP_HOST" => [
                "value" => "example.com",
                "description" => "Host name that will be used when runing the application as command line utility."
            ]
        ], $driver->getEnvVars());
        $driver->addEnvVar('COOL_OR_NOT', 'cool');
        $driver->addEnvVar('DO_IT', false);
        $driver->addEnvVar('MULTIPLY_BY', 4, 'A number to multiply by.');
    }
    /**
     * @test
     * @depends testAddEnvVar00
     */
    public function testAddEnvVar01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals([
            'WF_VERBOSE' => [
                'value' => false,
                'description' => 'Configure the verbosity of error messsages at run-time. This should be set to true in testing and false in production.'
            ],
            "CLI_HTTP_HOST" => [
                "value" => "example.com",
                "description" => "Host name that will be used when runing the application as command line utility."
            ],
            "COOL_OR_NOT" => [
                "value" => "cool",
                'description' => null
            ],
            "DO_IT" => [
                "value" => false,
                'description' => null
            ],
            "MULTIPLY_BY" => [
                "value" => 4,
                "description" => "A number to multiply by."
            ],
        ], $driver->getEnvVars());
        $driver->removeEnvVar('COOL_OR_NOT');
    }
    /**
     * @test
     * @depends testAddEnvVar01
     */
    public function testAddEnvVar02() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals([
            'WF_VERBOSE' => [
                'value' => false,
                'description' => 'Configure the verbosity of error messsages at run-time. This should be set to true in testing and false in production.'
            ],
            "CLI_HTTP_HOST" => [
                "value" => "example.com",
                "description" => "Host name that will be used when runing the application as command line utility."
            ],
            "DO_IT" => [
                "value" => false,
                'description' => null
            ],
            "MULTIPLY_BY" => [
                "value" => 4,
                "description" => "A number to multiply by."
            ],
        ], $driver->getEnvVars());
    }
    /**
     * @test
     */
    public function testSetTitle00() {
        $driver = new JsonDriver();
        $this->assertEquals([
            'AR' => 'افتراضي',
            'EN' => 'Default'
        ], $driver->getTitles());
        $this->assertEquals('Default', $driver->getTitle('En'));
        $this->assertEquals('افتراضي', $driver->getTitle('aR'));
        $this->assertEquals('', $driver->getTitle('aRn'));
        $this->assertEquals('', $driver->getTitle(''));
        $driver->setTitle('Ok', 'En');
        $driver->setTitle('اوكي', 'ar');
    }
    /**
     * @test
     * @depends testSetTitle00
     */
    public function testSetTitle01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals([
            'AR' => 'اوكي',
            'EN' => 'Ok'
        ], $driver->getTitles());
        $this->assertEquals('Ok', $driver->getTitle('En'));
        $this->assertEquals('اوكي', $driver->getTitle('aR'));
        $driver->setTitle('Jap', 'Jp');
        $driver->setTitle('Look', 'RUP');
        $driver->setTitle('', 'En');
    }
    /**
     * @test
     * @depends testSetTitle01
     */
    public function testSetTitle02() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals([
            'AR' => 'اوكي',
            'EN' => 'Ok',
            'JP' => 'Jap'
        ], $driver->getTitles());
        $this->assertEquals('Ok', $driver->getTitle('En'));
        $this->assertEquals('اوكي', $driver->getTitle('aR'));
        $this->assertEquals('Jap', $driver->getTitle('jp'));
    }
    /**
     * @test
     */
    public function testSetVersion00() {
        $driver = new JsonDriver();
        $this->assertEquals('1.0', $driver->getAppVersion());
        $this->assertEquals(date('Y-m-d'), $driver->getAppReleaseDate());
        $this->assertEquals('Stable', $driver->getAppVersionType());
        $driver->setAppVersion('2.0.0', 'Alpha', '2023-09-15');
    }
    /**
     * @test
     * @depends testSetVersion00
     */
    public function testSetVersion01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals('2.0.0', $driver->getAppVersion());
        $this->assertEquals('2023-09-15', $driver->getAppReleaseDate());
        $this->assertEquals('Alpha', $driver->getAppVersionType());
    }
    /**
     * @test
     */
    public function testSetDescription00() {
        $driver = new JsonDriver();
        $this->assertEquals([
            'AR' => '',
            'EN' => ''
        ], $driver->getDescriptions());
        $this->assertEquals('', $driver->getDescription('En'));
        $this->assertEquals('', $driver->getDescription('aR'));
        $this->assertEquals('', $driver->getDescription('aRn'));
        $this->assertEquals('', $driver->getDescription(''));
        $driver->setDescription('Ok', 'En');
        $driver->setDescription('اوكي', 'ar');
    }
    
    /**
     * @test
     * @depends testSetDescription00
     */
    public function testSetDescription01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals([
            'AR' => 'اوكي',
            'EN' => 'Ok'
        ], $driver->getDescriptions());
        $this->assertEquals('Ok', $driver->getDescription('En'));
        $this->assertEquals('اوكي', $driver->getDescription('aR'));
        $driver->setDescription('Jap', 'Jp');
        $driver->setDescription('Look', 'RUP');
        $driver->setDescription('', 'En');
    }
    /**
     * @test
     * @depends testSetDescription01
     */
    public function testSetDescription02() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals([
            'AR' => 'اوكي',
            'EN' => '',
            'JP' => 'Jap'
        ], $driver->getDescriptions());
        $this->assertEquals('', $driver->getDescription('En'));
        $this->assertEquals('اوكي', $driver->getDescription('aR'));
        $this->assertEquals('Jap', $driver->getDescription('jp'));
    }
    /**
     * @test
     */
    public function testSetTitleSeparator00() {
        $driver = new JsonDriver();
        $this->assertEquals('|', $driver->getTitleSeparator());
        $driver->setTitleSeparator('*');
    }
    /**
     * @test
     * @depends testSetTitleSeparator00
     */
    public function testSetTitleSeparator01() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals('*', $driver->getTitleSeparator());
        $driver->setTitleSeparator('');
    }
    /**
     * @test
     * @depends testSetTitleSeparator01
     */
    public function testSetTitleSeparator02() {
        $driver = new JsonDriver();
        $driver->initialize();
        $this->assertEquals('*', $driver->getTitleSeparator());
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
        $account = $driver->getSMTPConnection('Cool');
        $this->assertNotNull($account);
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
    /**
     * @test
     */
    public function testAppWithError00() {
        $this->expectExceptionMessage('The property "username" of the connection "New_Connection" is missing.');
        JsonDriver::setConfigFileName('config-with-err-00');
        $driver = new JsonDriver();
        $driver->initialize();
        $driver->getDBConnections();
    }
}
