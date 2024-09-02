<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\App;
use webfiori\framework\scheduler\webServices\TasksServicesManager;
/**
 * @author Ibrahim
 */
class CreateAPITestCaseTest extends CreateTestCase {
    /**
     * @test
     */
    public function testCreateAPITestCase00() {
        $runner = $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'api-test',
            '--manager' => 'A',
            '--service' => 'c'
        ]);
        $runner->setInputs([
            
        ]);
        $exitCode = $runner->start();
        $this->assertEquals(-1, $exitCode);
        $this->assertEquals([
            "Error: The argument --manager has invalid value.\n",
            
        ], $runner->getOutput());
//        $this->assertTrue(class_exists('\\app\\commands\\NewCLICommand'));
//        $this->removeClass('\\app\\commands\\NewCLICommand');
    }
    /**
     * @test
     */
    public function testCreateAPITestCase01() {
        $runner = $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'api-test',
            '--manager' => TasksServicesManager::class,
            '--service' => 'c'
        ]);
        $runner->setInputs([
            "0",
            "y"
        ]);
        $exitCode = $runner->start();
        $this->assertEquals(0, $exitCode);
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
            'Name: \\tests\webfiori\\framework\scheduler\webServices\\TasksLoginServiceTest'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $runner->getOutput());
        $clazz = '\tests\webfiori\\framework\scheduler\webServices\\TasksLoginServiceTest';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase02() {
        $runner = $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'api-test',
            '--manager' => TasksServicesManager::class,
            '--service' => 'get-tasks',
            '--defaults'
        ]);
        $runner->setInputs([
        ]);
        $exitCode = $runner->start();
        $this->assertEquals(0, $exitCode);
        $path = ROOT_PATH.DS."tests".DS."webfiori".DS."framework".DS."scheduler".DS."webServices";
        $this->assertEquals([
            "Info: New class was created at \"".$path."\".\n"
        ], $runner->getOutput());
        $clazz = '\tests\webfiori\\framework\scheduler\webServices\\GetTasksServiceTest';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testCreateAPITestCase03() {
        $runner = $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'api-test',
            '--service' => 'get-tasks',
        ]);
        $runner->setInputs([
            '\webfiori\\framework\scheduler\webServices\\TasksServicesManager',
            'n',
            '10',
            '',
            '',
            
        ]);
        $exitCode = $runner->start();
        $this->assertEquals(0, $exitCode);
        $path = ROOT_PATH.DS."tests".DS."webfiori".DS."framework".DS."scheduler".DS."webServices";
        $this->assertEquals([
            "Please enter services manager information:\n",
            "Test case will be created with following parameters:\n",
            "PHPUnit Version: 9\n",
            'Name: \\tests\webfiori\\framework\scheduler\webServices\\GetTasksServiceTest'."\n",
            "Path: ".$path."\n",
            "Would you like to use default parameters?(Y/n)\n",
            "PHPUnit Version: Enter = '11'\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'tests\webfiori\\framework\scheduler\webServices'\n",
            "Info: New class was created at \"".$path."\".\n"
        ], $runner->getOutput());
        $clazz = '\tests\webfiori\\framework\scheduler\webServices\\GetTasksServiceTest';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
}
