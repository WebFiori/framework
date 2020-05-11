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

use webfiori\entity\Theme;

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
        parent::__construct('--list-themes', [
            'theme-name' => [
                'optional' => true,
                'description' => 'An optional theme name. If provided, only given '
                .'theme information will be shown.'
            ]
        ], 'List all registered themes.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() {
        $themesArr = Theme::getAvailableThemes();

        $themsCount = count($themesArr);
        $themeName = $this->getArgValue('theme-name');

        $index = 1;

        if ($themeName !== null) {
            if (isset($themesArr[$themeName])) {
                $this->_printThemeObj($themesArr[$themeName]);
            } else {
                fprintf(STDERR, "Error: No theme was registered which has the name '$themeName'.\n");

                return -1;
            }
        }
        fprintf(STDOUT, "Total Number of Themes: $themsCount .\n");

        foreach ($themesArr as $themeObj) {
            if ($index < 10) {
                fprintf(STDOUT, $this->formatOutput("--------- Job #0$index ---------\n", [
                    'color' => 'light-blue',
                    'bold' => true
                ]));
            } else {
                fprintf(STDOUT, $this->formatOutput("--------- Theme #0$index ---------\n", [
                    'color' => 'light-blue',
                    'bold' => true
                ]));
            }
            $this->_printThemeObj($themeObj);
            $index++;
        }

        return 0;
    }

    private function _printThemeObj($themeObj) {
        $spaceSize = 15;
        $len00 = $spaceSize - strlen('Theme Name');
        $len01 = $spaceSize - strlen('Author');
        $len02 = $spaceSize - strlen('Author URL');
        $len03 = $spaceSize - strlen('License');
        $len04 = $spaceSize - strlen('License URL');

        fprintf(STDOUT, "Theme Name: %".$len00."s %s\n",':',$themeObj->getName());
        fprintf(STDOUT, "Author: %".$len01."s %s\n",':',$themeObj->getAuthor());
        fprintf(STDOUT, "Author URL: %".$len02."s %s\n",':',$themeObj->getAuthorUrl());
        fprintf(STDOUT, "License: %".$len03."s %s\n",':',$themeObj->getLicenseName());
        fprintf(STDOUT, "License URL: %".$len04."s %s\n",':',$themeObj->getLicenseUrl());
        fprintf(STDOUT, "Theme Desription: \n%s\n",$themeObj->getDescription());
    }
}
