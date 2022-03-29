<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\UpdateSettingsCommand;
use webfiori\framework\WebFioriApp;
use webfiori\framework\ConfigController;

/**
 * Description of TestUpdateSettingsCommand
 *
 * @author Ibrahim
 */
class TestUpdateSettingsCommand extends TestCase {
    /**
     * @test
     */
    public function testUpdateVersion00() {
        $runner = new CommandRunner([
            '0',
            '2.0.1',
            'Beta',
            ''
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $version = ConfigController::get()->getAppVersionInfo();
        $this->assertEquals('2.0.1', $version['version']);
        $this->assertEquals('Beta', $version['version-type']);
        $this->assertEquals(date('Y-m-d'), $version['release-date']);
    }
    /**
     * @test
     */
    public function testUpdateAppName00() {
        $runner = new CommandRunner([
            '1',
            'EN',
            'Super App',
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals('Super App', ConfigController::get()->getWebsiteNames()['EN']);
    }
    /**
     * @test
     */
    public function testUpdateAppName01() {
        $runner = new CommandRunner([
            '1',
            '',
            'XC',
            '0',
            'Super App',
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
    }
    /**
     * @test
     */
    public function testUpdateAppName02() {
        $runner = new CommandRunner([
            '1',
            '0',
            '',
            '          ',
            '  Super App X '
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals('Super App X', ConfigController::get()->getWebsiteNames()['EN']);
    }
    
    /**
     * @test
     */
    public function testUpdateCronPass00() {
        $runner = new CommandRunner([
            '2',
            '123456'
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals(hash('sha256', '123456'), ConfigController::get()->getCRONPassword());
    }
    /**
     * @test
     */
    public function testUpdateCronPass01() {
        $runner = new CommandRunner([
            '2',
            ''
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals('NO_PASSWORD', ConfigController::get()->getCRONPassword());
    }
    /**
     * @test
     */
    public function testUpdatePageTitle00() {
        $runner = new CommandRunner([
            '3',
            'EN',
            'NEW PAGE'
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals('NEW PAGE', ConfigController::get()->getTitles()['EN']);
    }
    /**
     * @test
     */
    public function testUpdatePageDescription00() {
        $runner = new CommandRunner([
            '4',
            'EN',
            'NEW PAGE DESCRIPTION'
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals('NEW PAGE DESCRIPTION', ConfigController::get()->getDescriptions()['EN']);
    }
    /**
     * @test
     */
    public function testUpdatePrimaryLang00() {
        $runner = new CommandRunner([
            '5',
            '1'
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
            "Select new primary language:\n",
            "0: EN\n",
            "1: AR\n",
            "Success: Primary language successfully updated.\n"
        ], $this));
        $this->assertEquals('AR', ConfigController::get()->getPrimaryLang());
    }
    /**
     * @test
     */
    public function testUpdateTitleSep00() {
        $runner = new CommandRunner([
            '6',
            '+-+'
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
        $this->assertEquals('+-+', ConfigController::get()->getTitleSep());
    }
    /**
     * @test
     */
    public function testUpdateHomePage00() {
        $runner = new CommandRunner([
            '10',
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
    }
    /**
     * @test
     */
    public function testUpdatePrimaryTheme00() {
        $runner = new CommandRunner([
            '10',
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
    }
    /**
     * @test
     */
    public function testUpdateAdminTheme00() {
        $runner = new CommandRunner([
            '10',
        ]);
        $runner->runCommand(new UpdateSettingsCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
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
        ], $this));
    }
}
