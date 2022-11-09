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

use Throwable;
use webfiori\cli\CLICommand;
use webfiori\cli\InputValidator;
use webfiori\framework\ConfigController;
use webfiori\framework\router\Router;
use webfiori\framework\Theme;
use webfiori\framework\WebFioriApp;
/**
 * This class implements a CLI command which is used to update the settings which are 
 * stored in the class 'AppConfing' of the application.
 *
 * @author Ibrahim
 * 
 * @since 2.3.1
 */
class UpdateSettingsCommand extends CLICommand {
    public function __construct() {
        parent::__construct('update-settings', [
            '--w' => [
                'description' => 'An argument which is used to indicate what will be updated. '
                . 'Possible values are: version, app-name, cron-pass, page-title, '
                . 'page-description, primary-lang, title-sep, home-page, primary-theme,'
                . 'admin-theme.',
                'optional' => true
            ]
        ], 'Update application settings which are stored in the class "AppConfig".');
    }
    private function addOption(&$optArr, $key, $txt) {
        $optArr[$key] = $txt;
    }
    public function exec() : int {
        $options = [];
        $this->addOption($options,'version', 'Update application version info.');
        $this->addOption($options,'app-name', 'Update application name.');
        $this->addOption($options,'cron-pass', 'Update CRON password.');
        $this->addOption($options,'page-title', 'Update default page title.');
        $this->addOption($options,'page-description', 'Update default page description.');
        $this->addOption($options,'primary-lang', 'Change primary language.');
        $this->addOption($options,'title-sep', 'Change title separator.');
        $this->addOption($options,'home-page', 'Set home page.');
        $this->addOption($options,'primary-theme', 'Set primay theme.');
        $this->addOption($options,'admin-theme', 'Set admin theme.');
        $this->addOption($options,'q', 'Quit.');
        
        $what = $this->getArgValue('--w');
        $answer = null;
        if ($what !== null) {
            $answer = isset($options[$what]) ? $options[$what] : null;
            
            if ($answer === null) {
                $this->warning('The argument --w has invalid value.');
            }
        }
        
        
        
        if ($answer === null) {
            $answer = $this->select('What would you like to update?', $options, count($options) - 1);
        }

        if ($answer == 'Quit.') {
            return 0;
        } else if ($answer == 'Update application name.') {
            $this->_updateName();
        } else if ($answer == 'Update default page title.') {
            $this->_updateTitle();
        } else if ($answer == 'Update CRON password.') {
            $this->_updateCronPass();
        } else if ($answer == 'Change title separator.') {
            $this->_updateTitleSep();
        } else if ($answer == 'Update default page description.') {
            $this->_updateDescription();
        } else if ($answer == 'Change primary language.') {
            $this->_updatePrimaryLang();
        } else if ($answer == 'Set primay theme.') {
            $this->_setPrimaryTheme();
        } else if ($answer == 'Set admin theme.') {
            $this->_setAdminTheme();
        } else if ($answer == 'Set home page.') {
            $this->_setHome();
        } else if ($answer == 'Update application version info.') {
            $this->_updateVersionInfo();
        }

        return 0;
    }
    private function _setAdminTheme() {
        $classNs = $this->getThemeNs();
        ConfigController::get()->updateSiteInfo(['admin-theme' => $classNs]);
        $this->success('Admin theme successfully updated.');
    }
    private function _setHome() {
        $routes = array_keys(Router::routes());
        if (count($routes) == 0) {
            $this->info('Router has no routes. Nothing to change.');
            return;
        }
        $home = $this->select('Select home page route:', $routes);
        ConfigController::get()->updateSiteInfo(['home-page' => substr($home, strlen(Router::base()) + 1)]);
        $this->success('Home page successfully updated.');
    }
    private function _setPrimaryTheme() {
        $classNs = $this->getThemeNs();
        ConfigController::get()->updateSiteInfo(['base-theme' => $classNs]);
        $this->success('Primary theme successfully updated.');
    }
    private function _updateCronPass() {
        $newPass = $this->getInput('Enter new password:', '');
        if (strlen($newPass) == 0) {
            $newPass = 'NO_PASSWORD';
        } else {
            $newPass = hash('sha256', $newPass);
        }
        ConfigController::get()->updateCronPassword($newPass);
        $this->success('Password successfully updated.');
    }
    private function _updateDescription() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new description:', null, new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        $descriptions = WebFioriApp::getAppConfig()->getDescriptions();
        $descriptions[$lang] = $newName;
        ConfigController::get()->updateSiteInfo(['descriptions' => $descriptions]);
        $this->success('Description successfully updated.');
    }
    private function _updateName() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new name:', null, new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        $names = WebFioriApp::getAppConfig()->getWebsiteNames();
        $names[$lang] = trim($newName);
        ConfigController::get()->updateSiteInfo(['website-names' => $names]);
        $this->println('Name successfully updated.');
    }
    private function _updatePrimaryLang() {
        $langs = array_keys(WebFioriApp::getAppConfig()->getWebsiteNames());
        $newPrimary = $this->select('Select new primary language:', $langs);
        ConfigController::get()->updateSiteInfo(['primary-lang' => $newPrimary]);
        $this->success('Primary language successfully updated.');
    }
    private function _updateTitle() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new title:', null, new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        $titles = WebFioriApp::getAppConfig()->getTitles();
        $titles[$lang] = trim($newName);
        ConfigController::get()->updateSiteInfo(['titles' => $titles]);
        $this->success('Title successfully updated.');
    }
    private function _updateTitleSep() {
        $newSep = $this->getInput('Enter new title separator string:', '|', new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        ConfigController::get()->updateSiteInfo(['title-sep' => $newSep]);
        $this->success('Title separator successfully updated.');
    }
    private function _updateVersionInfo() {
        $versionNum = $this->getInput('Application version:', WebFioriApp::getAppConfig()->getVersion(), new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        $versionType = $this->getInput('Application version type:', WebFioriApp::getAppConfig()->getVersionType(), new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        $versionReleaseDate = $this->getInput('Release date (YYYY-MM-DD):', date('Y-m-d'), new InputValidator(function ($val)
        {
            $expl = explode('-', $val);

            if (count($expl) != 3) {
                return false;
            }

            return intval($expl[0]) > 0
                && intval($expl[0]) < 10000
                && intval($expl[1]) > 0
                && intval($expl[1]) < 13
                && intval($expl[2]) > 0
                && intval($expl[2]) < 32;
        }));
        ConfigController::get()->updateAppVersionInfo($versionNum, $versionType, date('Y-m-d', strtotime($versionReleaseDate)));
        $this->println('Version information successfully updated.');
    }
    private function getThemeNs() {
        return $this->getInput('Enter theme class name with namespace:', null, new InputValidator(function ($themeNs)
        {
            if (!class_exists($themeNs)) {
                return false;
            }
            try {
                $instance = new $themeNs();

                if ($instance instanceof Theme) {
                    return true;
                }
            } catch (Throwable $exc) {
                return false;
            }
        }));
    }
    private function whichLang() {
        $langs = array_keys(WebFioriApp::getAppConfig()->getWebsiteNames());

        return $this->select('In which language you would like to update?', $langs);
    }
}
