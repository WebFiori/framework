<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Page;
/**
 * Description of PageTest
 *
 * @author Ibrahim
 */
class PageTest extends TestCase{
    /**
     * @test
     */
    public function test00() {
        $page = Page::get();
        $page2 = Page::get();
        $this->assertSame($page,$page2);
        $this->assertTrue($page === $page2);
        $this->assertSame(Page::document(),$page->document());
    }
    /**
     * @test
     */
    public function test01() {
        $page = Page::get();
        $page2 = Page::get();
        $page->title('Hello World');
        $this->assertEquals('Hello World',$page2->title());
    }
}
