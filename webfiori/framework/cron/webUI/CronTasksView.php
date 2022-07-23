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
namespace webfiori\framework\cron\webUI;

use webfiori\framework\cron\Cron;
use webfiori\file\File;
use webfiori\ui\HTMLNode;
/**
 * A view to display information about CRON Jobs.
 * The view will show a table of all scheduled cron jobs. The table will include 
 * the following information about each job:
 * <ul>
 * <li>The name of the job.</li>
 * <li>Its cron expression.</li>
 * <li>
 * 5 columns that shows if it is time to execute the job or not 
 * (Yes, No). The columns are:
 * <ul>
 * <li>Is Minute: Is it current minute in the hour to run the job.</li>
 * <li>Is Hour: Is it current hour in the day to run the job.</li>
 * <li>Is Day of Month: Is it the date in month to run the job.</li>
 * <li>Is month: Is it current month in year to run the job.</li>
 * <li>Is day of week: Is it current day of week to run the job.</li>
 * </ul>
 * </li>
 * </ul>
 * In addition to that, the view contains controls to make the page refresh 
 * every one minute. Also, there is a section that shows execution logs 
 * of cron jobs.
 * @version 1.0
 */
class CronTasksView extends CronView {
    /**
     * Creates new instance of the view.
     */
    public function __construct() {
        parent::__construct('Scheduled CRON Tasks', 'A list of available CRON jobs.');

        $searchRow = $this->insert('v-row');
        $searchRow->addChild('v-col', [
                    'cols' => 12, 'sm' => 12, 'md' => 4
                ])
                ->addChild('v-text-field', [
                    'label' => 'Search for a specific job...',
                    'v-model' => 'search',
                    'dense', 'outlined'
                ]);

        $row = $this->insert('v-row');

        $table = $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-data-table', [
            ':items' => 'jobs',
            ':loading' => 'loading',
            ':headers' => 'jobs_table_headers',
            'show-expand', 'single-expand',
            ':expanded.sync' => "expanded",
            'item-key' => "name",
            ':search' => 'search'
        ]);


        $this->addIsTimeSlot($table, 'is_minute');
        $this->addIsTimeSlot($table, 'is_hour');
        $this->addIsTimeSlot($table, 'is_day_of_week');
        $this->addIsTimeSlot($table, 'is_month');
        $this->addIsTimeSlot($table, 'is_day_of_month');
        $table->addChild('template', [
            '#item.actions' => '{ item }'
        ])->addChild('v-btn', [
            '@click' => 'forceExec(item)',
            ':loading' => 'item.executing',
            ':disabled' => 'loading',
            'x-small', 'color' => 'primary'
        ])->text('Force Execution');
        $tableRow = $table->addChild('template', [
            '#expanded-item' => "{ headers, item }"
        ])->addChild('td', [
            ':colspan' => "headers.length"
        ])->addChild('div', [
            'style' => [
                'padding' => '20px'
            ]
        ])->addChild('v-row');

        $tableRow->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => 6
        ])->addChild('v-textarea', [
            'label' => 'Job Description',
            'v-model' => 'item.description',
            'disabled', 'outlined'
        ]);
        $card = $tableRow->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => 6
        ])->addChild('div');
        $card->addChild('h3')->text('Job Arguments');
        $card->addChild('div', [
            'v-if' => 'item.args.length !== 0'
        ])->addChild('v-tooltip', [
            'left',
            'v-for' => 'arg in item.args'
        ])->addChild('template', [
            '#activator' => '{ on, attrs }'
        ])->addChild('v-text-field', [
            'outlined', 'dense',
            'v-model' => 'arg.value',
            ':label' => 'arg.name',
            'v-bind' => "attrs",
            'v-on' => "on"
        ], true)->getParent()->addChild('span')->text('{{ arg.description }}');
        $card->addChild('p', [
            'v-else'
        ])->text('No Arguments.');

        $logRow = $this->insert('v-row');
        $card = $logRow->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-card');
        $card->addChild('v-card-title')->text('Jobs Execution Log');
        $file = new File(ROOT_DIR.DS.APP_DIR_NAME.DS.'sto'.DS.'logs'.DS.'cron.log');

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

    /**
     * 
     * @param HTMLNode $table
     * @param type $slot
     */
    private function addIsTimeSlot(&$table, $slot) {
        $template = $table->addChild('template', [
            '#item.time.'.$slot => '{ item }'
        ]);
        $template->addChild('v-chip', [
            'v-if' => 'item.time.'.$slot,
            'color' => 'green',
            'small'
        ])->text('Yes');
        $template->addChild('v-chip', [
            'v-else',
            'color' => 'red',
            'small'
        ])->text('No');
    }
}
