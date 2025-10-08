<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;

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
            'WebFiori',
            'create',
            '--c' => 'db'
        ], [
            'Tables\\EmployeeInfoTable',
            'EmployeeOperations',
            "\n", // Hit Enter to pick default value (App\database)
            'SuperUser',
            "\n", // Hit Enter to pick default value (App\entity)
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "We need from you to give us class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Database'\n",
            "Warning: No database connections were found. Make sure to specify connection later inside the class.\n",
            "We need from you to give us entity class information.\n",
            "Entity class name:\n",
            "Entity namespace: Enter = 'App\\Entity'\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Info: New class was created at \"". ROOT_PATH.DS."app".DS."database\".\n"
        ], $output);
        $clazz = '\\App\\Database\\EmployeeOperationsDB';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
    
    /**
     * @test
     */
    public function test01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'db'
        ], [
            'Tables\\EmployeeInfoTable',
            'EmployeeS',
            'App\\Database\\Empl',
            'SuperHero',
            'App\\Entity\\Subs',
            'y'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "We need from you to give us class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Database'\n",
            "Warning: No database connections were found. Make sure to specify connection later inside the class.\n",
            "We need from you to give us entity class information.\n",
            "Entity class name:\n",
            "Entity namespace: Enter = 'App\\Entity'\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Info: New class was created at \"". ROOT_PATH.DS."app".DS."database".DS."empl\".\n"
        ], $output);
        $clazz = '\\App\\Database\\Empl\\EmployeeSDB';
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
            'WebFiori',
            'create',
            '--c' => 'db'
        ], [
            'Tables\\PositionInfoTable',
            'Position2x',
            'App\\Database',
            '0',
            'SuperPosition',
            'App\\Entity\\subs',
            'y'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "We need from you to give us class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Database'\n",
            "Select database connecion to use with the class:\n",
            "0: Test Connection\n",
            "1: None <--\n",
            "We need from you to give us entity class information.\n",
            "Entity class name:\n",
            "Entity namespace: Enter = 'App\\Entity'\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Info: New class was created at \"". ROOT_PATH.DS."app".DS."database\".\n"
        ], $output);
        $clazz = '\\App\\Database\\Position2xDB';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
    }
}
