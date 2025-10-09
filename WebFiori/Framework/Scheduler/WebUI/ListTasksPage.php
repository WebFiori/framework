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
namespace WebFiori\Framework\Scheduler\WebUI;

use WebFiori\File\File;
use WebFiori\Http\Response;
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

        if (!$this->isLoggedIn()) {
            Response::addHeader('location', $this->getBase().'/scheduler/login');
            Response::send();
        }
        $this->includeLogoutButton();
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
        ])->addChild($this->include('templates/tasks-table.html'));

        $logRow = $this->insert('v-row');
        $card = $logRow->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-card');
        $card->addChild('v-card-title')->text('Tasks Execution Log');
        $file = new File(APP_PATH.'sto'.DS.'logs'.DS.'tasks-execution.log');

        if ($file->isExist()) {
            $file->read();
            $data = $file->getRawData();

            if (strlen($data) == 0) {
                $card->addChild('v-card-text')->addChild('pre')->text('Empty log file.');
            } else {
                $card->addChild('v-card-text')->addChild('pre')->text($file->getRawData());
            }
        } else {
            $file->create();
            $card->addChild('v-card-text')->addChild('pre', [
                'style' => 'color:red'
            ])->text('Log file not found!');
        }

        $this->insert($this->include('templates/job-execution-status-dialog.html'));
        $this->insert($this->include('templates/job-output-dialog.html'));
    }
    private function includeLogoutButton() {
        $row = $this->insert('v-row');
        $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-btn', [
            '@click' => 'logout',
            'color' => 'primary',
            ':loading' => 'loading'
        ])->text('Logout');
    }
}
