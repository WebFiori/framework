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

use Throwable;
use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Cli\InputValidator;
use WebFiori\Framework\App;
use WebFiori\Framework\Config\Controller;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Theme;
/**
 * This class implements a CLI command which is used to update the settings which are
 * stored in the class 'AppConfing' of the application.
 *
 * @author Ibrahim
 *
 * @since 2.3.1
 */
class UpdateSettingsCommand extends Command {
    public function __construct() {
        parent::__construct('update-settings', [
            new Argument('--w', 'An argument which is used to indicate what will be updated. '
                .'Possible values are: version, app-name, scheduler-pass, page-title, '
                .'page-description, primary-lang, title-sep, home-page, theme,'
                .'admin-theme.', true),
        ], 'Update application settings which are stored in specific configuration driver.');
    }
    public function exec() : int {
        $options = [];
        $this->addOption($options,'version', 'Update application version info.');
        $this->addOption($options,'app-name', 'Update application name.');
        $this->addOption($options,'scheduler-pass', 'Update scheduler password.');
        $this->addOption($options,'page-title', 'Update default page title.');
        $this->addOption($options,'page-description', 'Update default page description.');
        $this->addOption($options,'primary-lang', 'Change primary language.');
        $this->addOption($options,'title-sep', 'Change title separator.');
        $this->addOption($options,'home-page', 'Set home page.');
        $this->addOption($options,'theme', 'Set primay theme.');
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
            $this->updateName();
        } else if ($answer == 'Update default page title.') {
            $this->updateTitle();
        } else if ($answer == 'Update scheduler password.') {
            $this->updateSchedulerPass();
        } else if ($answer == 'Change title separator.') {
            $this->updateTitleSep();
        } else if ($answer == 'Update default page description.') {
            $this->updateDescription();
        } else if ($answer == 'Change primary language.') {
            $this->updatePrimaryLang();
        } else if ($answer == 'Set primay theme.') {
            $this->setAdminTheme();
        } else if ($answer == 'Set home page.') {
            $this->setHome();
        } else if ($answer == 'Update application version info.') {
            $this->updateVersionInfo();
        }

        return 0;
    }
    private function addOption(&$optArr, $key, $txt) {
        $optArr[$key] = $txt;
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

                return false;
            } catch (Throwable $exc) {
                return false;
            }
        }));
    }
    private function setAdminTheme() {
        $classNs = $this->getThemeNs();
        Controller::getDriver()->setTheme($classNs);
        $this->success('Theme successfully updated.');
    }
    private function setHome() {
        $routes = array_keys(Router::routes());

        if (count($routes) == 0) {
            $this->info('Router has no routes. Nothing to change.');

            return;
        }
        $home = $this->select('Select home page route:', $routes);
        Controller::getDriver()->setHomePage(substr($home, strlen(Router::base()) + 1));
        $this->success('Home page successfully updated.');
    }
    private function updateDescription() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new description:', null, new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        Controller::getDriver()->setDescription($newName, $lang);
        $this->success('Description successfully updated.');
    }
    private function updateName() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new name:', null, new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        Controller::getDriver()->setAppName($newName, $lang);
        $this->println('Name successfully updated.');
    }
    private function updatePrimaryLang() {
        $langs = array_keys(App::getConfig()->getAppNames());
        $newPrimary = $this->select('Select new primary language:', $langs);
        Controller::getDriver()->setPrimaryLanguage($newPrimary);
        $this->success('Primary language successfully updated.');
    }
    private function updateSchedulerPass() {
        $newPass = $this->getInput('Enter new password:', null, new InputValidator(function (string $val)
        {
            return strlen(trim($val)) != 0;
        }, 'Empty string is not allowed.'));

        Controller::getDriver()->setSchedulerPassword(hash('sha256',$newPass));
        $this->success('Password successfully updated.');
    }
    private function updateTitle() {
        $lang = $this->whichLang();
        $newName = $this->getInput('Enter new title:', null, new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        Controller::getDriver()->setTitle($newName, $lang);
        $this->success('Title successfully updated.');
    }
    private function updateTitleSep() {
        $newSep = $this->getInput('Enter new title separator string:', '|', new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        Controller::getDriver()->setTitleSeparator($newSep);
        $this->success('Title separator successfully updated.');
    }
    private function updateVersionInfo() {
        $versionNum = $this->getInput('Application version:', App::getConfig()->getAppVersion(), new InputValidator(function ($val)
        {
            return strlen(trim($val)) != 0;
        }));
        $versionType = $this->getInput('Application version type:', App::getConfig()->getAppVersionType(), new InputValidator(function ($val)
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
        Controller::getDriver()->setAppVersion($versionNum, $versionType, date('Y-m-d', strtotime($versionReleaseDate)));
        $this->println('Version information successfully updated.');
    }
    private function whichLang() {
        $langs = array_keys(App::getConfig()->getAppNames());

        return $this->select('In which language you would like to update?', $langs);
    }
}
