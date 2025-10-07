<?php
namespace webfiori\framework\test\cli;

use WebFiori\Database\ConnectionInfo;
use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\CreateCommand;

/**
 * Description of CreateDBAccessTest
 *
 * @author Ibrahim
 */
class CreateDBAccessTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        App::getConfig()->removeAllDBConnections();
        
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create',
            '--c' => 'db'
        ], [
            'tables\\EmployeeInfoTable',
            'EmployeeOperations',
            "\n", // Hit Enter to pick default value (app\database)
            'SuperUser',
            "\n", // Hit Enter to pick default value (app\entity)
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "We need from you to give us class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Warning: No database connections were found. Make sure to specify connection later inside the class.\n",
            "We need from you to give us entity class information.\n",
            "Entity class name:\n",
            "Entity namespace: Enter = 'app\\entity'\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Info: New class was created at \"". ROOT_PATH.DS."app".DS."database\".\n"
        ], $output);
        $clazz = '\\app\\database\\EmployeeOperationsDB';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function test01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create',
            '--c' => 'db'
        ], [
            'tables\\EmployeeInfoTable',
            'EmployeeS',
            'app\\database\\empl',
            'SuperHero',
            'app\\entity\\subs',
            'y'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "We need from you to give us class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Warning: No database connections were found. Make sure to specify connection later inside the class.\n",
            "We need from you to give us entity class information.\n",
            "Entity class name:\n",
            "Entity namespace: Enter = 'app\\entity'\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Info: New class was created at \"". ROOT_PATH.DS."app".DS."database".DS."empl\".\n"
        ], $output);
        $clazz = '\\app\\database\\empl\\EmployeeSDB';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function test02() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1', 3306);
        $conn->setName('Test Connection');
        App::getConfig()->removeAllDBConnections();
        App::getConfig()->addOrUpdateDBConnection($conn);

        $output = $this->executeSingleCommand(new CreateCommand(), [
            'webfiori',
            'create',
            '--c' => 'db'
        ], [
            'tables\\PositionInfoTable',
            'Position2x',
            'app\\database',
            '0',
            'SuperPosition',
            'app\\entity\\subs',
            'y'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "We need from you to give us class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\database'\n",
            "Select database connecion to use with the class:\n",
            "0: Test Connection\n",
            "1: None <--\n",
            "We need from you to give us entity class information.\n",
            "Entity class name:\n",
            "Entity namespace: Enter = 'app\\entity'\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Info: New class was created at \"". ROOT_PATH.DS."app".DS."database\".\n"
        ], $output);
        $clazz = '\\app\\database\\Position2xDB';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
}
