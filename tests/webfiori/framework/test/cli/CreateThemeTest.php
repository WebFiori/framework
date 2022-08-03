<?php

namespace webfiori\framework\test\cli;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\WebFioriApp;

/**
 * Description of CreateThemeTest
 *
 * @author Ibrahim
 */
class CreateThemeTest extends CreateTestCase {
    /**
     * @test
     */
    public function testCreateTheme00() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInput([
            '6',
            'NewTest',
            'themes\\fiori',
            '',
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
            "8: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'themes'\n",
            'Creating theme at "'.ROOT_DIR.DS.'themes'.DS."fiori\"...\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'themes'.DS."fiori\".\n",
        ], $runner->getOutput());

        $this->assertTrue(class_exists('\\themes\\fiori\\NewTestTheme'));
        $this->removeClass('\\themes\\fiori\\NewTestTheme');
        $this->removeClass('\\themes\\fiori\\AsideSection');
        $this->removeClass('\\themes\\fiori\\FooterSection');
        $this->removeClass('\\themes\\fiori\\HeadSection');
        $this->removeClass('\\themes\\fiori\\HeaderSection');
    }
}
