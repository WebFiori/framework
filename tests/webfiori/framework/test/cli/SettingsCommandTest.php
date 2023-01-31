<?php
namespace webfiori\framework\test\cli;
use webfiori\cli\Runner;
use webfiori\framework\cli\commands\ListRoutesCommand;
use PHPUnit\Framework\TestCase;
use webfiori\framework\WebFioriApp;
use webfiori\framework\router\Router;

class SettingsCommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setInput();
        $runner->setArgsVector([
            'webfiori',
            'show-settings'
        ]);
        $runner->start();
        $config = WebFioriApp::getAppConfig();
        $this->assertEquals([
            "Framework Version Settings:\n",
            "    Framework Version        : ".WF_VERSION."\n",
            "    Version Type             : ".WF_VERSION_TYPE."\n",
            "    Release Date             : ".WF_RELEASE_DATE."\n",
            "AppConfig.php Settings:\n",
            "    Application Path         : ".ROOT_PATH.DS.APP_DIR."\n",
            "    Application Version      : ".$config->getVersion()."\n",
            "    Version Type             : ".$config->getVersionType()."\n",
            "    Application Release Date : ".$config->getReleaseDate()."\n",
            "    Base CLI URL             : ".$config->getBaseURL()."\n",
            "    Admin Theme              : ".$config->getAdminThemeName()."\n",
            "    Base Theme               : ".$config->getBaseThemeName()."\n",
            "    Title Separator          : ".$config->getTitleSep()."\n",
            "    Home Page                : ".$config->getHomePage()."\n",
            "    Config Version           : ".$config->getConfigVersion()."\n",
            "    Website Names:\n",
            "        EN => WebFiori\n",
            "        AR => ويب فيوري\n",
            "    Website Descriptions:\n",
            "        EN => \n",
            "        AR => \n",
            "    Pages Titles:\n",
            "        EN => Hello World\n",
            "        AR => اهلا و سهلا\n"
        ], $runner->getOutput());
    }
}