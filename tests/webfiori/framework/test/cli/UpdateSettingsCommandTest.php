<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\commands\UpdateSettingsCommand;
use webfiori\framework\ConfigController;
use webfiori\cli\Runner;

/**
 * Description of TestUpdateSettingsCommand
 *
 * @author Ibrahim
 */
class UpdateSettingsCommandTest extends TestCase {
    /**
     * @test
     */
    public function testUpdateVersion00() {
        $runner = new Runner();
        $runner->setInput([
            '0',
            '2.0.1',
            'Beta',
            ''
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand()));
        $this->assertEquals([
            "What would you like to update?\n",
            "0: Update application version info.\n",
            "1: Update application name.\n",
            "2: Update CRON password.\n",
            "3: Update default page title.\n",
            "4: Update default page description.\n",
            "5: Change primary language.\n",
            "6: Change title separator.\n",
            "7: Set home page.\n",
            "8: Set primay theme.\n",
            "9: Set admin theme.\n",
            "10: Quit. <--\n",
            "Application version: Enter = '1.0'\n",
            "Application version type: Enter = 'Stable'\n",
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
        $runner = new Runner();
        $runner->setInput([
            'EN',
            'Super App',
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'app-name'
        ]));
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
        $runner = new Runner();
        $runner->setInput([
            '',
            'XC',
            '0',
            'Super App',
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'app-name'
        ]));
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
        $runner = new Runner();
        $runner->setInput([
            '0',
            '',
            '          ',
            '  Super App X '
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'app-name'
        ]));
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
    public function testUpdateCronPass00() {
        $runner = new Runner();
        $runner->setInput([
            '123456'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'cron-pass'
        ]));
        $this->assertEquals([
            "Enter new password: Enter = ''\n",
            "Success: Password successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals(hash('sha256', '123456'), ConfigController::get()->getCRONPassword());
    }
    /**
     * @test
     */
    public function testUpdateCronPass01() {
        $runner = new Runner();
        $runner->setInput([
            ''
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'cron-pass'
        ]));
        $this->assertEquals([
            "Enter new password: Enter = ''\n",
            "Success: Password successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('NO_PASSWORD', ConfigController::get()->getCRONPassword());
    }
    /**
     * @test
     */
    public function testUpdatePageTitle00() {
        $runner = new Runner();
        $runner->setInput([
            'EN',
            'NEW PAGE'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'page-title'
        ]));
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
        $runner = new Runner();
        $runner->setInput([
            'EN',
            'NEW PAGE DESCRIPTION'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'page-description'
        ]));
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
        $runner = new Runner();
        $runner->setInput([
            'EN',
            'NEW PAGE DESCRIPTION'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'page-description'
        ]));
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
        $runner = new Runner();
        $runner->setInput([
            '6',
            '+-+'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand()));
        $this->assertEquals([
            "What would you like to update?\n",
            "0: Update application version info.\n",
            "1: Update application name.\n",
            "2: Update CRON password.\n",
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
        $runner = new Runner();
        $runner->setInput([
            
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'home-page'
        ]));
        $this->assertEquals([
            "Info: Router has no routes. Nothing to change.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme00() {
        $runner = new Runner();
        $runner->setInput([
            'themes\\greeny\\GreenyTheme'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'primary-theme'
        ]));
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Success: Primary theme successfully updated.\n"
        ], $runner->getOutput());
        
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getBaseTheme());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme01() {
        $runner = new Runner();
        $runner->setInput([
            'themes\\greeny\\NotATheme',
            '',
            'themes\\greeny\\GreenyTheme'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'primary-theme'
        ]));
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Primary theme successfully updated.\n"
        ], $runner->getOutput());
        
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getBaseTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme00() {
        $runner = new Runner();
        $runner->setInput([
            'themes\\greeny\\GreenyTheme'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'admin-theme'
        ]));
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getAdminTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme01() {
        $runner = new Runner();
        $runner->setInput([
            'themes\\greeny\\NotATheme',
            '',
            'themes\\greeny\\GreenyTheme'
        ]);
        
        
        $this->assertEquals(0, $runner->runCommand(new UpdateSettingsCommand(), [
            '--w' => 'admin-theme'
        ]));
        $this->assertEquals([
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], $runner->getOutput());
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getAdminTheme());
    }
}
