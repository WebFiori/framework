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
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Command;
use WebFiori\Framework\App;
/**
 * A CLI command which is used to show framework configuration.
 *
 * @author Ibrahim
 */
class SettingsCommand extends Command {
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
        $C = App::getConfig();
        $format = [];
        $format['color'] = 'yellow';
        $format['bold'] = true;
        $this->println("Framework Version Settings:", $format);
        $this->println("    Framework Version %".($spaces - strlen('Framework Version'))."s %s",':',WF_VERSION);
        $this->println("    Version Type %".($spaces - strlen('Version Type'))."s %s",':',WF_VERSION_TYPE);
        $this->println("    Release Date %".($spaces - strlen('Release Date'))."s %s",':',WF_RELEASE_DATE);

        $this->println("AppConfig.php Settings:", $format);
        $this->println("    Application Path %".($spaces - strlen('Application Path'))."s %s",':',APP_PATH);
        $this->println("    Application Version %".($spaces - strlen('Application Version'))."s %s",':',$C->getAppVersion());
        $this->println("    Version Type %".($spaces - strlen('Version Type'))."s %s",':',$C->getAppVersionType());
        $this->println("    Application Release Date %".($spaces - strlen('Application Release Date'))."s %s",':',$C->getAppReleaseDate());
        $this->println("    Base CLI URL %".($spaces - strlen('Base CLI URL'))."s %s",':',$C->getBaseURL());
        $this->println("    Base Theme %".($spaces - strlen('Base Theme'))."s %s",':',$C->getTheme());
        $this->println("    Title Separator %".($spaces - strlen('Title Separator'))."s %s",':',$C->getTitleSeparator());
        $this->println("    Home Page %".($spaces - strlen('Home Page'))."s %s",':',$C->getHomePage());
        $this->println("    Website Names:",':');
        $names = $C->getAppNames();

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
