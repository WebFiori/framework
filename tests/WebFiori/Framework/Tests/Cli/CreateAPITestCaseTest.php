<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Scheduler\WebServices\TasksServicesManager;
use WebFiori\Http\WebServicesManager;
/**
 * @author Ibrahim
 */
class CreateAPITestCaseTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateAPITestCase00() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--manager' => 'A',
            '--service' => 'c'
        ]);
        
        $this->assertStringContainsString("Error: The argument --manager has invalid value", $output[0]);
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCreateAPITestCase01() {
        $path = ROOT_PATH.DS."tests".DS."WebFiori".DS."framework".DS."scheduler".DS."webServices";
        $this->assertEquals([
            "Info: Selected services manager has no service with name 'c'.\n",
            "Which service you would like to have a test case for?\n",
            "0: login\n",
            "1: force-execution\n",
            "2: logout\n",
            "3: get-tasks\n",
            "4: set-password\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: tests\WebFiori\\Framework\Scheduler\WebServices\\TasksLoginServiceTest'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--manager' => TasksServicesManager::class,
            '--service' => 'c'
        ], [
            "0",
            "y"
        ]));
        
        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\tests\WebFiori\\Framework\Scheduler\WebServices\\TasksLoginServiceTest';
        $this->assertTrue(file_exists($path.DS.'TasksLoginServiceTest.php'));
        require_once $path.DS.'TasksLoginServiceTest.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase02() {
        $path = ROOT_PATH.DS."tests".DS."WebFiori".DS."framework".DS."scheduler".DS."webServices";
        $this->assertEquals([
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--manager' => TasksServicesManager::class,
            '--service' => 'get-tasks',
            '--defaults'
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\tests\WebFiori\\Framework\Scheduler\WebServices\\GetTasksServiceTest';
        $this->assertTrue(file_exists($path.DS.'GetTasksServiceTest.php'));
        require_once $path.DS.'GetTasksServiceTest.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase03() {
        $path = ROOT_PATH.DS."tests".DS."WebFiori".DS."framework".DS."scheduler".DS."webServices";
        $this->assertEquals([
            "Please enter services manager information:\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: tests\WebFiori\\Framework\Scheduler\WebServices\\GetTasksServiceTest'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\WebFiori\\Framework\Scheduler\WebServices'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'get-tasks',
        ], [
            '\WebFiori\\Framework\Scheduler\WebServices\\TasksServicesManager',
            'n',
            '10',
            '',
            '',
        ]));
        $this->assertEquals(0, $this->getExitCode());

        $clazz = '\\tests\WebFiori\\Framework\Scheduler\WebServices\\GetTasksServiceTest';
        $this->assertTrue(file_exists($path.DS.'GetTasksServiceTest.php'));
        require_once $path.DS.'GetTasksServiceTest.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase04() {
        $path = ROOT_PATH.DS."tests".DS."tests".DS."Apis".DS."Multiple";
        $this->assertEquals([
            "Please enter services manager information:\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: tests\tests\Apis\Multiple\WebService00Test'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\\tests\Apis\Multiple'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'say-hi-service',
        ], [
            '\\Tests\\Apis\\Multiple\\ServicesManager00',
            'n',
            '10',
            '',
            '',
        ]));
        $this->assertEquals(0, $this->getExitCode());

        $clazz = '\\tests\\Tests\\Apis\\Multiple\\WebService00Test';
        $this->assertTrue(file_exists($path.DS.'WebService00Test.php'));
        require_once $path.DS.'WebService00Test.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function testCreateAPITestCase05() {
        $this->assertEquals([
            "Info: Provided services manager has 0 registered services.\n",
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--manager' => '\\Tests\\Apis\\EmptyService\\EmptyServicesManager',
        ]));
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCreateAPITestCase06() {
        $path = ROOT_PATH.DS."tests".DS."tests".DS."apis".DS."multiple";
        $this->assertEquals([
            "Please enter services manager information:\n",
            "Error: Provided class is not an instance of ".WebServicesManager::class."\n",
            "Please enter services manager information:\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: tests\tests\Apis\Multiple\WebService00Test'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\\tests\Apis\Multiple'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'say-hi-service',
        ], [
            '\\Tests\\Apis\\Multiple\\WebService00',
            '\\Tests\\Apis\\Multiple\\ServicesManager00',
            'n',
            '10',
            '',
            '',
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\tests\\Tests\\Apis\\Multiple\\WebService00Test';
        $this->assertTrue(file_exists($path.DS.'WebService00Test.php'));
        require_once $path.DS.'WebService00Test.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase07() {
        $this->assertEquals([
            "Error: The argument --manager has invalid value: Not a class: \\Tests\\Apis\\EmptyService\\Xyz\n",
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--manager' => '\\Tests\\Apis\\EmptyService\\Xyz',
        ]));
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCreateAPITestCase08() {
        $path = ROOT_PATH.DS."tests".DS."tests".DS."Apis".DS."Multiple";
        $this->assertEquals([
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'say-hi-service-2',
            '--manager' => '\\Tests\\Apis\\Multiple\\ServicesManager00',
            '--defaults'
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\tests\\Tests\\Apis\\Multiple\\WebService01Test';
        $this->assertTrue(file_exists($path.DS.'WebService01Test.php'));
        require_once $path.DS.'WebService01Test.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
}
