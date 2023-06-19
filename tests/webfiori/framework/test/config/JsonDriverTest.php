<?php
namespace webfiori\framework\test\config;

use PHPUnit\Framework\TestCase;
use webfiori\framework\config\JsonDriver;
/**
 *
 * @author Ibrahim
 */
class JsonDriverTest extends TestCase{
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
}
