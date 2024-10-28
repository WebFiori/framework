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

use webfiori\cli\Argument;
use webfiori\cli\CLICommand;
use webfiori\framework\ThemeLoader;

/**
 * A CLI command which is used to list all registered themes.
 *
 * @author Ibrahim
 */
class ListThemesCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--list-themes'. In addition to that,
     * it will have the following arguments:
     * <ul>
     * <li><b>theme-name</b>: If specified, only information about given theme
     * will be shown.</li>
     * </ul>
     */
    public function __construct() {
        parent::__construct('list-themes', [
            new Argument('--theme-name', 'An optional theme name. If provided, only given theme information will be shown.', true)
        ], 'List all registered themes.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $themesArr = ThemeLoader::getAvailableThemes();

        $themsCount = count($themesArr);
        $themeName = $this->getArgValue('--theme-name');

        $index = 1;

        if ($themeName === null) {
            $this->println("Total Number of Themes: $themsCount .");

            foreach ($themesArr as $themeObj) {
                $number = $index < 10 ? '0'.$index : $index;

                $this->println("--------- Theme #$number ---------\n", [
                    'color' => 'light-blue',
                    'bold' => true
                ]);
                $this->printThemeObj($themeObj);
                $index++;
            }

            return 0;
        }

        if (!isset($themesArr[$themeName])) {
            $this->error("No theme was registered which has the name '$themeName'.");

            return -1;
        }
        $this->printThemeObj($themesArr[$themeName]);

        return 0;
    }
    private function isSet($var) {
        if (strlen($var) == 0) {
            return '<NOT SET>';
        }

        return $var;
    }

    private function printThemeObj($themeObj) {
        $spaceSize = 15;
        $len00 = $spaceSize - strlen('Theme Name');
        $len01 = $spaceSize - strlen('Author');
        $len02 = $spaceSize - strlen('Author URL');
        $len03 = $spaceSize - strlen('License');
        $len04 = $spaceSize - strlen('License URL');

        $this->println("Theme Name: %".$len00."s %s",':', $this->isSet($themeObj->getName()));
        $this->println("Author: %".$len01."s %s",':', $this->isSet($themeObj->getAuthor()));
        $this->println("Author URL: %".$len02."s %s",':', $this->isSet($themeObj->getAuthorUrl()));
        $this->println("License: %".$len03."s %s",':', $this->isSet($themeObj->getLicenseName()));
        $this->println("License URL: %".$len04."s %s",':', $this->isSet($themeObj->getLicenseUrl()));
        $this->println("Theme Desription: %s", $this->isSet($themeObj->getDescription()));
    }
}
