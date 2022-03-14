<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\File;
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
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input-streams'.DS.'create-job-00.txt', TESTS_PATH.DS.'output.txt');
        $commandRunner->runCommand(new CreateCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'What would you like to create?',
            '0: Database table class.',
            '1: Entity class from table.',
            '2: Web service.',
            '3: Background job.',
            '4: Middleware.',
            '5: Database table from class.',
            '6: CLI Command.',
            '7: Theme.',
            '8: Quit. <--',
            'Enter a name for the new class:',
            'Enter an optional namespace for the class: Enter = "app\jobs"',
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter =".' "app\jobs"',
            'Enter a name for the job:',
            'Provide short description of what does the job will do:',
            'Would you like to add arguments to the job?(y/N)',
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS.'jobs".',
            ""
        ], $this));
        $this->assertTrue(class_exists('\\app\\jobs\\SuperCoolJob'));
        $this->removeClass('\\app\\jobs\\SuperCoolJob');
    }
    /**
     * @test
     */
    public function testCreateMiddleware00() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input-streams'.DS.'create-middleware-00.txt', TESTS_PATH.DS.'output.txt');
        $commandRunner->runCommand(new CreateCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'What would you like to create?',
            '0: Database table class.',
            '1: Entity class from table.',
            '2: Web service.',
            '3: Background job.',
            '4: Middleware.',
            '5: Database table from class.',
            '6: CLI Command.',
            '7: Theme.',
            '8: Quit. <--',
            'Enter a name for the new class:',
            'Enter an optional namespace for the class: Enter = "app\middleware"',
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter =".' "app\middleware"',
            'Enter a name for the middleware:',
            'Enter middleware priority: Enter = "0"',
            'Would you like to add the middleware to a group?(y/N)',
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS.'middleware".',
            ""
        ], $this));
        $this->assertTrue(class_exists('\\app\\middleware\\NewCoolMdMiddleware'));
        $this->removeClass('\\app\\middleware\\NewCoolMdMiddleware');
    }
    /**
     * @test
     */
    public function testCreateCommand00() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input-streams'.DS.'create-command-00.txt', TESTS_PATH.DS.'output.txt');
        $commandRunner->runCommand(new CreateCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'What would you like to create?',
            '0: Database table class.',
            '1: Entity class from table.',
            '2: Web service.',
            '3: Background job.',
            '4: Middleware.',
            '5: Database table from class.',
            '6: CLI Command.',
            '7: Theme.',
            '8: Quit. <--',
            'Enter a name for the new class:',
            'Enter an optional namespace for the class: Enter = "app\commands"',
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter =".' "app\commands"',
            'Enter a name for the command:',
            'Give a short description of the command:',
            'Would you like to add arguments to the command?(y/N)',
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS.'commands".',
            ""
        ], $this));
        $this->assertTrue(class_exists('\\app\\commands\\NewCLICommand'));
        $this->removeClass('\\app\\commands\\NewCLICommand');
    }
    /**
     * @test
     */
    public function testCreateWebService00() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input-streams'.DS.'create-web-service-00.txt', TESTS_PATH.DS.'output.txt');
        $commandRunner->runCommand(new CreateCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'What would you like to create?',
            '0: Database table class.',
            '1: Entity class from table.',
            '2: Web service.',
            '3: Background job.',
            '4: Middleware.',
            '5: Database table from class.',
            '6: CLI Command.',
            '7: Theme.',
            '8: Quit. <--',
            'Enter a name for the new class:',
            'Enter an optional namespace for the class: Enter = "app\apis"',
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter =".' "app\apis"',
            'Enter a name for the new web service:',
            'Request method:',
            '0: GET <--',
            '1: HEAD',
            '2: POST',
            '3: PUT',
            '4: DELETE',
            '5: TRACE',
            '6: OPTIONS',
            '7: PATCH',
            '8: CONNECT',
            'Would you like to add request parameters to the service?(y/N)',
            'Choose parameter type:',
            '0: array <--',
            '1: boolean',
            '2: email',
            '3: double',
            '4: integer',
            '5: json-obj',
            '6: string',
            '7: url',
            'Enter a name for the request parameter:',
            'Is this parameter optional?(Y/n)',
            'Success: New parameter added to the service \'get-hello\'.',
            'Would you like to add another parameter?(y/N)',
            'Creating the class...',
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS.'apis".',
            'Info: Don\'t forget to add the service to a services manager.',
            ""
        ], $this));
        $this->assertTrue(class_exists('\\app\\apis\\NewWebService'));
        $this->removeClass('\\app\\apis\\NewWebService');
    }
    /**
     * @test
     */
    public function testCreateTheme00() {
        $commandRunner = new CommandRunner(TESTS_PATH.DS.'input-streams'.DS.'create-theme-00.txt', TESTS_PATH.DS.'output.txt');
        $commandRunner->runCommand(new CreateCommand());
        $this->assertEquals(0, $commandRunner->getExitStatus());
        $this->assertTrue($commandRunner->isOutputEquals([
            'What would you like to create?',
            '0: Database table class.',
            '1: Entity class from table.',
            '2: Web service.',
            '3: Background job.',
            '4: Middleware.',
            '5: Database table from class.',
            '6: CLI Command.',
            '7: Theme.',
            '8: Quit. <--',
            'Enter a name for the new class:',
            'Enter an optional namespace for the class: Enter = "themes"',
            'Where would you like to store the '. "class? (must be a directory inside '".ROOT_DIR."') Enter =".' "themes\fiori"',
            'Creating theme at "'.ROOT_DIR.DS.'themes'.DS.'fiori"...',
            'Info: New class was created at "'.ROOT_DIR.DS.'themes'.DS.'fiori".',
            ""
        ], $this));
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
