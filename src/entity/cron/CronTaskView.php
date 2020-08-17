<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\cron;

use phpStructs\html\HTMLNode;
use phpStructs\html\JsCode;
use phpStructs\html\PNode;
use phpStructs\html\TableCell;
use phpStructs\html\TableRow;
use webfiori\entity\Page;
use webfiori\entity\router\Router;
use webfiori\WebFiori;
/**
 * A view to show details of a specific CRON task.
 *
 * @author Ibrahim
 * @version 1.0
 */
class CronTaskView extends CronView {
    /**
     * The job that the view will display its info.
     * @var CronJob 
     * @since 1.0
     */
    private $job;
    /**
     * @since 1.0
     */
    public function __construct() {
        parent::__construct('View Task', 'Display task info.');
        $jobName = Router::getVarValue('job-name');
        $job = Cron::getJob($jobName);
        $this->job = $job;

        if ($job instanceof AbstractJob) {
            $backButton = new HTMLNode('button');
            $backButton->addTextNode('Back to Jobs List');
            $backButton->setAttribute('onclick',"window.location.href = 'cron/jobs';");
            $backButton->setStyle([
                'float' => 'left'
            ]);
            $backButton->setName('input-element');
            $this->getControlsContainer()->addChild($backButton);
            $this->getControlsContainer()->addTextNode('<br/>', false);
            $this->_createInfoTable($job);
            $this->_createCustomParamsContainer();
            $custAttrsNames = $job->getExecArgsNames();
            $custAtrrsAsJsonArr = '[';

            for ($x = 0 ; $x < count($custAttrsNames) ; $x++) {
                if ($x == 0) {
                    $custAtrrsAsJsonArr .= '"'.$custAttrsNames[$x].'"';
                } else {
                    $custAtrrsAsJsonArr .= ',"'.$custAttrsNames[$x].'"';
                }
            }
            $custAtrrsAsJsonArr .= ']';
            $isRefresh = 'false';

            if (isset($_GET['refresh'])) {
                $isRefresh = 'true';
            }
            $jsCode = new JsCode();
            $jsCode->addCode(''
                    .'window.onload = function(){'."\n"
                    .'     window.isRefresh = '.$isRefresh.';'
                    .'     window.customAttrsArr = '.$custAtrrsAsJsonArr.';'
                    .'     window.customAttrs = [];'
                    .'     for(var x = 0 ; x < window.customAttrsArr.length ; x++){'
                    .'         addAttribute(window.customAttrsArr[x]);'
                    .'     }'."\n"
                    ."     "
                    .'     window.intervalId = window.setInterval(function(){'."\n"
                    .'         if(window.isRefresh){'."\n"
                    .'             disableOrEnableInputs();'."\n"
                    .'             document.getElementById(\'refresh-label\').innerHTML = \'<b>Refreshing...</b>\';'."\n"
                    .'             window.location.href = \'cron/jobs?refresh=yes\';'."\n"
                    .'         }'."\n"
                    .'     },60000)'."\n"
                    .' };'."\n"
                    );
            Page::document()->getHeadNode()->addChild($jsCode);
            $forceNode = new HTMLNode();
            $forceNode->addTextNode('<button style="margin-top:30px" name="input-element" onclick="execJob(this,\''.$job->getJobName().'\')" class="force-execution-button">Force Execution</button>', false);
            $this->getControlsContainer()->addChild($forceNode);
            $this->createOutputWindow();
        } else {
            Response::addHeader('location', WebFiori::getSiteConfig()->getBaseURL().'cron/jobs');
            Response::send();
        }
        Page::render();
    }

