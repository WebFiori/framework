<?php
namespace webfiori\framework\test\cli;

use webfiori\cli\CLICommand;
use webfiori\framework\App;
/**
 * @author Ibrahim
 */
class CreateCLICommandTest extends CreateTestCase {
    /**
     * @test
     */
    public function testCreateCommand00() {
        $runner = $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInputs([
            '5',
            'NewCLI',
            'app\commands',
            'print-hello',
            'Prints \'Hello World\' in the console.',
            'N',
            '',
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
            "9: Web service test case.\n",
            "10: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\commands'\n",
            "Enter a name for the command:\n",
            "Give a short description of the command:\n",
            "Would you like to add arguments to the command?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."commands\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\commands\\NewCLICommand'));
        $this->removeClass('\\app\\commands\\NewCLICommand');
    }
    /**
     * @test
     */
    public function testCreateCommand01() {
        $runner = $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'command'
        ]);
        $runner->setInputs([
            'DoIt',
            'app\commands',
            'do-it',
            'Do something amazing.',
            'y',
            '--what-to-do',
            "The thing that the command will do.",
            "y",
            "Say Hi",
            "y",
            "Say No",
            "y",
            "Say No",
            'n',
            'y',
            '',
            'n'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\commands'\n",
            "Enter a name for the command:\n",
            "Give a short description of the command:\n",
            "Would you like to add arguments to the command?(y/N)\n",
            "Enter argument name:\n",
            "Describe this argument and how to use it: Enter = ''\n",
            "Does this argument have a fixed set of values?(y/N)\n",
            "Enter the value:\n",
            "Would you like to add more values?(y/N)\n",
            "Enter the value:\n",
            "Would you like to add more values?(y/N)\n",
            "Enter the value:\n",
            "Info: Given value was already added.\n",
            "Would you like to add more values?(y/N)\n",
            "Is this argument optional or not?(Y/n)\n",
            "Enter default value:\n",
            "Would you like to add more arguments?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."commands\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\commands\\DoItCommand';
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof CLICommand);
        $this->assertEquals('do-it', $clazzObj->getName());
        $arg = $clazzObj->getArg('--what-to-do');
        $this->assertNotNull($arg);
        $this->assertEquals([
            'Say Hi', 'Say No'
        ], $arg->getAllowedValues());
        $this->assertTrue($arg->isOptional());
        $this->assertEquals('The thing that the command will do.', $arg->getDescription());
        $this->assertEquals('', $arg->getDefault());
        $this->removeClass($clazz);
    }
}
