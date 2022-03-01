<?php
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\writers\TableClassWriter;
use webfiori\database\Table;
use webfiori\framework\cli\helpers\TableObjHelper;
/**
 * Description of UpdateTableObj
 *
 * @author Ibrahim BinAlshikh
 */
class CreateUpdateTableHelper extends CreateClassHelper {

    public function __construct(CreateCommand $command, Table $table) {
        parent::__construct($command, new TableClassWriter($table));

        $whatToDo = $this->askForToDo();
        
        $update = new TableObjHelper($this, $table);
        
        if ($whatToDo == 'Add new column.') {
            $update->addColumn();
            $update->getCreateHelper()->writeClass();
            $this->success('New column was added to the table.');
        } else if ($whatToDo == 'Drop column.') {
            $update->dropColumn();
        } else if ($whatToDo == 'Add foreign key.') {
            $update->addForeignKey();
        } else if ($whatToDo == 'Update existing column.') {
            $update->updateColumn();
        } else if ($whatToDo == 'Drop foreign key.') {
            $update->removeForeignKey();
        } else {
            $this->error('Option not implemented.');
        }
    }
    public function askForToDo() {
        $whatToDo = $this->select('What operation whould you like to do with the table?', [
            'Add new column.',
            'Add foreign key.',
            'Update existing column.',
            'Drop column.',
            'Drop foreign key.'
        ]);
        return $whatToDo;
    }
}
