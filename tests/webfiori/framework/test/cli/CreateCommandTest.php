<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\CreateCommand;
/**
 * Description of TestCreateCommand
 *
 * @author Ibrahim
 */
class CreateCommandTest extends TestCase {

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
        $this->assertTrue(class_exists('app\\jobs\\SuperCoolJob'));
    }

}
