<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\UpdateSettingsCommand;
use webfiori\framework\WebFioriApp;
use webfiori\framework\ConfigController;
use webfiori\framework\cli\Runner;
use webfiori\framework\cli\ArrayInputStream;
use webfiori\framework\cli\ArrayOutputStream;

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
        Runner::setInputStream(new ArrayInputStream([
            '0',
            '2.0.1',
            'Beta',
            ''
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Application version: Enter = \"1.0\"\n",
            "Application version type: Enter = \"Stable\"\n",
            "Release date (YYYY-MM-DD): Enter = \"".date('Y-m-d')."\"\n",
            "Version information successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());

        $version = ConfigController::get()->getAppVersionInfo();
        $this->assertEquals('2.0.1', $version['version']);
        $this->assertEquals('Beta', $version['version-type']);
        $this->assertEquals(date('Y-m-d'), $version['release-date']);
    }
    /**
     * @test
     */
    public function testUpdateAppName00() {
        Runner::setInputStream(new ArrayInputStream([
            '1',
            'EN',
            'Super App',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], Runner::getOutputStream()->getOutputArray());
        
        $this->assertEquals('Super App', ConfigController::get()->getWebsiteNames()['EN']);
    }
    /**
     * @test
     */
    public function testUpdateAppName01() {
        Runner::setInputStream(new ArrayInputStream([
            '1',
            '',
            'XC',
            '0',
            'Super App',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
        ], Runner::getOutputStream()->getOutputArray());
    }
    /**
     * @test
     */
    public function testUpdateAppName02() {
        Runner::setInputStream(new ArrayInputStream([
            '1',
            '0',
            '',
            '          ',
            '  Super App X '
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
            "Enter new name:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter new name:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter new name:\n",
            "Name successfully updated.\n",
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals('Super App X', ConfigController::get()->getWebsiteNames()['EN']);
    }
    
    /**
     * @test
     */
    public function testUpdateCronPass00() {
        Runner::setInputStream(new ArrayInputStream([
            '2',
            '123456'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter new password: Enter = \"\"\n",
            "Success: Password successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals(hash('sha256', '123456'), ConfigController::get()->getCRONPassword());
    }
    /**
     * @test
     */
    public function testUpdateCronPass01() {
        Runner::setInputStream(new ArrayInputStream([
            '2',
            ''
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter new password: Enter = \"\"\n",
            "Success: Password successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals('NO_PASSWORD', ConfigController::get()->getCRONPassword());
    }
    /**
     * @test
     */
    public function testUpdatePageTitle00() {
        Runner::setInputStream(new ArrayInputStream([
            '3',
            'EN',
            'NEW PAGE'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
            "Enter new title:\n",
            "Success: Title successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        
        $this->assertEquals('NEW PAGE', ConfigController::get()->getTitles()['EN']);
    }
    /**
     * @test
     */
    public function testUpdatePageDescription00() {
        Runner::setInputStream(new ArrayInputStream([
            '4',
            'EN',
            'NEW PAGE DESCRIPTION'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
            "Enter new description:\n",
            "Success: Description successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        
        $this->assertEquals('NEW PAGE DESCRIPTION', ConfigController::get()->getDescriptions()['EN']);
    }
    /**
     * @test
     */
    public function testUpdatePrimaryLang00() {
        Runner::setInputStream(new ArrayInputStream([
            '4',
            'EN',
            'NEW PAGE DESCRIPTION'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "In which language you would like to update?\n",
            "0: EN\n",
            "1: AR\n",
            "Enter new description:\n",
            "Success: Description successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals('NEW PAGE DESCRIPTION', ConfigController::get()->getDescriptions()['EN']);
    }
    /**
     * @test
     */
    public function testUpdateTitleSep00() {
        Runner::setInputStream(new ArrayInputStream([
            '6',
            '+-+'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter new title separator string: Enter = \"|\"\n",
            "Success: Title separator successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals('+-+', ConfigController::get()->getTitleSep());
    }
    /**
     * @test
     */
    public function testUpdateHomePage00() {
        Runner::setInputStream(new ArrayInputStream([
            '7',
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Info: Router has no routes. Nothing to change.\n",
        ], Runner::getOutputStream()->getOutputArray());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme00() {
        Runner::setInputStream(new ArrayInputStream([
            '8',
            'themes\\greeny\\GreenyTheme'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter theme class name with namespace:\n",
            "Success: Primary theme successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getBaseTheme());
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme01() {
        Runner::setInputStream(new ArrayInputStream([
            '8',
            'themes\\greeny\\NotATheme',
            '',
            'themes\\greeny\\GreenyTheme'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Primary theme successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getBaseTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme00() {
        Runner::setInputStream(new ArrayInputStream([
            '9',
            'themes\\greeny\\GreenyTheme'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getAdminTheme());
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme01() {
        Runner::setInputStream(new ArrayInputStream([
            '9',
            'themes\\greeny\\NotATheme',
            '',
            'themes\\greeny\\GreenyTheme'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        
        $this->assertEquals(0, Runner::runCommand(new UpdateSettingsCommand()));
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
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Error: Invalid input is given. Try again.\n",
            "Enter theme class name with namespace:\n",
            "Success: Admin theme successfully updated.\n"
        ], Runner::getOutputStream()->getOutputArray());
        $this->assertEquals('themes\\greeny\\GreenyTheme', ConfigController::get()->getAdminTheme());
    }
}
