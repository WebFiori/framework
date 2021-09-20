<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\CLICommand;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\ThemeClassWriter;
/**
 * Description of CreateTheme
 *
 * @author Ibrahim
 */
class CreateThemeHelper {
    /**
     *
     * @var CLICommand 
     */
    private $command;
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->command = $command;

        $classInfo = $this->_getCommand()->getClassInfo('themes');
        $writer = new ThemeClassWriter($classInfo['path'], $classInfo['name']);
        $command->println('Creating theme at "'.$writer->getPath().'"...');
        $writer->writeClass();
        $command->success('Created.');
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
    }
}
