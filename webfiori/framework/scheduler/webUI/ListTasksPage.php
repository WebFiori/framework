<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\scheduler\webUI;

use webfiori\file\File;
/**
 * A view to display information about scheduled tasks.
 * 
 * The view will show a table of all scheduled tasks. The table will include 
 * the following information about each task:
 * <ul>
 * <li>The name of the task.</li>
 * <li>Its cron expression.</li>
 * <li>
 * 5 columns that shows if it is time to execute the task or not 
 * (Yes, No). The columns are:
 * <ul>
 * <li>Is Minute: Is it current minute in the hour to run the task.</li>
 * <li>Is Hour: Is it current hour in the day to run the task.</li>
 * <li>Is Day of Month: Is it the date in month to run the task.</li>
 * <li>Is month: Is it current month in year to run the task.</li>
 * <li>Is day of week: Is it current day of week to run the task.</li>
 * </ul>
 * </li>
 * </ul>
 * Also, there is a section that shows execution logs 
 * of tasks.
 * 
 * @version 1.0
 */
class ListTasksPage extends BaseTasksPage {
    /**
     * Creates new instance of the view.
     */
    public function __construct() {
        parent::__construct('Scheduled Tasks', 'A list of scheduled tasks.');

        $searchRow = $this->insert('v-row');
        $searchRow->addChild('v-col', [
                    'cols' => 12, 'sm' => 12, 'md' => 4
                ])
                ->addChild('v-text-field', [
                    'label' => 'Search for a specific task...',
                    'v-model' => 'search',
                    'dense', 'outlined'
                ]);

        $row = $this->insert('v-row');

        $row->addChild('v-col', [
            'cols' => 12
        ])->addChild(new TasksTable());

        $logRow = $this->insert('v-row');
        $card = $logRow->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-card');
        $card->addChild('v-card-title')->text('tasks Execution Log');
        $file = new File(ROOT_PATH.DS.APP_DIR.DS.'sto'.DS.'logs'.DS.'tasks.log');

        if ($file->isExist()) {
            $file->read();
            $card->addChild('v-card-text')->addChild('pre')->text($file->getRawData());
        } else {
            $file->create();
            $file->write();
            $card->addChild('v-card-text')->addChild('pre', [
                'style' => 'color:red'
            ])->text('Log file not found!');
        }
    }
}
