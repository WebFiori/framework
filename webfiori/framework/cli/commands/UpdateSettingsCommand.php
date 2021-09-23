<?php
namespace webfiori\framework\cli\commands;

use webfiori\framework\cli\CLICommand;
use webfiori\framework\ConfigController;
use webfiori\framework\WebFioriApp;
use webfiori\framework\router\Router;
use webfiori\framework\Theme;
use Exception;
use Error;
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
        parent::__construct('update-settings', [], 'Update application settings which are stored in the class "AppConfig".');
    }
    public function exec() {
        $options = [
            'Update application version info.',
            'Update application name.',
            'Update CRON password.',
            'Update default page title.',
            'Update default page description.',
            'Change primary language.',
            'Change title separator.',
            'Set home page.',
            'Set primay theme.',
            'Set admin theme.',
            'Quit.'
        ];
        $whatToUpdate = $this->select('What would you like to update?', $options, count($options) - 1);
        
        if ($whatToUpdate == 'Quit.') {
            return 0;
        } else if ($whatToUpdate == 'Update application name.') {
            $this->_updateName();
        } else if ($whatToUpdate == 'Update default page title.') {
            $this->_updateTitle();
        } else if ($whatToUpdate == 'Update CRON password.') {
            $this->_updateCronPass();
        } else if ($whatToUpdate == 'Change title separator.') {
            $this->_updateTitleSep();
        } else if ($whatToUpdate == 'Update default page description.') {
            $this->_updateDescription();
        } else if ($whatToUpdate == 'Change primary language.') {
            $this->_updatePrimaryLang();
        } else if ($whatToUpdate == 'Set primay theme.') {
            $this->_setPrimaryTheme();
        } else if ($whatToUpdate == 'Set admin theme.') {
            $this->_setAdminTheme();
        } else if ($whatToUpdate == 'Set home page.') {
            $this->_setHome();
        } else if ($whatToUpdate == 'Update application version info.') {
            $this->_updateVersionInfo();
        } else {
            $this->println('Not implemented yet.');
        }
        return 0;
    }
    private function _updateVersionInfo() {
        $versionNum = $this->getInput('Application version:', WebFioriApp::getAppConfig()->getVersion(), function ($val) {
            return strlen(trim($val)) != 0;
        });
        $versionType = $this->getInput('Application version type:', WebFioriApp::getAppConfig()->getVersionType(), function ($val) {
            return strlen(trim($val)) != 0;
        });
        $versionReleaseDate = $this->getInput('Release date (YYYY-MM-DD):', date('Y-m-d'), function ($val) {
            $trimmed = trim($val);
            if (strlen($trimmed) == 0) {
                return false;
            }
            $expl = explode('-', $trimmed);
            if (count($expl) != 3) {
                return false;
            }
            return intval($expl[0]) > 0
                && intval($expl[0]) < 10000
                && intval($expl[1]) > 0
                && intval($expl[1]) < 13
                && intval($expl[2]) > 0
                && intval($expl[2]) < 32;
        });
        ConfigController::get()->updateAppVersionInfo($versionNum, $versionType, date('Y-m-d', strtotime($versionReleaseDate)));
        $this->println('Version information successfully updated.');
    }
    private function _setAdminTheme() {
        $classNs = $this->getThemeNs();
        ConfigController::get()->updateSiteInfo([
            'admin-theme' => $classNs
        ]);
        $this->println('Admin theme successfully updated.');
    }
    private function _setPrimaryTheme() {
        $classNs = $this->getThemeNs();
        ConfigController::get()->updateSiteInfo([
            'base-theme' => $classNs
        ]);
        $this->println('Primary theme successfully updated.');
    }
    private function getThemeNs() {
        return $this->getInput('Enter theme class name with namespace:', null, function ($themeNs) {
            if (!class_exists($themeNs)) {
                return false;
            }
            try {
                $instance = new $themeNs();
                if ($instance instanceof Theme) {
                    return true;
                }
            } catch (Exception $exc) {
                return false;
            } catch (Error $exc) {
                return false;
            }
        });
    }
    private function _setHome() {
        $routes = array_keys(Router::routes());
        $home = $this->select('Select home page route:', $routes);
        ConfigController::get()->updateSiteInfo([
            'home-page' => $home
        ]);
        $this->println('Home page successfully updated.');
    }
    private function _updatePrimaryLang() {
        $langs = array_keys(WebFioriApp::getAppConfig()->getWebsiteNames());
        $newPrimary = $this->select('Select new primary language:', $langs);
        ConfigController::get()->updateSiteInfo([
            'primary-lang' => $newPrimary
        ]);
        $this->println('Primary language successfully updated.');
    }
    private function _updateCronPass() {
        $newPass = $this->getInput('Enter new password:', '');
        ConfigController::get()->updateCronPassword($newPass);
        $this->println('Password successfully updated.');
    }
    private function _updateTitleSep() {
        $newSep = $this->getInput('Enter new title separator string:', '|', function ($val) {
            return strlen(trim($val)) != 0;
        });
        ConfigController::get()->updateSiteInfo([
            'title-sep' => $newSep
        ]);
        $this->println('Title separator successfully updated.');
    }
    private function _updateTitle() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new title:', null, function ($val) {
            $trimmed = trim($val);
            return strlen($trimmed) != 0;
        });
        $titles = WebFioriApp::getAppConfig()->getTitles();
        $titles[$lang] = $newName;
        ConfigController::get()->updateSiteInfo([
            'titles' => $titles
        ]);
        $this->println('Title successfully updated.');
    }
    private function _updateDescription() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new description:', null, function ($val) {
            $trimmed = trim($val);
            return strlen($trimmed) != 0;
        });
        $descriptions = WebFioriApp::getAppConfig()->getDescriptions();
        $descriptions[$lang] = $newName;
        ConfigController::get()->updateSiteInfo([
            'descriptions' => $descriptions
        ]);
        $this->println('Description successfully updated.');
    }
    private function _updateName() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new name:', null, function ($val) {
            $trimmed = trim($val);
            return strlen($trimmed) != 0;
        });
        $names = WebFioriApp::getAppConfig()->getWebsiteNames();
        $names[$lang] = $newName;
        ConfigController::get()->updateSiteInfo([
            'website-names' => $names
        ]);
        $this->println('Name successfully updated.');
    }
    private function whichLang() {
        $langs = array_keys(WebFioriApp::getAppConfig()->getWebsiteNames());
        return $this->select('In which language you would like to update?', $langs);
    }
}
