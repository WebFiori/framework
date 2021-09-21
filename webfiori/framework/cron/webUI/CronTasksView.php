<?php
/*
 * The MIT License
 *
 * Copyright 2018, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\framework\cron\webUI;

use webfiori\framework\File;
use webfiori\framework\WebFioriApp;
use webfiori\ui\HTMLNode;
use webfiori\ui\Input;
use webfiori\ui\Label;
use webfiori\ui\Paragraph;
use webfiori\ui\TableCell;
use webfiori\ui\TableRow;
use webfiori\framework\cron\Cron;
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
        
        $row = $this->insert('v-row');
        
        $table = $row->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-data-table', [
            ':items' => 'jobs',
            ':loading' => 'loading',
            ':headers' => 'jobs_table_headers',
            'dense', 'show-expand'
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
            'small', 'color' => 'primary'
        ])->text('Force Execution');
        $tableRow = $table->addChild('template', [
            '#expanded-item' => "{ headers, item }"
        ])->addChild('td', [
            ':colspan' => "headers.length"
        ])->addChild('v-row');
        $tableRow->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => 4
        ])->addChild('v-text-field', [
            'label' => 'Name',
            'v-model' => 'item.name',
            'disabled'
        ]);
        $tableRow->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => 4
        ])->addChild('v-textarea', [
            'label' => 'Job Description',
            'v-model' => 'item.description',
            'disabled'
        ]);
        $card = $tableRow->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => '4'
        ])->addChild('v-card');
        $card->addChild('v-card-title')->text('Job Arguments');
        $card->addChild('v-card-text', [
            'v-if' => 'item.args.length !== 0',
            'v-for' => 'arg in item.args'
        ])->addChild('v-row')->addChild('v-col', [
            'cols' => 12
        ])->addChild('v-text-field', [
            'label' => 'arg'
        ]);
        $card->addChild('v-card-text', [
            'v-else'
        ])->text('No Arguments.');
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
            'color' => 'green'
        ])->text('Yes');
        $template->addChild('v-chip', [
            'v-else',
            'color' => 'red'
        ])->text('No');
    }
}
