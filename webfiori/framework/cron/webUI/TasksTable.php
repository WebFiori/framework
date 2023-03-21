<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2022 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cron\webUI;

use webfiori\ui\HTMLNode;

/**
 * A table which is used to list all scheduled background job.
 * 
 * This UI component is used by the page which is used to list all
 * cron jobs. The table has job information including arguments,
 * description and the ability to force execute a job.
 *
 * @author Ibrahim
 */
class TasksTable extends HTMLNode {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('v-data-table', [
            ':items' => 'jobs',
            ':loading' => 'loading',
            ':headers' => 'jobs_table_headers',
            'show-expand', 'single-expand',
            ':expanded.sync' => "expanded",
            'item-key' => "name",
            ':search' => 'search'
        ]);

        $this->createInfoCol();
        $this->addIsTimeSlot('is_minute');
        $this->addIsTimeSlot('is_hour');
        $this->addIsTimeSlot('is_day_of_week');
        $this->addIsTimeSlot('is_month');
        $this->addIsTimeSlot('is_day_of_month');

        $this->createExpansionArea();
        $this->createActions();
    }

        /**
         * 
         * @param HTMLNode $table
         * @param type $slot
         */
    private function addIsTimeSlot($slot) {
        $template = $this->addChild('template', [
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

    private function createActions() {
        $this->addChild('template', [
            '#item.actions' => '{ item }'
        ])->addChild('v-btn', [
            '@click' => 'forceExec(item)',
            ':loading' => 'item.executing',
            ':disabled' => 'loading',
            'x-small', 'color' => 'primary'
        ])->text('Force Execution');
    }

    private function createExpansionArea() {
        $tableRow = $this->addChild('template', [
            '#expanded-item' => "{ headers, item }"
        ])->addChild('td', [
            ':colspan' => "headers.length"
        ])->addChild('div', [
            'style' => [
                'padding' => '20px'
            ]
        ])->addChild('v-row');

        $card = $tableRow->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => 6
        ])->addChild('div');
        $card->addChild('h3')->text('Job Arguments');

        $textField = $card->addChild('div', [
            'v-if' => 'item.args.length !== 0'
        ])->addChild('v-text-field', [
            'v-for' => 'arg in item.args',
            'outlined', 'dense',
            'v-model' => 'arg.value',
            ':label' => 'arg.name',
        ]);
        $tooltip = $textField->addChild('template', [
            '#prepend'
        ])->addChild('v-tooltip', [
            'bottom'
        ]);
        $tooltip->addChild('template', [
            '#activator' => '{ on, attrs }',
        ])->addChild('v-icon', [
            'v-bind' => "attrs",
            'v-on' => "on",
            'small'
        ])->text('mdi-information');
        $tooltip->addChild('span')->text('{{ arg.description }}');
        $card->addChild('p', [
            'v-else'
        ])->text('No Arguments.');
    }
    private function createInfoCol() {
        $info = $this->addChild('template', [
            '#item.info' => '{ item }'
        ]);
        $vTooltip = $info->addChild('v-tooltip', [
            'bottom'
        ]);
        $vTooltip->addChild('template', [
            '#activator' => '{ on, attrs }',
        ])->addChild('v-icon', [
            'v-bind' => "attrs",
            'v-on' => "on",
            'small'
        ])->text('mdi-information');
        $vTooltip->addChild('span')->text('{{ item.description }}');
    }
}
