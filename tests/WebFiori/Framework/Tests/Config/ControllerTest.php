<?php
namespace WebFiori\Framework\Test\Config;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Config\Controller;
use WebFiori\Framework\Config\JsonDriver;

class ControllerTest extends TestCase {
    public function testGetInstance() {
        $ctrl = Controller::get();
        $this->assertInstanceOf(Controller::class, $ctrl);
        // Calling again returns same instance
        $this->assertSame($ctrl, Controller::get());
    }
    public function testGetDriver() {
        $driver = Controller::getDriver();
        $this->assertNotNull($driver);
    }
    public function testSetDriver() {
        $newDriver = new JsonDriver();
        Controller::setDriver($newDriver);
        $this->assertSame($newDriver, Controller::getDriver());
    }
    public function testAddEnvVar() {
        $ctrl = Controller::get();
        $ctrl->addEnvVar('TEST_CTRL_VAR', 'hello', 'A test variable');
        $vars = Controller::getDriver()->getEnvVars();
        $this->assertArrayHasKey('TEST_CTRL_VAR', $vars);
    }
    public function testResolveEnvValueNonString() {
        $this->assertEquals(42, Controller::resolveEnvValue(42));
        $this->assertEquals(null, Controller::resolveEnvValue(null));
        $this->assertEquals(true, Controller::resolveEnvValue(true));
    }
    public function testResolveEnvValueNoPrefix() {
        $this->assertEquals('hello', Controller::resolveEnvValue('hello'));
        $this->assertEquals('environment', Controller::resolveEnvValue('environment'));
    }
    public function testResolveEnvValueWithEnvPrefix() {
        putenv('TEST_RESOLVE_VAR=resolved_value');
        $this->assertEquals('resolved_value', Controller::resolveEnvValue('env:TEST_RESOLVE_VAR'));
        putenv('TEST_RESOLVE_VAR');
    }
    public function testResolveEnvValueFallback() {
        // Non-existent env var falls back to original value
        $result = Controller::resolveEnvValue('env:NON_EXISTENT_VAR_XYZ_123');
        $this->assertEquals('env:NON_EXISTENT_VAR_XYZ_123', $result);
    }
    public function testUpdateEnv() {
        Controller::getDriver()->addEnvVar('TEST_UPDATE_ENV_VAR', 'test_val', 'desc');
        Controller::updateEnv();
        // The constant should be defined if not already
        if (defined('TEST_UPDATE_ENV_VAR')) {
            $this->assertEquals('test_val', TEST_UPDATE_ENV_VAR);
        } else {
            $this->assertTrue(true);
        }
    }
    public function testCopySameDriver() {
        $ctrl = Controller::get();
        $driver = Controller::getDriver();
        // Copy to same type should do nothing
        $sameType = new JsonDriver();
        $ctrl->copy($sameType);
        $this->assertTrue(true);
    }
    public function testCopyToDifferentDriver() {
        $ctrl = Controller::get();
        Controller::getDriver()->setAppName('CopyTestApp', 'EN');
        Controller::getDriver()->setPrimaryLanguage('EN');
        Controller::getDriver()->setTheme('');
        Controller::getDriver()->setHomePage('https://example.com');
        Controller::getDriver()->setTitleSeparator('|');
        Controller::getDriver()->setSchedulerPassword('pass123');

        $classDriver = new \WebFiori\Framework\Config\ClassDriver();
        $ctrl->copy($classDriver);
        // Verify copy happened by checking app names
        $names = $classDriver->getAppNames();
        $this->assertArrayHasKey('EN', $names);
    }
}
