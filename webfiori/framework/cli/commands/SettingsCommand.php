<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\commands;

use webfiori\cli\CLICommand;
use webfiori\framework\WebFioriApp;
/**
 * A CLI command which is used to show framework configuration.
 *
 * @author Ibrahim
 */
class SettingsCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--show-config'. This command is used to display 
     * the configuration of the framework.
     */
    public function __construct() {
        parent::__construct('show-settings', [], 'Display application configuration.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $spaces = 25;
        $C = WebFioriApp::getAppConfig();
        $this->println("Framework Version Settings:", [
            'color' => 'yellow',
            'bold' => true
        ]);
        $this->println("    Framework Version %".($spaces - strlen('Framework Version'))."s %s",':',WF_VERSION);
        $this->println("    Version Type %".($spaces - strlen('Version Type'))."s %s",':',WF_VERSION_TYPE);
        $this->println("    Release Date %".($spaces - strlen('Release Date'))."s %s",':',WF_RELEASE_DATE);

        $this->println("AppConfig.php Settings:", [
            'color' => 'yellow',
            'bold' => true
        ]);
        $this->println("    Application Path %".($spaces - strlen('Application Path'))."s %s",':',ROOT_DIR.DS.APP_DIR_NAME);
        $this->println("    Application Version %".($spaces - strlen('Application Version'))."s %s",':',$C->getVersion());
        $this->println("    Version Type %".($spaces - strlen('Version Type'))."s %s",':',$C->getVersionType());
        $this->println("    Application Release Date %".($spaces - strlen('Application Release Date'))."s %s",':',$C->getReleaseDate());
        $this->println("    Base CLI URL %".($spaces - strlen('Base CLI URL'))."s %s",':',$C->getBaseURL());
        $this->println("    Admin Theme %".($spaces - strlen('Admin Theme'))."s %s",':',$C->getAdminThemeName());
        $this->println("    Base Theme %".($spaces - strlen('Base Theme'))."s %s",':',$C->getBaseThemeName());
        $this->println("    Title Separator %".($spaces - strlen('Title Separator'))."s %s",':',$C->getTitleSep());
        $this->println("    Home Page %".($spaces - strlen('Home Page'))."s %s",':',$C->getHomePage());
        $this->println("    Config Version %".($spaces - strlen('Config Version'))."s %s",':',$C->getConfigVersion());
        $this->println("    Website Names:",':');
        $names = $C->getWebsiteNames();

        foreach ($names as $langCode => $name) {
            $this->println("        $langCode => $name");
        }
        $this->println("    Website Descriptions:",':');

        foreach ($C->getDescriptions() as $langCode => $desc) {
            $this->println("        $langCode => $desc");
        }

        $this->println("    Pages Titles:",':');

        foreach ($C->getTitles() as $langCode => $title) {
            $this->println("        $langCode => $title");
        }

        return 0;
    }
}
