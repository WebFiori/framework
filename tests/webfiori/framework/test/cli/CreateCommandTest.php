<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\file\File;
use webfiori\cli\Runner;
/**
 * Description of TestCreateCommand
 *
 * @author Ibrahim
 */
class CreateCommandTest extends TestCase {
    /**
     * @test
     */
    public function testCreate00() {
        $runner = new Runner();
        $runner->setInput([
            '7',
        ]);
        
        $this->assertEquals(0, $runner->runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Quit. <--\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testCreate01() {
        $runner = new Runner();
        $runner->setInput([
            '',
        ]);
        
        $this->assertEquals(0, $runner->runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Quit. <--\n",
        ], $runner->getOutput());
    }
    
    
    /**
     * @test
     */
    public function testCreateMiddleware00() {
        $runner = new Runner();
        $runner->setInput([
            '4',
            'NewCoolMd',
            'app\middleware',
            'Check is authorized',
            '22',
            '',
            '',
        ]);
        
        $this->assertEquals(0, $runner->runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\middleware'\n",
            "Enter a name for the middleware:\n",
            "Enter middleware priority: Enter = '0'\n",
            "Would you like to add the middleware to a group?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."middleware\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\middleware\\NewCoolMdMiddleware'));
        $this->removeClass('\\app\\middleware\\NewCoolMdMiddleware');
    }
    /**
     * @test
     */
    public function testCreateCommand00() {
        $runner = new Runner();
        $runner->setInput([
            '5',
            'NewCLI',
            'app\commands',
            'print-hello',
            'Prints \'Hello World\' in the console.',
            'N',
            '',
        ]);
        
        $this->assertEquals(0, $runner->runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\commands'\n",
            "Enter a name for the command:\n",
            "Give a short description of the command:\n",
            "Would you like to add arguments to the command?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."commands\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\commands\\NewCLICommand'));
        $this->removeClass('\\app\\commands\\NewCLICommand');
    }
    /**
     * @test
     */
    public function testCreateWebService00() {
        $runner = new Runner();
        $runner->setInput([
            '2',
            'NewWeb',
            '',
            'get-hello',
            '0',
            'y',
            '6',
            'name',
            'n',
            'n',
            '',
        ]);
        
        $this->assertEquals(0, $runner->runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\apis'\n",
            "Enter a name for the new web service:\n",
            "Request method:\n",
            "0: GET <--\n",
            "1: HEAD\n",
            "2: POST\n",
            "3: PUT\n",
            "4: DELETE\n",
            "5: TRACE\n",
            "6: OPTIONS\n",
            "7: PATCH\n",
            "8: CONNECT\n",
            "Would you like to add request parameters to the service?(y/N)\n",
            "Choose parameter type:\n",
            "0: array <--\n",
            "1: boolean\n",
            "2: email\n",
            "3: double\n",
            "4: integer\n",
            "5: json-obj\n",
            "6: string\n",
            "7: url\n",
            "Enter a name for the request parameter:\n",
            "Is this parameter optional?(Y/n)\n",
            "Success: New parameter added to the service 'get-hello'.\n",
            "Would you like to add another parameter?(y/N)\n",
            "Creating the class...\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."apis\".\n",
            "Info: Don't forget to add the service to a services manager.\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\apis\\NewWebService'));
        $this->removeClass('\\app\\apis\\NewWebService');
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_DIR.$classPath.'.php');
        $file->remove();
    }
}
