<?php
/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim, WebFiori Framework.
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
use webfiori\entity\Page;
use phpStructs\html\JsCode;
use phpStructs\html\TableRow;
use phpStructs\html\TableCell;
use phpStructs\html\Input;
use phpStructs\html\HTMLNode;
use phpStructs\html\PNode;
use phpStructs\html\Label;
use phpStructs\html\CodeSnippet;
use webfiori\entity\File;
use webfiori\WebFiori;
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
class CronTasksView extends CronView{
    
    /**
     * Creates new instance of the view.
     */
    public function __construct() {
        parent::__construct('Scheduled CRON Tasks', 'A list of available CRON jobs.');
        $parag = new PNode();
        $tasksCount = Cron::jobsQueue()->size();
        $parag->addText('<b>Total Scheduled Tasks:</b> '.$tasksCount.'.', array('esc-entities'=>false));
        $this->getControlsContainer()->addChild($parag);
        $this->_createRefreshControls();
        $this->_createTasksTable();
        
        $outputWindow = new HTMLNode();
        $outputWindow->setID('output-window');
        $outputWindow->addTextNode('<p style="border:1px dotted;font-weight:bold">Output Window</p><pre'
                . ' style="font-family:monospace" id="output-area"></pre>', false);
        $outputWindow->setStyle([
            'width'=>'100%',
            'float'=>'right',
            'border'=>'1px dotted',
            'overflow-y'=>'scroll',
            'height'=>'300px'
        ]);
        Page::insert($outputWindow);
        $this->_displayExecLog();
        Page::render();
    }
    /**
     * Creates the section that is used to display execution logs of 
     * Cron jobs.
     */
    private function _displayExecLog() {
        $pre = new HTMLNode('pre');
        $pre->setID('execution-log-view');
        $sec = new HTMLNode('section');
        $h = new HTMLNode('h2');
        $h->addTextNode('Jobs Execution Log:');
        $sec->addChild($h);
        $sec->addChild($pre);
        Page::insert($sec);
        if(file_exists(ROOT_DIR.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'cron.txt')){
            $file = new File('cron.txt', ROOT_DIR.DIRECTORY_SEPARATOR.'logs');
            $file->read();
            if(strlen(trim($file->getRawData())) != 0){
                $pre->addTextNode($file->getRawData());
            }
            else{
                $pre->addTextNode('No logs found. Log file is empty.');
            }
        } 
        else{
            $pre->addTextNode('<b style="color:red">Log file not found.</b>',false);
        }
    }
    /**
     * Creates a form which contains the controls that allow the user to 
     * enable refresh functionality.
     */
    private function _createRefreshControls(){
        $form = new HTMLNode('form');
        $form->setID('refresh-controls-form');
        $refreshCheckBox = new Input('checkbox');
        $refreshCheckBox->setName('input-element');
        $refreshCheckBox->setID('refresh-checkbox');
        $refreshCheckBox->setAttribute('onclick', 'window.isRefresh = this.checked;');
        if(isset($_GET['refresh']) && $_GET['refresh'] == 'yes'){
            $refreshCheckBox->setAttribute('checked');
        }
        $form->addChild($refreshCheckBox);
        $label = new Label('Refresh The Page Every <input style="width:50px" disabled value="60" id="refresh-time-input" min="5" type="number"> Second(s).');
        $label->setStyle([
            'display'=>'inline-block'
        ]);
        $label->setAttribute('for', 'refresh-checkbox');
        $label->setID('refresh-label');
        $form->addChild($label);
        $this->getControlsContainer()->addChild($form);
    }
    /**
     * Creates the table that is used to display cron jobs information.
     */
    private function _createTasksTable() {
        $tasksTable = new HTMLNode('table');
        $tasksTable->setID('tasks-table');
        $this->getControlsContainer()->addChild($tasksTable);
        $tasksTable->setAttribute('border', 1);
        $tasksTable->setStyle(array(
            'border-collapse'=>'collapse',
            'margin-top'=>'30px'
        ));
        $tableHeader = new TableRow();
        $tableHeader->setID('tasks-table-header-row');
        $tasksTable->addChild($tableHeader);
        $tableHeader->setStyle(array(
            'border-bottom'=>'double',
            'background-color'=>'rgba(66,234,88,0.3)',
            'font-weight'=>'bold'
        ));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Job Name'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Cron Excepression'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Is Minute'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Is Hour'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Is Day of Month'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Is Month'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Is Day of Week'));
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Execute'));
        $jobsQueue = Cron::jobsQueue();
        if($jobsQueue->size() == 0){
            $cell = new TableCell();
            $cell->setColSpan(8);
            $cell->addTextNode('No Jobs Has Been Scheduled.');
            $cell->setStyle(array(
                'background-color'=>'lightgray',
                'text-align'=>'center',
                'font-weight'=>'bold'
            ));
            $tasksTable->addChild($cell);
        }
        else{
            while ($job = $jobsQueue->dequeue()){
                $row = new TableRow();
                $row->setClassName('tasks-table-row');
                $jobNameCell = new TableCell();
                $jobNameCell->setClassName('tasks-table-cell');
                $jobNameCell->addTextNode('<a href="'.WebFiori::getSiteConfig()->getBaseURL().'cron/jobs/'.$job->getJobName().'">'.$job->getJobName().'</a>',false);
                $row->addChild($jobNameCell);
                $exprCell = new TableCell();
                $jobNameCell->setClassName('tasks-table-cell');
                $exprCell->addTextNode($job->getExpression());
                $row->addChild($exprCell);
                $row->addChild($this->_createTasksTableCell($job->isMinute()));
                $row->addChild($this->_createTasksTableCell($job->isHour()));
                $row->addChild($this->_createTasksTableCell($job->isDayOfMonth()));
                $row->addChild($this->_createTasksTableCell($job->isMonth()));
                $row->addChild($this->_createTasksTableCell($job->isDayOfWeek()));
                $forceCell = new TableCell();
                $forceCell->addTextNode('<button name="input-element" onclick="execJob(this,\''.$job->getJobName().'\')" class="force-execution-button">Force Execution</button>', false);
                $row->addChild($forceCell);
                $tasksTable->addChild($row);
            }
        }
    }
    /**
     * Creates an object of type TableCell that represents cron jobs table 
     * header.
     * @param string $cellText The text that will be displayed in the body of the 
     * cell.
     * @return TableCell
     */
    private function _createTasksTableHeaderCell($cellText) {
        $headerCell = new TableCell('th');
        $headerCell->setClassName('tasks-table-header-cell');
        $headerCell->setStyle(array(
            'padding'=>'10px'
        ));
        $headerCell->addTextNode($cellText);
        return $headerCell;
    }
    /**
     * Creates an object of type TableCell that represents one of cron jobs table 
     * cells.
     * @param boolean $isTime If true is passed, the cell body will have a 'Yes' 
     * in the body and its background color will be green. If false is passed, 
     * the cell body will have a 'No' in the body and its background color will be red.
     * @return TableCell
     */
    private function _createTasksTableCell($isTime){
        $cell = new TableCell();
        $cell->setClassName('tasks-table-cell');
        if($isTime){
            $cell->setStyle(array(
                    'background-color'=>'rgba(100,255,29,0.3)'
                ));
            $cell->addTextNode('Yes');
        }
        else{
            $cell->setStyle(array(
                'background-color'=>'rgba(255,87,29,0.3)'
            ));
            $cell->addTextNode('No');
        }
        return $cell;
    }
}
