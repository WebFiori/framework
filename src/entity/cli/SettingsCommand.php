<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\entity\cli;

use webfiori\WebFiori;
use webfiori\entity\cli\CLICommand;
/**
 * A CLI command which is used to show framework configuration.
 *
 * @author Ibrahim
 */
class SettingsCommand extends CLICommand{
    public function __construct() {
        parent::__construct('--show-config', [], 'Display framework configuration.');
    }
    public function exec() {
        $spaces = 25;
        $C = WebFiori::getConfig();
        fprintf(STDOUT, "Config.php Settings:\n");
        fprintf(STDOUT, "    Framework Version %".($spaces - strlen('Framework Version'))."s %s\n",':',$C->getVersion());
        fprintf(STDOUT, "    Version Type %".($spaces - strlen('Version Type'))."s %s\n",':',$C->getVersionType());
        fprintf(STDOUT, "    Release Date %".($spaces - strlen('Release Date'))."s %s\n",':',$C->getReleaseDate());
        fprintf(STDOUT, "    Config Version %".($spaces - strlen('Config Version'))."s %s\n",':',$C->getConfigVersion());
        $isConfigured = $C->isConfig() === true ? 'Yes' : 'No';
        fprintf(STDOUT, "    Is System Configured %".($spaces - strlen('Is System Configured'))."s %s\n",':',$isConfigured);
        fprintf(STDOUT, "SiteConfig.php Settings:\n");
        $SC = WebFiori::getSiteConfig();
        fprintf(STDOUT, "    Base URL %".($spaces - strlen('Base URL'))."s %s\n",':',$SC->getBaseURL());
        fprintf(STDOUT, "    Admin Theme %".($spaces - strlen('Admin Theme'))."s %s\n",':',$SC->getAdminThemeName());
        fprintf(STDOUT, "    Base Theme %".($spaces - strlen('Base Theme'))."s %s\n",':',$SC->getBaseThemeName());
        fprintf(STDOUT, "    Title Separator %".($spaces - strlen('Title Separator'))."s %s\n",':',$SC->getTitleSep());
        fprintf(STDOUT, "    Home Page %".($spaces - strlen('Home Page'))."s %s\n",':',$SC->getHomePage());
        fprintf(STDOUT, "    Config Version %".($spaces - strlen('Config Version'))."s %s\n",':',$SC->getConfigVersion());
        fprintf(STDOUT, "    Website Names:\n",':');
        $names = $SC->getWebsiteNames();

        foreach ($names as $langCode => $name) {
            fprintf(STDOUT,"        $langCode => $name\n");
        }
        fprintf(STDOUT, "    Website Descriptions:\n",':');

        foreach ($SC->getDescriptions() as $langCode => $desc) {
            fprintf(STDOUT,"        $langCode => $desc\n");
        }
    }

}
