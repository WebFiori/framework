<?php
namespace WebFiori\Framework\Test\Cli;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\App;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\UI\WebPage;
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
            'WebFiori',
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
            'WebFiori',
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
