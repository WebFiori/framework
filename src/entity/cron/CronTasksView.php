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
use phpStructs\html\Label;
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
class CronTasksView {
    /**
     * The URL of this view which is constructed when the method _getPageURL() is 
     * called.
     * @var string 
     */
    private $pageUrl;
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
        $isRefresh = 'false';
        
        if(isset($_GET['use-theme']) && 
          $_GET['use-theme'] == 'yes' && 
            isset($_GET['refresh']) && 
            $_GET['refresh'] == 'yes'){
            $params = '?use-theme=yes&refresh=yes';
            $isRefresh = 'true';
        }
        else if(isset($_GET['use-theme']) && 
          $_GET['use-theme'] == 'yes'){
            $params = '?use-theme=yes&refresh=yes';
        }
        else{
            $params = '?refresh=yes';
        }
        if(isset($_GET['refresh'])){
            $refStr = 'window.location.reload(true);';
            $isRefresh = 'true';
        }
        else{
            $refStr = 'window.location.href = \''.$this->_getPageURL().$params.'\'';
        }
        $password = Cron::password();
        if($password != 'NO_PASSWORD'){
            $forceUrl = WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/execute/'.$password.'/force/';
        }
        else{
            $forceUrl = WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/execute/force/';
        }
        $jsCode->addCode(''
                . 'window.onload = function(){'."\n"
                . '     window.isRefresh = '.$isRefresh.';'."\n"
                . '     window.intervalId = window.setInterval(function(){'."\n"
                . '         if(window.isRefresh){'."\n"
                . '             disableExeButtons();'."\n"
                . '             document.getElementById(\'refresh-checkbox\').setAttribute(\'disabled\',\'\');'."\n"
                . '             document.getElementById(\'theme-checkbox\').setAttribute(\'disabled\',\'\');'."\n"
                . '             document.getElementById(\'refresh-label\').innerHTML = \'<b>Refreshing...</b>\';'."\n"
                . '             '.$refStr.';'."\n"
                . '         }'."\n"
                . '     },60000)'."\n"
                . ' };'."\n"
                . 'function disableExeButtons(){'."\n"
                . '    var buttons = document.getElementsByName(\'execute-button\');'."\n"
                . '    for(var x = 0 ; x < buttons.length ; x++){'."\n"
                . '        buttons[x].setAttribute(\'disabled\',\'\');'."\n"
                . '    }'."\n"
                . '}'."\n"
                . 'function execJob(source,jobName){'."\n"
                . '     var refresh = window.isRefresh;'."\n"
                . '     window.isRefresh = false;'."\n"
                . '     document.getElementById(\'refresh-checkbox\').setAttribute(\'disabled\',\'\');'."\n"
                . '     document.getElementById(\'theme-checkbox\').setAttribute(\'disabled\',\'\');'."\n"
                . '     source.setAttribute(\'disabled\',\'\');'."\n"
                . '     source.innerHTML = \'Executing Job...\';'."\n"
                . '     var xhr = new XMLHttpRequest();'."\n"
                . '     xhr.open(\'get\',\''.$forceUrl.'\'+jobName);'."\n"
                . '     xhr.onreadystatechange = function(){'."\n"
                . '         if(this.readyState === 4 && this.status === 200){'."\n"
                . '             source.innerHTML = \'<b>Job Executed Successfully</b>\';'."\n"
                . '             document.getElementById(\'refresh-checkbox\').removeAttribute(\'disabled\');'."\n"
                . '             document.getElementById(\'theme-checkbox\').removeAttribute(\'disabled\');'."\n"
                . '             window.isRefresh = refresh;'."\n"
                . '         }'."\n"
                . '         else if(this.readyState === 4 && this.status === 0){'."\n"
                . '             source.innerHTML = \'<b>Connection Lost. Try Again.</b>\';'."\n"
                . '             source.removeAttribute(\'disabled\');'."\n"
                . '             document.getElementById(\'refresh-checkbox\').removeAttribute(\'disabled\');'."\n"
                . '             document.getElementById(\'theme-checkbox\').removeAttribute(\'disabled\');'."\n"
                . '             window.isRefresh = refresh;'."\n"
                . '         }'."\n"
                . '         else{'."\n"
                . '             source.innerHTML = \'Something Went Wrong While Executing the Job. Try Again\';'."\n"
                . '             source.removeAttribute(\'disabled\');'."\n"
                . '             document.getElementById(\'refresh-checkbox\').removeAttribute(\'disabled\');'."\n"
                . '             document.getElementById(\'theme-checkbox\').removeAttribute(\'disabled\');'."\n"
                . '             window.isRefresh = refresh;'."\n"
                . '         }'."\n"
                . '     }'."\n"
                . '     xhr.send();'."\n"
                . '};'."\n"
                . '');
        Page::document()->getHeadNode()->addChild($jsCode);
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
            $pre->addTextNode('<b style="color:red">Log file not found.</b>',FALSE);
        }
    }
    /**
     * Constructs and returns the URL of the page.
     * This method will include the password that is used to protect 
     * Cron jobs if it was set.
     * @return string
     */
    private function _getPageURL() {
        if($this->pageUrl !== NULL){
            return $this->pageUrl;
        }
        $url = WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/list';
        $pass = Cron::password();
        if($pass != 'NO_PASSWORD'){
            $url .= '/'.$pass.'';
        }
        $this->pageUrl = $url;
        return $url;
    }
    /**
     * Creates a form that contains the controls which is used to enable the 
     * use of website theme while displaying the view.
     */
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
        $label->setID('change-theme-label');
        $form->addChild($label);
        if(isset($_GET['refresh']) && $_GET['refresh'] == 'yes'){
            $params = '?refresh=yes';
        }
        else{
            $params = '';
        }
        $onclick = 'disableExeButtons();'
                . 'var isRef = window.isRefresh;'
                    . 'window.isRefresh = false;'
                    . 'document.getElementById(\'refresh-checkbox\').setAttribute(\'disabled\',\'\');'
                . 'document.getElementById(\'theme-checkbox\').setAttribute(\'disabled\',\'\');'
                . 'document.getElementById(\'change-theme-label\').innerHTML = \'<b>Updating Page...</b>\';';
        if(isset($_GET['use-theme']) && $_GET['use-theme'] = 'yes'){
            $onclick .= 
                'if(this.checked === false && isRef){'
                . 'window.location.href = \''.$this->_getPageURL().$params.'\';'
                . '}'
                . 'else if(this.checked === false && !isRef){'
                . 'window.location.href = \''.$this->_getPageURL().'\';'
                . '}';
            $themeCheckBox->setAttribute('onclick', $onclick);
        }
        else{
            $onclick .= 
                'if(this.checked && isRef){'
                . 'window.location.href = \''.$this->_getPageURL().$params.'&use-theme=yes\';'
                . '}'
                . 'else{'
                . 'window.location.href = \''.$this->_getPageURL().'?use-theme=yes\';'
                . '}';
            $themeCheckBox->setAttribute('onclick', $onclick);
        }
        
        Page::insert($form);
    }
    /**
     * Creates a form which contains the controls that allow the user to 
     * enable refresh functionality.
     */
    private function _createRefreshControls(){
        $form = new HTMLNode('form');
        $form->setID('refresh-controls-form');
        $refreshCheckBox = new Input('checkbox');
        $refreshCheckBox->setID('refresh-checkbox');
        $refreshCheckBox->setAttribute('onclick', 'window.isRefresh = this.checked;');
        if(isset($_GET['refresh']) && $_GET['refresh'] == 'yes'){
            $refreshCheckBox->setAttribute('checked');
        }
        $form->addChild($refreshCheckBox);
        $label = new Label('Refresh The Page Every 1 Minute.');
        $label->setAttribute('for', 'refresh-checkbox');
        $label->setID('refresh-label');
        $form->addChild($label);
        Page::insert($form);
    }
    /**
     * Creates the table that is used to display cron jobs information.
     */
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
        $tableHeader->addChild($this->_createTasksTableHeaderCell('Execute'));
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
                $forceCell = new TabelCell();
                $forceCell->addTextNode('<button name="execute-button" onclick="execJob(this,\''.$job->getJobName().'\')" class="force-execution-button">Force Execution</button>', FALSE);
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
     * @return TabelCell
     */
    private function _createTasksTableHeaderCell($cellText) {
        $headerCell = new TabelCell('th');
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
     * @return TabelCell
     */
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
