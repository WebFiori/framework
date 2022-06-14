<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\File;
use webfiori\framework\cli\ArrayInputStream;
use webfiori\framework\cli\ArrayOutputStream;
use webfiori\framework\cli\Runner;
/**
 * Description of TestCreateCommand
 *
 * @author Ibrahim
 */
class CreateCommandTest extends TestCase {
    /**
     * @test
     */
    public function testCreateBackgroundJob00() {
        Runner::setInputStream(new ArrayInputStream([
            '3',
            'SuperCoolJob',
            'app\jobs',
            'app\jobs',
            'The Greatest Job',
            'The job will do nothing.',
            'N',
            '',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\jobs\"\n",
            "Where would you like to store the ". "class? (must be a directory inside '".ROOT_DIR."') Enter ="." \"app\jobs\"\n",
            "Enter a name for the job:\n",
            "Provide short description of what does the job will do:\n",
            "Would you like to add arguments to the job?(y/N)\n",
            "Info: New class was created at \"".ROOT_DIR.DS.'app'.DS."jobs\".\n",
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertTrue(class_exists('\\app\\jobs\\SuperCoolJob'));
        $this->removeClass('\\app\\jobs\\SuperCoolJob');
    }
    /**
     * @test
     */
    public function testCreateTable00() {
        Runner::setInputStream(new ArrayInputStream([
            '0',
            'mysql',
            'Cool00Table',
            '',
            '',
            'cool_table_00',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'n'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertTrue(class_exists('\\app\\database\\Cool00Table'));
        $this->removeClass('\\app\\database\\Cool00Table');
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
            "Where would you like to store the ". "class? (must be a directory inside '".ROOT_DIR."') Enter ="." \"app\database\"\n",
            "Enter database table name:\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: char <--\n",
            "1: int\n",
            "2: varchar\n",
            "3: timestamp\n",
            "4: tinyblob\n",
            "5: blob\n",
            "6: mediumblob\n",
            "7: longblob\n",
            "8: datetime\n",
            "9: text\n",
            "10: mediumtext\n",
            "11: decimal\n",
            "12: double\n",
            "13: float\n",
            "14: boolean\n", 
            "15: bool\n",
            "16: bit\n",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."database\".\n",
        ], Runner::getOutputStream()->getOutputArray());
        
    }
    /**
     * @test
     */
    public function testCreateMiddleware00() {
        Runner::setInputStream(new ArrayInputStream([
            '4',
            'NewCoolMd',
            'app\middleware',
            'app\middleware',
            'Check is authorized',
            '22',
            '',
            '',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\middleware\"\n",
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter ="." \"app\middleware\"\n",
            "Enter a name for the middleware:\n",
            "Enter middleware priority: Enter = \"0\"\n",
            "Would you like to add the middleware to a group?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."middleware\".\n",
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertTrue(class_exists('\\app\\middleware\\NewCoolMdMiddleware'));
        $this->removeClass('\\app\\middleware\\NewCoolMdMiddleware');
    }
    /**
     * @test
     */
    public function testCreateCommand00() {
        Runner::setInputStream(new ArrayInputStream([
            '6',
            'NewCLI',
            'app\commands',
            'app\commands',
            'print-hello',
            'Prints \'Hello World\' in the console.',
            'N',
            '',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\commands\"\n",
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter ="." \"app\commands\"\n",
            "Enter a name for the command:\n",
            "Give a short description of the command:\n",
            "Would you like to add arguments to the command?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."commands\".\n",
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertTrue(class_exists('\\app\\commands\\NewCLICommand'));
        $this->removeClass('\\app\\commands\\NewCLICommand');
    }
    /**
     * @test
     */
    public function testCreateWebService00() {
        Runner::setInputStream(new ArrayInputStream([
            '2',
            'NewWeb',
            '',
            '',
            'get-hello',
            '0',
            'y',
            '6',
            'name',
            'n',
            'n',
            '',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\apis\"\n",
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter ="." \"app\apis\"\n",
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
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertTrue(class_exists('\\app\\apis\\NewWebService'));
        $this->removeClass('\\app\\apis\\NewWebService');
    }
    /**
     * @test
     */
    public function testCreateTheme00() {
        Runner::setInputStream(new ArrayInputStream([
            '7',
            'NewTest',
            'themes\\fiori',
            'themes\\fiori',
            '',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"themes\"\n",
            "Where would you like to store the ". "class? (must be a directory inside '".ROOT_DIR."') Enter ="." \"themes\\fiori\"\n",
            'Creating theme at "'.ROOT_DIR.DS.'themes'.DS."fiori\"...\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'themes'.DS."fiori\".\n",
        ], Runner::getOutputStream()->getOutputArray());

        $this->assertTrue(class_exists('\\themes\\fiori\\NewTestTheme'));
        $this->removeClass('\\themes\\fiori\\NewTestTheme');
        $this->removeClass('\\themes\\fiori\\AsideSection');
        $this->removeClass('\\themes\\fiori\\FooterSection');
        $this->removeClass('\\themes\\fiori\\HeadSection');
        $this->removeClass('\\themes\\fiori\\HeaderSection');
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_DIR.$classPath.'.php');
        $file->remove();
    }
}
