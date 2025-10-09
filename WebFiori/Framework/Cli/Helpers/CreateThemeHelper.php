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
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\ThemeClassWriter;
/**
 * Description of CreateTheme
 *
 * @author Ibrahim
 */
class CreateThemeHelper extends CreateClassHelper {
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new ThemeClassWriter('New Theme'));
    }
    public function readClassInfo() {
        $this->setClassInfo('Themes', 'Theme');

        $this->println('Creating theme at "'.$this->getWriter()->getPath().'"...');
        $this->writeClass();
    }
}
