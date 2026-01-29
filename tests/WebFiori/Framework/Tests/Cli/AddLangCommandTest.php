<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\AddLangCommand;
use WebFiori\Framework\Config\Controller;

/**
 * Test cases for AddLangCommand
 *
 * @author Ibrahim
 */
class AddLangCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testAddLang00() {
        // Generate a unique 2-character language code based on current microseconds
        $langCode = substr(str_replace('.', '', microtime(true)), -2);
        // Ensure it's exactly 2 characters and alphabetic
        $langCode = chr(65 + ($langCode[0] % 26)) . chr(65 + ($langCode[1] % 26));
        
        // Clean up if it exists from previous runs
        if (class_exists('\\App\\Langs\\Lang' . $langCode)) {
            $this->removeClass('\\App\\Langs\\Lang' . $langCode);
        }
        
        $output = $this->executeSingleCommand(new AddLangCommand(), [], [
            $langCode,
            'F Name',
            'F description',
            'Default f Title',
            'ltr',
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Language code:\n",
            "Name of the website in the new language:\n",
            "Description of the website in the new language:\n",
            "Default page title in the new language:\n",
            "Select writing direction:\n",
            "0: ltr\n",
            "1: rtl\n",
            "Success: Language added. Also, a class for the language is created at \"".APP_DIR."\Langs\" for that language.\n"
        ], $output);
        $this->assertTrue(class_exists('\\App\\Langs\\Lang' . $langCode));
        $this->removeClass('\\App\\Langs\\Lang' . $langCode);
        Controller::getDriver()->initialize();
    }
    /**
     * @test
     */
    public function testAddLang01() {
        $output = $this->executeSingleCommand(new AddLangCommand(), [], [
            'EN',
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Language code:\n",
            "Info: This language already added. Nothing changed.\n",
        ], $output);
        Controller::getDriver()->initialize();
    }
    /**
     * @test
     */
    public function testAddLang02() {
        $output = $this->executeSingleCommand(new AddLangCommand(), [], [
            'FKRR',
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Language code:\n",
            "Error: Invalid language code.\n",
        ], $output);
        $this->removeClass('\\App\\Langs\\LanguageFK');
    }
}
