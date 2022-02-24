<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\CLICommand;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\ThemeClassWriter;
use webfiori\framework\cli\helpers\CreateClassHelper;
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
        parent::__construct($command);

        $this->setClassInfo('themes', 'Theme');
        
        $this->setWriter(new ThemeClassWriter());

        $command->println('Creating theme at "'.$this->getWriter()->getPath().'"...');
        $this->writeClass();
    }
}
