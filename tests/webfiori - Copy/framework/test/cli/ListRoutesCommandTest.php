<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\App;
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
        Router::removeAll();
        $runner = App::getRunner();
        $runner->setInputs();
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
        $runner = App::getRunner();
        $runner->setInputs();
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
            "https://127.0.0.1/xyz   =>  ".Router::class."\n",
            "https://127.0.0.1/xyzb  =>  ".WebPage::class."\n"
        ], $runner->getOutput());
        Router::removeAll();
    }
}