    private function _createCustomParamsContainer() {
        $h2 = new HTMLNode('h2');
        $h2->addTextNode('Custom Execution Parameters');
        $this->getControlsContainer()->addChild($h2);
        $p = new PNode();
        $p->addText('Here you can add extra parameters which will be sent with '
                .'force execute command.');
        $this->getControlsContainer()->addChild($p);
        $table = new HTMLNode('table');
        $table->setID('custom-params-table');
        $table->setAttribute('border', 1);
        $table->setStyle([
            'border-collapse' => 'collapse',
            'margin-top' => '30px'
        ]);
        $headerRow = new TableRow();
        $headerRow->setStyle([
            'border-bottom' => 'double',
            'background-color' => 'rgba(66,234,88,0.3)',
            'font-weight' => 'bold'
        ]);
        $headerRow->setClassName('tasks-table-header-row');
        $nameCell = new TableCell();
        $nameCell->setClassName('tasks-table-header-cell');
        $nameCell->setStyle([
            'padding' => '10px'
        ]);
        $nameCell->addTextNode('Attribute Name');
        $headerRow->addChild($nameCell);

        $valueCell = new TableCell();
        $valueCell->setClassName('tasks-table-header-cell');
        $valueCell->setStyle([
            'padding' => '10px'
        ]);
        $valueCell->addTextNode('Attribute Value');
        $headerRow->addChild($valueCell);

        $removeCell = new TableCell();
        $removeCell->setClassName('tasks-table-header-cell');
        $removeCell->setStyle([
            'padding' => '10px'
        ]);
        $removeCell->addTextNode('Remove');
        $headerRow->addChild($removeCell);

        $table->addChild($headerRow);
        $noDataRow = new TableRow();
        $noDataRow->setID('no-attributes-row');
        $noDataCell = new TableCell();
        $noDataCell->setColSpan(3);
        $noDataCell->addTextNode('No Attributes');
        $noDataRow->addChild($noDataCell);
        $table->addChild($noDataRow);
        $addRow = new TableRow();
        $addRow->setID('add-attr-button-row');
        $addButtonCell = new TableCell();
        $addButtonCell->setColSpan(3);
        $addButtonCell->setID('add-attr-button-cell');
        $addButtonCell->addTextNode('<button name="input-element" class="ok" style="width:100%" onclick="addAttribute()">Add Attribute</button>', false);
        $addRow->addChild($addButtonCell);
        $table->addChild($addRow);
        $this->getControlsContainer()->addChild($table);
    }

    /**
     * 
     * @param CronJob $job
     */
    private function _createInfoTable($job) {
        $taskTable = new HTMLNode('table');
        $taskTable->setID('tasks-table');
        $this->getControlsContainer()->addChild($taskTable);
        $taskTable->setAttribute('border', 1);
        $row1 = new TableRow();
        $row1->addCell('<b>Task Name:</b>','td',false);
        $row1->addCell($job->getJobName());
        $taskTable->addChild($row1);

        $row2 = new TableRow();
        $row2->addCell('<b>CRON Expression:</b>','td',false);
        $row2->addCell($job->getExpression());
        $taskTable->addChild($row2);

        $row3 = new TableRow();
        $row3->addCell('<b>Is Minute:</b>','td',false);
        $row3->addChild($this->_createTasksTableCell($job->isMinute()));
        $taskTable->addChild($row3);

        $row4 = new TableRow();
        $row4->addCell('<b>Is Houre:</b>','td',false);
        $row4->addChild($this->_createTasksTableCell($job->isHour()));
        $taskTable->addChild($row4);

        $row5 = new TableRow();
        $row5->addCell('<b>Is Day of Month:</b>','td',false);
        $row5->addChild($this->_createTasksTableCell($job->isDayOfMonth()));
        $taskTable->addChild($row5);

        $row6 = new TableRow();
        $row6->addCell('<b>Is Month:</b>','td',false);
        $row6->addChild($this->_createTasksTableCell($job->isMonth()));
        $taskTable->addChild($row6);

        $row7 = new TableRow();
        $row7->addCell('<b>Is Day of Week:</b>','td',false);
        $row7->addChild($this->_createTasksTableCell($job->isDayOfWeek()));
        $taskTable->addChild($row7);
    }
    private function _createTasksTableCell($isTime) {
        $cell = new TableCell();
        $cell->setClassName('tasks-table-cell');

        if ($isTime) {
            $cell->setClassName('yes-cell');
            $cell->addTextNode('Yes');
        } else {
            $cell->setClassName('no-cell');
            $cell->addTextNode('No');
        }

        return $cell;
    }
}

return __NAMESPACE__;
