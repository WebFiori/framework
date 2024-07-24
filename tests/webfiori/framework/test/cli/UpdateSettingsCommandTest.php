<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\App;
use webfiori\framework\config\Controller;
use webfiori\framework\config\JsonDriver;
use webfiori\framework\router\Router;

/**
 * Description of TestUpdateSettingsCommand
 *
 * @author Ibrahim
 */
class UpdateSettingsCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        JsonDriver::setConfigFileName('app-config.json');
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'q'
        ]);
        $runner->setInputs([]);


        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
        ], $runner->getOutput());
    }

    /**
     * @test
     */
    public function testUpdateAppName00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'EN',
            'Super App',
        ]);
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'app-name'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], $runner->getOutput());

        $this->assertEquals('Super App', Controller::getDriver()->getAppName('EN'));
    }
    /**
     * @test
     */
    public function testUpdateAppName01() {
        $runner = App::getRunner();
        $runner->setInputs([
            '',
            'XC',
            '0',
            'Super App',
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'app-name'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Error: Invalid answer.\n",
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Error: Invalid answer.\n",
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testUpdateAppName02() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0',
            '',
            '          ',
            '  Super App X '
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'app-name'
        ]);


        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Enter new name:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter new name:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], $runner->getOutput());
        $this->assertEquals('Super App X', Controller::getDriver()->getAppName('AR'));
    }
    /**
     * @test
     */
    public function testUpdateHomePage00() {
        $runner = App::getRunner();
        $runner->setInputs();

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'home-page'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Info: Router has no routes. Nothing to change.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testUpdateHomePage01() {
        Router::page([
            'path' => 'x/y/z',
            'route-to' => 'test.txt'
        ]);
        $runner = App::getRunner();
        $runner->setInputs([
            '0'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'home-page'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select home page route:\n",
            "0: https://127.0.0.1/x/y/z\n",
            "Success: Home page successfully updated.\n",
        ], $runner->getOutput());
        $this->assertEquals('x/y/z', Controller::getDriver()->getHomePage());
    }
    /**
     * @test
     */
    public function testUpdatePageDescription00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'EN',
            'NEW PAGE DESCRIPTION'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'page-description'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Enter new description:\n",
            "Success: Description successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('NEW PAGE DESCRIPTION', App::getConfig()->getDescription('EN'));
    }
    /**
     * @test
     */
    public function testUpdatePageTitle00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'EN',
            'NEW PAGE'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'page-title'
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Enter new title:\n",
            "Success: Title successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('NEW PAGE', App::getConfig()->getTitle('EN'));
    }
    public function testUpdatePrimaryLang() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'primary-lang'
        ]);
        $runner->setInputs(['0']);
        $runner->start();
        //$this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select new primary language:\n",
            "0: AR\n",
            "1: EN\n",
            "Success: Primary language successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('AR', Controller::getDriver()->getPrimaryLanguage());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryLang00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'EN',
            'NEW PAGE DESCRIPTION'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'page-description'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "In which language you would like to update?\n",
            "0: AR\n",
            "1: EN\n",
            "Enter new description:\n",
            "Success: Description successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('NEW PAGE DESCRIPTION', App::getConfig()->getDescription('EN'));
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'themes\\fioriTheme2\\NewTestTheme2'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'theme'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Success: Theme successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', App::getConfig()->getTheme());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme01() {
        $runner = App::getRunner();
        $runner->setInputs([
            'themes\\greeny\\NotATheme',
            '',
            'themes\\fioriTheme2\\NewTestTheme2'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'theme'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Theme successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', App::getConfig()->getTheme());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme02() {
        $runner = App::getRunner();
        $runner->setInputs([
            'webfiori\\framework\\Privilege',
            'themes\\fioriTheme2\\NewTestTheme2'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'theme'
        ]);
        $runner->start();
        //$this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Theme successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', App::getConfig()->getTheme());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme03() {
        $runner = App::getRunner();
        $runner->setInputs([
            'webfiori\\framework\\App',
            'themes\\fioriTheme2\\NewTestTheme2'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'theme'
        ]);
        $runner->start();
        //$this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Theme successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', App::getConfig()->getTheme());
    }
    /**
     * @test
     */
    public function testUpdateSchedulerPass00() {
        $runner = App::getRunner();
        $runner->setInputs([
            '123456'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'scheduler-pass',
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter new password:\n",
            "Success: Password successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals(hash('sha256', '123456'), App::getConfig()->getSchedulerPassword());
    }
    /**
     * @test
     */
    public function testUpdateSchedulerPass01() {
        $runner = App::getRunner();
        $runner->setInputs([
            '',
            '123'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'scheduler-pass',
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter new password:\n",
            "Error: Empty string is not allowed.\n",
            "Enter new password:\n",
            "Success: Password successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals(hash('sha256', '123'), App::getConfig()->getSchedulerPassword());
    }
    /**
     * @test
     */
    public function testUpdateTitleSep00() {
        $runner = App::getRunner();
        $runner->setInputs([
            '6',
            '+-+'
        ]);

        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'xyz'
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Warning: The argument --w has invalid value.\n",
            "What would you like to update?\n",
            "0: Update application version info.\n",
            "1: Update application name.\n",
            "2: Update scheduler password.\n",
            "3: Update default page title.\n",
            "4: Update default page description.\n",
            "5: Change primary language.\n",
            "6: Change title separator.\n",
            "7: Set home page.\n",
            "8: Set primay theme.\n",
            "9: Quit. <--\n",
            "Enter new title separator string: Enter = '|'\n",
            "Success: Title separator successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('+-+', Controller::getDriver()->getTitleSeparator());
    }
    /**
     * @test
     */
    public function testUpdateVersion00() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'version'
        ]);
        $runner->setInputs([
            '2.0.1',
            'Beta',
            '99',
            '99-99-99',
            '   ',
        ]);

        $this->assertEquals(0, $runner->start());
        $this->assertEquals([

            "Application version: Enter = '1.0'\n",
            "Application version type: Enter = 'Stable'\n",
            "Release date (YYYY-MM-DD): Enter = '".date('Y-m-d')."'\n",
            "Error: Invalid input is given. Try again.\n",
            "Release date (YYYY-MM-DD): Enter = '".date('Y-m-d')."'\n",
            "Error: Invalid input is given. Try again.\n",
            "Release date (YYYY-MM-DD): Enter = '".date('Y-m-d')."'\n",
            "Version information successfully updated.\n"
        ], $runner->getOutput());

        $this->assertEquals('2.0.1', App::getConfig()->getAppVersion());
        $this->assertEquals('Beta', App::getConfig()->getAppVersionType());
        $this->assertEquals(date('Y-m-d'), App::getConfig()->getAppReleaseDate());
    }
}
