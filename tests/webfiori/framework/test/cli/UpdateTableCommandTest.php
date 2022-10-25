<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\cli\Runner;
use webfiori\framework\cli\commands\UpdateSettingsCommand;
use webfiori\framework\ConfigController;
use webfiori\framework\WebFioriApp;

class UpdateTableCommandTest extends TestCase {
    /**
     * 
     */
    public function test00() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'update-table',
        ]);
        $runner->setInput([
            '   ',
            'ok\\y\\Super',
            'app\\database\\TestTable',
            '0',
            'new-col',
            '1',
            '9',
            'n',
            'n',
            '',
            'n',
            'Cool new column.'
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Enter database table class name (include namespace):\n",
            "Error: Class not found.\n",
            "Enter database table class name (include namespace):\n",
            "Error: Class not found.\n",
            "Enter database table class name (include namespace):\n",
            "What operation whould you like to do with the table?\n",
            "0: Add new column.\n",
            "1: Add foreign key.\n",
            "2: Update existing column.\n",
            "3: Drop column.\n",
            "4: Drop foreign key.\n",
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: mixed <--\n",
            "1: int\n",
            "2: char\n",
            "3: varchar\n",
            "4: timestamp\n",
            "5: tinyblob\n",
            "6: blob\n",
            "7: mediumblob\n",
            "8: longblob\n",
            "9: datetime\n",
            "10: text\n",
            "11: mediumtext\n",
            "12: decimal\n",
            "13: double\n",
            "14: float\n",
            "15: boolean\n",
            "16: bool\n",
            "17: bit\n",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
        ], $runner->getOutput());
    }
}
