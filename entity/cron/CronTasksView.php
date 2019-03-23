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
use phpStructs\html\TabelCell;
use phpStructs\html\Input;
use phpStructs\html\HTMLNode;
use phpStructs\html\PNode;
use webfiori\entity\Util;
use phpStructs\html\Label;
use webfiori\entity\File;
use webfiori\WebFiori;
/**
 * A view to display information about CRON Jobs.
 * @version 1.0
 */
class CronTasksView {
    /**
     * Creates new instance of the view.
     */
    public function __construct() {
        $useTheme = isset($_GET['use-theme']) && $_GET['use-theme'] == 'yes' ? TRUE : FALSE;
        if($useTheme){
            Page::theme(WebFiori::getSiteConfig()->getBaseThemeName());    
        }
        Page::title('Scheduled CRON Tasks');
        Page::description('A list of available CRON jobs.');
        $tasksCount = Cron::jobsQueue()->size();
        $h1 = new HTMLNode('h1');
        $h1->addTextNode('Scheduled CRON Tasks');
        Page::insert($h1);
        $hr = new HTMLNode('hr',FALSE);
        Page::insert($hr);
        $parag = new PNode();
        $parag->addText('<b>Total Scheduled Tasks:</b> '.$tasksCount.'.', array('esc-entities'=>FALSE));
        Page::insert($parag);
        $this->_createRefreshControls();
        $this->_createThemeControls();
        $this->_createTasksTable();
        $this->_displayExecLog();
        $jsCode = new JsCode();
        if(isset($_GET['use-theme']) && $_GET['use-theme'] == 'yes'){
            $params = '?use-theme=yes&';
        }
        else{
            $params = '?';
        }
        if(isset($_GET['refresh'])){
            $refStr = 'window.location.reload(true);';
        }
        else{
            $refStr = 'window.location.href = \''.Util::getRequestedURL().$params.'refresh=yes\'';
        }
        $jsCode->addCode(''
                . 'window.onload = function(){'
                . 'window.setInterval(function(){'
                . 'var isRefresh = document.getElementById(\'refresh-checkbox\').checked;'
                . 'console.log(isRefresh);'
                . 'if(isRefresh){'
                . ''.$refStr.';'
                . '}'
                . '},5000)'
                . '}'
                . '');
        Page::document()->getHeadNode()->addChild($jsCode);
        Page::render();
    }
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
            if(strlen($file->getRawData()) != 0){
                $pre->addTextNode($file->getRawData());
            }
            else{
                $pre->addTextNode('No logs found.');
            }
        } 
        else{
            $pre->addTextNode('<b style="color:red">Log file not found.</b>',FALSE);
        }
    }
    private function _createThemeControls(){
        $form = new HTMLNode('form');
        $form->setID('theme-controls-form');
        $themeCheckBox = new Input('checkbox');
        $themeCheckBox->setID('theme-checkbox');
        if(isset($_GET['use-theme']) && $_GET['use-theme'] == 'yes'){
            $themeCheckBox->setAttribute('checked');
        }
        $form->addChild($themeCheckBox);
        $label = new Label('Use Website Theme to Display This Page.');
        $label->setAttribute('for', 'theme-checkbox');
        $form->addChild($label);
        if(isset($_GET['refresh']) && $_GET['refresh'] == 'yes'){
            $params = '&';
        }
        else{
            $params = '?';
        }
        $themeCheckBox->setAttribute('onclick', ''
                . 'if(this.checked){'
                . 'window.location.href = \''.Util::getRequestedURL().$params.'use-theme=yes\';'
                . '}'
                . 'else{'
                . 'window.location.href = \''.Util::getRequestedURL().$params.'use-theme=no\';'
                . '}'
                . '');
        Page::insert($form);
    }
    private function _createRefreshControls(){
        $form = new HTMLNode('form');
        $form->setID('refresh-controls-form');
        $refreshCheckBox = new Input('checkbox');
        $refreshCheckBox->setID('refresh-checkbox');
        if(isset($_GET['refresh']) && $_GET['refresh'] == 'yes'){
            $refreshCheckBox->setAttribute('checked');
        }
        $form->addChild($refreshCheckBox);
        $label = new Label('Refresh The Page Every 1 Minute.');
        $label->setAttribute('for', 'refresh-checkbox');
        $form->addChild($label);
        Page::insert($form);
    }

    private function _createTasksTable() {
        $tasksTable = new HTMLNode('table');
        $tasksTable->setID('tasks-table');
        Page::insert($tasksTable);
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
        $jobsQueue = Cron::jobsQueue();
        if($jobsQueue->size() == 0){
            $cell = new TabelCell();
            $cell->setColSpan(7);
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
                $jobNameCell = new TabelCell();
                $jobNameCell->setClassName('tasks-table-cell');
                $jobNameCell->addTextNode($job->getJobName());
                $row->addChild($jobNameCell);
                $exprCell = new TabelCell();
                $jobNameCell->setClassName('tasks-table-cell');
                $exprCell->addTextNode($job->getExpression());
                $row->addChild($exprCell);
                $row->addChild($this->_createTasksTableCell($job->isMinute()));
                $row->addChild($this->_createTasksTableCell($job->isHour()));
                $row->addChild($this->_createTasksTableCell($job->isDayOfMonth()));
                $row->addChild($this->_createTasksTableCell($job->isMonth()));
                $row->addChild($this->_createTasksTableCell($job->isDayOfWeek()));
                $tasksTable->addChild($row);
            }
        }
    }
    private function _createTasksTableHeaderCell($cellText) {
        $headerCell = new TabelCell('th');
        $headerCell->setClassName('tasks-table-header-cell');
        $headerCell->setStyle(array(
            'padding'=>'10px'
        ));
        $headerCell->addTextNode($cellText);
        return $headerCell;
    }
    private function _createTasksTableCell($isTime){
        $cell = new TabelCell();
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
