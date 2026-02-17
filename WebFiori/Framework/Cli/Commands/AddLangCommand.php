<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Command;
use WebFiori\Framework\App;
use WebFiori\Framework\Writers\LangClassWriter;

/**
 * A command which is used to add a website language.
 *
 * @author Ibrahim
 *
 */
class AddLangCommand extends Command {
    public function __construct() {
        parent::__construct('add:lang', [], 'Add a website language.');
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $langCode = strtoupper(trim($this->getInput('Language code:')));

        if (strlen($langCode) != 2) {
            $this->error('Invalid language code.');

            return -1;
        }

        if (App::getConfig()->getAppName($langCode) !== null) {
            $this->info('This language already added. Nothing changed.');

            return 0;
        }
        App::getConfig()->setAppName($this->getInput('Name of the website in the new language:'), $langCode);
        App::getConfig()->setDescription($this->getInput('Description of the website in the new language:'), $langCode);
        App::getConfig()->setTitle($this->getInput('Default page title in the new language:'), $langCode);
        $writingDir = $this->select('Select writing direction:', [
            'ltr', 'rtl'
        ]);

        $writer = new LangClassWriter($langCode, $writingDir);
        $writer->writeClass();
        $this->success('Language added. Also, a class for the language '
                .'is created at "'.APP_DIR.'\Langs" for that language.');

        return 0;
    }
}
