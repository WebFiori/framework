<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\App;

class SettingsCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        App::getConfig()->initialize(true);
        $runner = App::getRunner();
        $runner->setInputs();
        $runner->setArgsVector([
            'webfiori',
            'show-settings'
        ]);
        $runner->start();
        $config = App::getConfig();
        $this->assertEquals([
            "Framework Version Settings:\n",
            "    Framework Version        : ".WF_VERSION."\n",
            "    Version Type             : ".WF_VERSION_TYPE."\n",
            "    Release Date             : ".WF_RELEASE_DATE."\n",
            "AppConfig.php Settings:\n",
            "    Application Path         : ".APP_PATH."\n",
            "    Application Version      : ".$config->getAppVersion()."\n",
            "    Version Type             : ".$config->getAppVersionType()."\n",
            "    Application Release Date : ".$config->getAppReleaseDate()."\n",
            "    Base CLI URL             : ".$config->getBaseURL()."\n",
            "    Base Theme               : ".$config->getTheme()."\n",
            "    Title Separator          : ".$config->getTitleSeparator()."\n",
            "    Home Page                : ".$config->getHomePage()."\n",
            "    Website Names:\n",
            "        AR => تطبيق\n",
            "        EN => Application\n",
            "    Website Descriptions:\n",
            "        AR => \n",
            "        EN => \n",
            "    Pages Titles:\n",
            "        AR => افتراضي\n",
            "        EN => Default\n",

        ], $runner->getOutput());
    }
}
