<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\App;

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
            'webfiori',
            'list-themes'
        ]);
        $runner->start();
        $this->assertEquals([
            "Total Number of Themes: 2 .\n",
            "--------- Theme #01 ---------\n\n",
            "Theme Name:     : New Super Theme\n",
            "Author:         : <NOT SET>\n",
            "Author URL:     : <NOT SET>\n",
            "License:        : <NOT SET>\n",
            "License URL:    : <NOT SET>\n",
            "Theme Desription: <NOT SET>\n",
            "--------- Theme #02 ---------\n\n",
            "Theme Name:     : New Theme 2\n",
            "Author:         : Ibrahim Ali\n",
            "Author URL:     : https://ibrahim-binalshikh.me\n",
            "License:        : MIT\n",
            "License URL:    : https://opensource.org/licenses/MIT\n",
            "Theme Desription: Colofull Theme.\n",
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
            'webfiori',
            'list-themes',
            '--theme-name="Not Exist"'
        ]);
        $runner->start();
        $this->assertEquals([
            "Error: No theme was registered which has the name 'Not Exist'.\n",
        ], $runner->getOutput());
    }
}
