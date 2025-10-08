<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\ThemeManager;

/**
 * Description of CreateThemeTest
 *
 * @author Ibrahim
 */
class CreateThemeTest extends CLITestCase {
    /**
     * @test
     */
    public function testCreateTheme00() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '6',
            'NewTest',
            'Themes\\Fiori',
            "\n", // Hit Enter to pick default value
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
            "Enter an optional namespace for the class: Enter = 'Themes'\n",
            'Creating theme at "'.ROOT_PATH.DS.'Themes'.DS."fiori\"...\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'Themes'.DS."fiori\".\n",
        ], $output);

        $this->assertTrue(class_exists('\\Themes\\Fiori\\NewTestTheme'));
        
        $this->removeClass('\\Themes\\Fiori\\NewTestTheme');
        $this->removeClass('\\Themes\\Fiori\\AsideSection');
        $this->removeClass('\\Themes\\Fiori\\FooterSection');
        $this->removeClass('\\Themes\\Fiori\\HeadSection');
        $this->removeClass('\\Themes\\Fiori\\HeaderSection');
    }
    
    /**
     * @test
     */
    public function testCreateThemeWithExistingName() {
        $runner = App::getRunner();
        $ns = '\\Themes\\FioriTheme';
        $name = 'NewFTestTheme';

        $ns2 = '\\Themes\\Cool';
        $name2 = 'NewFTestTheme';
        
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '6',
            $name,
            $ns,
            $name2,
            $ns2,
            "\n" // Hit Enter to pick default value
        ]);
        
        // Verify exact output array for duplicate theme creation attempt
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
            "Enter an optional namespace for the class: Enter = 'Themes'\n",
            "Error: A class in the given namespace which has the given name was found.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'Themes'\n",
            'Creating theme at "'.ROOT_PATH.DS.'Themes'.DS."cool\"...\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'Themes'.DS."cool\".\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $this->removeClass($ns2.'\\'.$name2);
        $this->removeClass($ns2.'\\AsideSection');
        $this->removeClass($ns2.'\\FooterSection');
        $this->removeClass($ns2.'\\HeadSection');
        $this->removeClass($ns2.'\\HeaderSection');
    }
}
