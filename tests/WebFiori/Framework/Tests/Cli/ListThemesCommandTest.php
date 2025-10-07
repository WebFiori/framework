<?php
namespace WebFiori\Framework\Test\Cli;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\App;

/**
 * Description of ListRoutesCommandTest
 *
 * @author Ibrahim
 */
class ListThemesCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'WebFiori',
            'list-themes'
        ]);
        $runner->start();
        $output = $runner->getOutput();
        $this->assertContains("Total Number of Themes: 2 .\n", $output);
        $this->assertContains("Theme Name:     : New Super Theme\n", $output);
        $this->assertContains("Theme Name:     : New Theme 2\n", $output);
    }
    /**
     * @test
     */
    public function test01() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'WebFiori',
            'list-themes',
            '--theme-name' => "New Super Theme"
        ]);
        $runner->start();
        $this->assertEquals([
            "Theme Name:     : New Super Theme\n",
            "Author:         : <NOT SET>\n",
            "Author URL:     : <NOT SET>\n",
            "License:        : <NOT SET>\n",
            "License URL:    : <NOT SET>\n",
            "Theme Desription: <NOT SET>\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function test02() {
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'WebFiori',
            'list-themes',
            '--theme-name="Not Exist"'
        ]);
        $runner->start();
        $this->assertEquals([
            "Error: No theme was registered which has the name 'Not Exist'.\n",
        ], $runner->getOutput());
    }
}
