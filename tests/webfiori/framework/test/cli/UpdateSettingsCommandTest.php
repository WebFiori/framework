<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\cli\Runner;
use webfiori\framework\cli\commands\UpdateSettingsCommand;
use webfiori\framework\ConfigController;
use webfiori\framework\App;

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
    public function testUpdatePrimaryLang() {
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'primary-lang'
        ]);
        $runner->setInputs(['1']);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Select new primary language:\n",
            "0: EN\n",
            "1: AR\n",
            "Success: Primary language successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('AR', ConfigController::get()->getPrimaryLang());
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
            
            "Application version: Enter = '".App::getAppConfig()->getVersion()."'\n",
            "Application version type: Enter = '".App::getAppConfig()->getVersionType()."'\n",
            "Release date (YYYY-MM-DD): Enter = '".date('Y-m-d')."'\n",
            "Error: Invalid input is given. Try again.\n",
            "Release date (YYYY-MM-DD): Enter = '".date('Y-m-d')."'\n",
            "Error: Invalid input is given. Try again.\n",
            "Release date (YYYY-MM-DD): Enter = '".date('Y-m-d')."'\n",
            "Version information successfully updated.\n"
        ], $runner->getOutput());

        $version = ConfigController::get()->getAppVersionInfo();
        $this->assertEquals('2.0.1', $version['version']);
        $this->assertEquals('Beta', $version['version-type']);
        $this->assertEquals(date('Y-m-d'), $version['release-date']);
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
            "0: EN\n",
            "1: AR\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], $runner->getOutput());
        
        $this->assertEquals('Super App', ConfigController::get()->getWebsiteNames()['EN']);
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
            "0: EN\n",
            "1: AR\n",
            "Error: Invalid answer.\n",
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
            "Error: Invalid answer.\n",
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
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
            "0: EN\n",
            "1: AR\n",
            "Enter new name:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter new name:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], $runner->getOutput());
        $this->assertEquals('Super App X', ConfigController::get()->getWebsiteNames()['EN']);
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
        $this->assertEquals(hash('sha256', '123456'), ConfigController::get()->getSchedulerPassword());
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
        $this->assertEquals(hash('sha256', '123'), ConfigController::get()->getSchedulerPassword());
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
            "0: EN\n",
            "1: AR\n",
            "Enter new title:\n",
            "Success: Title successfully updated.\n"
        ], $runner->getOutput());
        
        $this->assertEquals('NEW PAGE', ConfigController::get()->getTitles()['EN']);
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
            "0: EN\n",
            "1: AR\n",
            "Enter new description:\n",
            "Success: Description successfully updated.\n"
        ], $runner->getOutput());
        
        $this->assertEquals('NEW PAGE DESCRIPTION', ConfigController::get()->getDescriptions()['EN']);
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
            "0: EN\n",
            "1: AR\n",
            "Enter new description:\n",
            "Success: Description successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('NEW PAGE DESCRIPTION', ConfigController::get()->getDescriptions()['EN']);
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
            "9: Set admin theme.\n",
            "10: Quit. <--\n",
            "Enter new title separator string: Enter = '|'\n",
            "Success: Title separator successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('+-+', ConfigController::get()->getTitleSep());
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
        \webfiori\framework\router\Router::page([
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
            "0: https://example.com/x/y/z\n",
            "Success: Home page successfully updated.\n",
        ], $runner->getOutput());
        $this->assertEquals('x/y/z', ConfigController::get()->getHomePage());
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
            '--w' => 'primary-theme'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Success: Primary theme successfully updated.\n"
        ], $runner->getOutput());
        
        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', ConfigController::get()->getBaseTheme());
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
            '--w' => 'primary-theme'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Primary theme successfully updated.\n"
        ], $runner->getOutput());
        
        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', ConfigController::get()->getBaseTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme00() {
        $runner = App::getRunner();
        $runner->setInputs([
            'themes\\fioriTheme\\NewFTestTheme'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'admin-theme'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('themes\\fioriTheme\\NewFTestTheme', ConfigController::get()->getAdminTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme01() {
        $runner = App::getRunner();
        $runner->setInputs([
            'themes\\greeny\\NotATheme',
            '',
            'themes\\fioriTheme2\\NewTestTheme2'
        ]);
        
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'admin-theme'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', ConfigController::get()->getAdminTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme02() {
        $runner = App::getRunner();
        $runner->setInputs([
            'app\\entity\\WithException',
            '',
            'themes\\fioriTheme2\\NewTestTheme2'
        ]);
        
        $runner->setArgsVector([
            'webfiori',
            'update-settings',
            '--w' => 'admin-theme'
        ]);
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('themes\\fioriTheme2\\NewTestTheme2', ConfigController::get()->getAdminTheme());
    }
}
