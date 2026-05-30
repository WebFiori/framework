<?php
namespace WebFiori\Framework\Test\Ui;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Ui\HTTPCodeView;

class HTTPCodeViewTest extends TestCase {
    public function testCreate404View() {
        $view = new HTTPCodeView(404);
        $this->assertStringContainsString('404', $view->getTitle());
    }
    public function testCreate500View() {
        $view = new HTTPCodeView(500);
        $this->assertStringContainsString('500', $view->getTitle());
    }
    public function testCreate405View() {
        $view = new HTTPCodeView(405);
        $this->assertStringContainsString('405', $view->getTitle());
    }
}
