<?php

namespace webfiori\framework\test\cli;

use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\framework\WebFioriApp;

/**
 * Description of CreateThemeTest
 *
 * @author Ibrahim
 */
class MiddlewareTest extends CreateTestCase {
    /**
     * @test
     */
    public function testCreateMiddleware00() {
        $runner = $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create'
        ]);
        $runner->setInput([
            '4',
            'NewCoolMd',
            'app\middleware',
            'Check is authorized',
            '22',
            '',
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
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\middleware'\n",
            "Enter a name for the middleware:\n",
            "Enter middleware priority: Enter = '0'\n",
            "Would you like to add the middleware to a group?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."middleware\".\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\middleware\\NewCoolMdMiddleware'));
        $this->removeClass('\\app\\middleware\\NewCoolMdMiddleware');
    }
    /**
     * @test
     */
    public function testCreateMiddleware01() {
        $runner = $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'middleware'
        ]);
        $runner->setInput([
            'NewCool',
            'app\middleware',
            'Check is cool',
            '22',
            'y',
            'global',
            'n'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\middleware'\n",
            "Enter a name for the middleware:\n",
            "Enter middleware priority: Enter = '0'\n",
            "Would you like to add the middleware to a group?(y/N)\n",
            "Enter group name:\n",
            "Would you like to add the middleware to another group?(y/N)\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'app'.DS."middleware\".\n",
        ], $runner->getOutput());
        $clazz = '\\app\\middleware\\NewCoolMiddleware';
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof AbstractMiddleware);
        $this->assertEquals(22, $clazzObj->getPriority());
        $this->assertEquals(['global'], $clazzObj->getGroups());
        $this->assertEquals('Check is cool', $clazzObj->getName());
        $this->removeClass($clazz);
    }
}
