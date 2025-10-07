<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Middleware\AbstractMiddleware;

/**
 * Description of CreateMiddlewareTest
 *
 * @author Ibrahim
 */
class CreateMiddlewareTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateMiddleware00() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '4',
            'NewCoolMd',
            'App\Middleware',
            'Check is authorized',
            '22',
            "\n", // Hit Enter to pick default value (no group)
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Database migration.\n",
            "11: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Middleware'\n",
            "Enter a name for the middleware:\n",
            "Enter middleware priority: Enter = '0'\n",
            "Would you like to add the middleware to a group?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."middleware\".\n",
        ], $output);
        $this->assertTrue(class_exists('\\App\\Middleware\\NewCoolMdMiddleware'));
        $this->removeClass('\\App\\Middleware\\NewCoolMdMiddleware');
    }
    
    /**
     * @test
     */
    public function testCreateMiddleware01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'middleware'
        ], [
            'NewCool',
            'App\Middleware',
            '  ', // Invalid input (spaces only)
            'Check is cool',
            '22',
            'y',
            'global',
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Middleware'\n",
            "Enter a name for the middleware:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter a name for the middleware:\n",
            "Enter middleware priority: Enter = '0'\n",
            "Would you like to add the middleware to a group?(y/N)\n",
            "Enter group name:\n",
            "Would you like to add the middleware to another group?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."middleware\".\n",
        ], $output);
        
        $clazz = '\\App\\Middleware\\NewCoolMiddleware';
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof AbstractMiddleware);
        $this->assertEquals(22, $clazzObj->getPriority());
        $this->assertEquals(['global'], $clazzObj->getGroups());
        $this->assertEquals('Check is cool', $clazzObj->getName());
        $this->removeClass($clazz);
    }
}
