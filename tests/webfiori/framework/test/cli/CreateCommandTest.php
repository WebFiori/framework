<?php
namespace webfiori\framework\test\cli;

use app\database\TestTable;
use webfiori\framework\WebFioriApp;
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
        $runner = WebFioriApp::getRunner();
        $runner->setInput([
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
            "3: Background job.\n",
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
        $runner = $runner = WebFioriApp::getRunner();
        $runner->setInput([
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
            "3: Background job.\n",
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
        $runner = $runner = WebFioriApp::getRunner();
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
            "3: Background job.\n",
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
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."apis\".\n",
            "Info: Don't forget to add the service to a services manager.\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\apis\\NewWebService'));
        $this->removeClass('\\app\\apis\\NewWebService');
    }
    /**
     * @test
     */
    public function testCreateEntity00() {
        $runner = $runner = WebFioriApp::getRunner();
        $runner->setInput([
            'NeEntity',
            '',
            'y',
            'y',
            'superNewAttr',
            'y',
            'superNewAttr',
            'y',
            'invalid name',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'entity',
            '--table' => TestTable::class
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "We need from you to give us entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Enter attribute name:\n",
            "Success: Attribute successfully added.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",
            
            "Would you like to add another attribute?(y/N)\n",
            "Generating your entity...\n",
            "Success: Entity class created.\n"
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\entity\\NeEntity'));
        $this->removeClass('\\app\\entity\\NeEntity');
    }
    /**
     * @test
     */
    public function testCreateEntity01() {
        $runner = $runner = WebFioriApp::getRunner();
        $runner->setInput([
            '1',
            'NewEntity',
            '           ',
            'y',
            'y',
            'superNewAttr',
            'y',
            'superNewAttr',
            'y',
            'invalid name',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'entiy',
            '--table' => TestTable::class
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Warning: The argument --c has invalid value.\n",
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Quit. <--\n",
            "We need from you to give us entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Enter attribute name:\n",
            "Success: Attribute successfully added.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",
            "Would you like to add another attribute?(y/N)\n",
            "Enter attribute name:\n",
            "Warning: Unable to add attribute.\n",
            
            "Would you like to add another attribute?(y/N)\n",
            "Generating your entity...\n",
            "Success: Entity class created.\n"
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\entity\\NewEntity'));
        $this->removeClass('\\app\\entity\\NewEntity');
    }
}
