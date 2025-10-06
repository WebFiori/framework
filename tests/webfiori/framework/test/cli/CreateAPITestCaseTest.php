<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\scheduler\webServices\TasksServicesManager;
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
        
        if ($output[0] !== "Error: The argument --manager has invalid value.\n") {
            $this->fail("Expected error message not found. Full output: " . implode('', $output));
        }
        
        $this->assertEquals("Error: The argument --manager has invalid value.\n", $output[0]);
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCreateAPITestCase01() {
        $path = ROOT_PATH.DS."tests".DS."webfiori".DS."framework".DS."scheduler".DS."webServices";
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
            'Name: tests\webfiori\\framework\scheduler\webServices\\TasksLoginServiceTest'."\n",
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
        $clazz = '\\tests\webfiori\\framework\scheduler\webServices\\TasksLoginServiceTest';
        $this->assertTrue(file_exists($path.DS.'TasksLoginServiceTest.php'));
        require_once $path.DS.'TasksLoginServiceTest.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase02() {
        $path = ROOT_PATH.DS."tests".DS."webfiori".DS."framework".DS."scheduler".DS."webServices";
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
        $clazz = '\\tests\webfiori\\framework\scheduler\webServices\\GetTasksServiceTest';
        $this->assertTrue(file_exists($path.DS.'GetTasksServiceTest.php'));
        require_once $path.DS.'GetTasksServiceTest.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase03() {
        $path = ROOT_PATH.DS."tests".DS."webfiori".DS."framework".DS."scheduler".DS."webServices";
        $this->assertEquals([
            "Please enter services manager information:\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: tests\webfiori\\framework\scheduler\webServices\\GetTasksServiceTest'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\webfiori\\framework\scheduler\webServices'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'get-tasks',
        ], [
            '\webfiori\\framework\scheduler\webServices\\TasksServicesManager',
            'n',
            '10',
            '',
            '',
        ]));
        $this->assertEquals(0, $this->getExitCode());

        $clazz = '\\tests\webfiori\\framework\scheduler\webServices\\GetTasksServiceTest';
        $this->assertTrue(file_exists($path.DS.'GetTasksServiceTest.php'));
        require_once $path.DS.'GetTasksServiceTest.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase04() {
        $path = ROOT_PATH.DS."tests".DS."tests".DS."apis".DS."multiple";
        $this->assertEquals([
            "Please enter services manager information:\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: tests\tests\apis\multiple\WebService00Test'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\\tests\apis\multiple'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'say-hi-service',
        ], [
            '\\tests\\apis\\multiple\\ServicesManager00',
            'n',
            '10',
            '',
            '',
        ]));
        $this->assertEquals(0, $this->getExitCode());

        $clazz = '\\tests\\tests\\apis\\multiple\\WebService00Test';
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
            '--manager' => '\\tests\\apis\\emptyService\\EmptyServicesManager',
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
            'Name: tests\tests\apis\multiple\WebService00Test'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\\tests\apis\multiple'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'say-hi-service',
        ], [
            '\\tests\\apis\\multiple\\WebService00',
            '\\tests\\apis\\multiple\\ServicesManager00',
            'n',
            '10',
            '',
            '',
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\tests\\tests\\apis\\multiple\\WebService00Test';
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
            "Error: The argument --manager has invalid value.\n",
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--manager' => '\\tests\\apis\\emptyService\\Xyz',
        ]));
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCreateAPITestCase08() {
        $path = ROOT_PATH.DS."tests".DS."tests".DS."apis".DS."multiple";
        $this->assertEquals([
            "Info: New class was created at \"".$path."\".\n"
        ], $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'api-test',
            '--service' => 'say-hi-service-2',
            '--manager' => '\\tests\\apis\\multiple\\ServicesManager00',
            '--defaults'
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $clazz = '\\tests\\tests\\apis\\multiple\\WebService01Test';
        $this->assertTrue(file_exists($path.DS.'WebService01Test.php'));
        require_once $path.DS.'WebService01Test.php';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
}
