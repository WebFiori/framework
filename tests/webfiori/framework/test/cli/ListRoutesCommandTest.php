<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\WebFioriApp;
use webfiori\framework\router\Router;
use webfiori\framework\ui\WebPage;
/**
 * Description of ListRoutesCommandTest
 *
 * @author Ibrahim
 */
class ListRoutesCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput();
        $runner->setArgsVector([
            'webfiori',
            'list-routes'
        ]);
        $runner->start();
        $this->assertEquals([
            
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test01() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput();
        $runner->setArgsVector([
            'webfiori',
            'list-routes'
        ]);
        Router::addRoute([
            'path' => 'xyz',
            'route-to' => Router::class
        ]);
        Router::addRoute([
            'path' => 'xyzb',
            'route-to' => new WebPage()
        ]);
        $runner->start();
        $this->assertEquals([
            "https://example.com/xyz   =>  ".Router::class."\n",
            "https://example.com/xyzb  =>  ".WebPage::class."\n"
        ], $runner->getOutput());
        Router::removeAll();
    }
}
