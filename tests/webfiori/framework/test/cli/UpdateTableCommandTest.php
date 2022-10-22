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
            'tables\\EmployeeInfoTable',
            '0',
            'new-col',
            '1',
            '9',
            'n',
            'n',
            
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            
            "Enter the name of table class (including namespace):\n",
            "Given class name is invalid!\n",
            "Class \"'ok\\y\\Super'\" not found",
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
            "2: bigint\n",
            "3: varchar\n",
            "4: nvarchar\n",
            "5: char\n",
            "6: nchar\n",
            "7: binary\n",
            "8: varbinary\n",
            "9: date\n",
            "10: datetime2\n",
            "11: time\n",
            "12: money\n",
            "13: bit\n",
            "14: decimal\n",
            "15: float\n",
            "16: boolean\n",
            "17: bool\n",
        ], $runner->getOutput());
    }
}
