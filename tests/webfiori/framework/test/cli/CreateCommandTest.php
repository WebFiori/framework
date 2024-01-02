<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\App;
/**
 * Description of TestCreateCommand
 *
 * @author Ibrahim
 */
class CreateCommandTest extends CreateTestCase {
    /**
     * @test
     */
    public function testCreate00() {
        $runner = App::getRunner();
        $runner->setInputs([
            '9',
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $this->assertEquals(0, $runner->start());
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
            "9: Quit. <--\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testCreate01() {
        $runner = $runner = App::getRunner();
        $runner->setInputs([
            '',
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $this->assertEquals(0, $runner->start());
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
            "9: Quit. <--\n",
        ], $runner->getOutput());
    }




    /**
     * @test
     */
    public function testCreateWebService00() {
        $runner = $runner = App::getRunner();
        $runner->setInputs([
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
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $result = $runner->start();
        //$this->assertEquals(0, $result);
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
            "9: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\apis'\n",
            "Enter a name for the new web service:\n",
            "Request method:\n",
            "0: CONNECT\n",
            "1: DELETE\n",
            "2: GET <--\n",
            "3: HEAD\n",
            "4: OPTIONS\n",
            "5: POST\n",
            "6: PUT\n",
            "7: TRACE\n",
            
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
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."apis\".\n",
            "Info: Don't forget to add the service to a services manager.\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\apis\\NewWebService'));
        $this->removeClass('\\app\\apis\\NewWebService');
    }
}
