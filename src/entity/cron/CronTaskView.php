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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 404 Not Found");
    die('<!DOCTYPE html><html><head><title>Not Found</title></head><body>'
    . '<h1>404 - Not Found</h1><hr><p>The requested resource was not found on the server.</p></body></html>');
}
use webfiori\entity\Page;
use webfiori\entity\cron\CronJob;
use phpStructs\html\JsCode;
use phpStructs\html\TableRow;
use phpStructs\html\TableCell;
use phpStructs\html\Input;
use phpStructs\html\HTMLNode;
use phpStructs\html\PNode;
use phpStructs\html\Label;
use webfiori\entity\File;
use webfiori\WebFiori;
/**
 * A view to show details of a specific CRON task.
 *
 * @author Ibrahim
 * @version 1.0
 */
class CronTaskView {
    /**
     * The job that the view will display its info.
     * @var CronJob 
     * @since 1.0
     */
    private $job;
    /**
     * The URL of this view which is constructed when the method _getPageURL() is 
     * called.
     * @var string 
     * @since 1.0
     */
    private $pageUrl;
    /**
     * @since 1.0
     */
    public function __construct() {
        $jobName = $_GET['job-name'];
        $password = Cron::password();
        $job = Cron::getJob($jobName);
        if($job instanceof CronJob){
            $this->job = $job;
            $useTheme = isset($_GET['use-theme']) && $_GET['use-theme'] == 'yes' ? true : false;
            if($useTheme){
                Page::theme(WebFiori::getSiteConfig()->getBaseThemeName());    
            }
            Page::title('View Task');
            Page::description('Display task info.');
            $h1 = new HTMLNode('h1');
            $h1->addTextNode('View Task Details');
            Page::insert($h1);
            $hr = new HTMLNode('hr',false);
            
            Page::insert($hr);
            if($password != 'NO_PASSWORD'){
                $forceUrl = WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/execute/'.$password.'/force/';
            }
            else{
                $forceUrl = WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/execute/force/';
            }
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
                $refStr = 'window.location.href = \''.$this->_getPageURL().'\'+encodeURIComponent("'.$this->job->getJobName().'")+\''.$params.'\'';
            }
            
            //$this->_createRefreshControls();
            //$this->_createThemeControls();
            $this->_createInfoTable($job);
            $forceNode = new HTMLNode();
            $forceNode->addTextNode('<button name="input-element" onclick="execJob(this,\''.$job->getJobName().'\')" class="force-execution-button">Force Execution</button>', false);
            Page::insert($forceNode);
            $this->_createCustomParamsContainer();
            $custAttrsNames = $job->getExecutionAttributes();
            $custAtrrsAsJsonArr = '[';
            for($x = 0 ; $x < count($custAttrsNames) ; $x++){
                if($x == 0){
                    $custAtrrsAsJsonArr .= '"'.$custAttrsNames[$x].'"';
                }
                else{
                    $custAtrrsAsJsonArr .= ',"'.$custAttrsNames[$x].'"';
                }
            }
            $custAtrrsAsJsonArr .= ']';
            $jsCode = new JsCode();
            $jsCode->addCode(''
                    . 'window.onload = function(){'."\n"
                    . '     window.customAttrs = [];'."\n"
                    . '     window.custAttrsNames = '.$custAtrrsAsJsonArr.';'
                    . '     for(var x = 0 ; x < window.custAttrsNames.length ; x++){'."\n"
                    . '         addAttribute(window.custAttrsNames[x]);'."\n"
                    . '     }'."\n"
                    . '     window.isRefresh = '.$isRefresh.';'."\n"
                    . '     window.intervalId = window.setInterval(function(){'."\n"
                    . '         if(window.isRefresh){'."\n"
                    . '             disableOrEnableInputs();'."\n"
                    . '             document.getElementById(\'refresh-label\').innerHTML = \'<b>Refreshing...</b>\';'."\n"
                    . '             '.$refStr.';'."\n"
                    . '         }'."\n"
                    . '     },60000)'."\n"
                    . ' };'."\n"
                    . 'function disableOrEnableInputs(disable=true){'."\n"
                    . '    var inputEls = document.getElementsByName(\'input-element\');'."\n"
                    . '    if(disable){'."\n"
                    . '        for(var x = 0 ; x < inputEls.length ; x++){'."\n"
                    . '            inputEls[x].setAttribute(\'disabled\',\'\');'."\n"
                    . '        }'."\n"
                    . '    }'."\n"
                    . '    else{'."\n"
                    . '        for(var x = 0 ; x < inputEls.length ; x++){'."\n"
                    . '            inputEls[x].removeAttribute(\'disabled\');'."\n"
                    . '        }'."\n"
                    . '    }'."\n"
                    . '}'."\n"
                    . 'function addAttribute(name=undefined){'."\n"
                    . '    var attrsCount = 0;'."\n"
                    . '    for(var x = 0 ; x < window.customAttrs.length ; x++){'."\n"
                    . '        if(window.customAttrs[x].use === true){'."\n"
                    . '            attrsCount++'."\n"
                    . '        }'."\n"
                    . '    }'."\n"
                    . '    var attrsTable = document.getElementById(\'custom-params-table\').children[0];'."\n"
                    . '    var addBtnRow = attrsTable.children[attrsTable.children.length - 1];'."\n"
                    . '    attrsTable.removeChild(addBtnRow);'."\n"
                    . '    if(attrsCount === 0){'."\n"
                    . '        window.noAttrsNode = attrsTable.children[attrsTable.children.length - 1];'."\n"
                    . '        attrsTable.removeChild(window.noAttrsNode);'."\n"
                    . '    }'."\n"
                    . '    var inputsIndex = window.customAttrs.length;'."\n"
                    . '    if(name !== undefined){'."\n"
                    . '        var attrNameInput = "<input name=\'input-element\' type=\'text\' value=\'"+name+"\' oninput=\'attributeNameChanged(this,"+inputsIndex+")\'>";'."\n"
                    . '    }'
                    . '    else{'."\n"
                    . '        var attrNameInput = "<input name=\'input-element\' type=\'text\'  oninput=\'attributeNameChanged(this,"+inputsIndex+")\'>";'."\n"
                    . '    }'."\n"
                    . '    var attrValInput = "<input type=\'text\'  oninput=\'attributeValueChanged(this,"+inputsIndex+")\'>";'."\n"
                    . '    var newRow = document.createElement("tr");'."\n"
                    . '    newRow.id = "attribute-"+window.customAttrs.length+"-row";'."\n"
                    . '    newRow.className = "attribute-row";'."\n"
                    . '    newRow.innerHTML = "<td>"+attrNameInput+"</td><td>"+attrValInput+"</td><td>"'."\n"
                    . '    +"<button name=\'input-element\' class=\'remove-attribute-button\' onclick=\'removeAttr("+window.customAttrs.length+")\'>X</button></td>";'."\n"
                    . '    attrsTable.appendChild(newRow);'."\n"
                    . '    attrsTable.appendChild(addBtnRow);'."\n"
                    . '    window.customAttrs.push({name:name,value:"",use:true});'."\n"
                    . '    '."\n"
                    . '    '."\n"
                    . '}'."\n"
                    . 'function removeAttr(num){'."\n"
                    . '    window.customAttrs[num]["use"] = false;'."\n"
                    . '    var row = document.getElementById("attribute-"+num+"-row");'."\n"
                    . '    var table = document.getElementById(\'custom-params-table\').children[0];'."\n"
                    . '    table.removeChild(row);'."\n"
                    . '    var attrsCount = 0;'."\n"
                    . '    for(var x = 0 ; x < window.customAttrs.length ; x++){'."\n"
                    . '        if(window.customAttrs[x].use === true){'."\n"
                    . '            attrsCount++'."\n"
                    . '        }'."\n"
                    . '    }'."\n"
                    . '    if(attrsCount === 0){'."\n"
                    . '        var addBtnRow = table.children[table.children.length - 1];'."\n"
                    . '        table.removeChild(addBtnRow);'."\n"
                    . '        table.appendChild(window.noAttrsNode);'."\n"
                    . '        table.appendChild(addBtnRow);'."\n"
                    . '    }'."\n"
                    . '}'."\n"
                    . 'function attributeValueChanged(source,num){'."\n"
                    . '    window.customAttrs[num].value = source.value;'."\n"
                    . '};'."\n"
                    . 'function attributeNameChanged(source,num){'."\n"
                    . '    window.customAttrs[num].name = source.value;'."\n"
                    . '};'."\n"
                    . 'function extractCustomParams(){'."\n"
                    . '    var retVal = "";'."\n"
                    . '    for(var x = 0 ; x < window.customAttrs.length ; x++){'."\n"
                    . '        var attr = window.customAttrs[x];'."\n"
                    . '        if(attr.use === true){'."\n"
                    . '            if(retVal.length === 0){'."\n"
                    . '                retVal += attr.name+"="+encodeURIComponent(attr.value);'."\n"
                    . '            }'."\n"
                    . '            else{'."\n"
                    . '                retVal += "&"+attr.name+"="+encodeURIComponent(attr.value);'."\n"
                    . '            }'."\n"
                    . '        }'."\n"
                    . '    }'."\n"
                    . '    if(retVal.length === 0){'."\n"
                    . '        return "";'."\n"
                    . '    }'."\n"
                    . '    return "?"+retVal;'."\n"
                    . '};'."\n"
                    . 'function execJob(source,jobName){'."\n"
                    . '     var refresh = window.isRefresh;'."\n"
                    . '     window.isRefresh = false;'."\n"
                    . '     disableOrEnableInputs();'
                    . '     source.innerHTML = \'Executing Job...\';'."\n"
                    . '     var xhr = new XMLHttpRequest();'."\n"
                    . '     xhr.open(\'get\',\''.$forceUrl.'\'+encodeURIComponent(jobName)+extractCustomParams());'."\n"
                    . '     xhr.onreadystatechange = function(){'."\n"
                    . '         if(this.readyState === 4 && this.status === 200){'."\n"
                    . '             source.innerHTML = \'<b>Job Executed Successfully</b>\';'."\n"
                    . '             disableOrEnableInputs(false);'."\n"
                    . '             window.isRefresh = refresh;'."\n"
                    . '         }'."\n"
                    . '         else if(this.readyState === 4 && this.status === 0){'."\n"
                    . '             source.innerHTML = \'<b>Connection Lost. Try Again.</b>\';'."\n"
                    . '             disableOrEnableInputs(false);'."\n"
                    . '             window.isRefresh = refresh;'."\n"
                    . '         }'."\n"
                    . '         else{'."\n"
                    . '             source.innerHTML = \'Something Went Wrong While Executing the Job. Try Again\';'."\n"
                    . '             disableOrEnableInputs(false);'."\n"
                    . '             window.isRefresh = refresh;'."\n"
                    . '         }'."\n"
                    . '     }'."\n"
                    . '     xhr.send();'."\n"
                    . '};'."\n"
                    . '');
            Page::document()->getHeadNode()->addChild($jsCode);
            Page::render();
        }
        else{
            if($password != 'NO_PASSWORD'){
                header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/list/'.$password);
            }
            else{
                header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/list');
            }
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
        $label = new Label('Refresh The Page Every 1 Minute.');
        $label->setAttribute('for', 'refresh-checkbox');
        $label->setID('refresh-label');
        $form->addChild($label);
        Page::insert($form);
    }
    /**
     * Creates a form that contains the controls which is used to enable the 
     * use of website theme while displaying the view.
     */
    private function _createThemeControls(){
        $form = new HTMLNode('form');
        $form->setID('theme-controls-form');
        $themeCheckBox = new Input('checkbox');
        $themeCheckBox->setName('input-element');
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
        $onclick = 'disableOrEnableInputs();'
                . 'var isRef = window.isRefresh;'
                . 'window.isRefresh = false;'
                . 'document.getElementById(\'change-theme-label\').innerHTML = \'<b>Updating Page...</b>\';';
        if(isset($_GET['use-theme']) && $_GET['use-theme'] = 'yes'){
            $onclick .= 
                'if(this.checked === false && isRef){'
                . 'window.location.href = \''.$this->_getPageURL().'+encodeURIComponent("'.$this->job->getJobName().'")+'.$params.'\';'
                . '}'
                . 'else if(this.checked === false && !isRef){'
                . 'window.location.href = \''.$this->_getPageURL().'+encodeURIComponent("'.$this->job->getJobName().'")+'.'\';'
                . '}';
            $themeCheckBox->setAttribute('onclick', $onclick);
        }
        else{
            $onclick .= 
                'if(this.checked && isRef){'
                . 'window.location.href = \''.$this->_getPageURL().'+encodeURIComponent("'.$this->job->getJobName().'")+'.$params.'&use-theme=yes\';'
                . '}'
                . 'else{'
                . 'window.location.href = \''.$this->_getPageURL().'+encodeURIComponent("'.$this->job->getJobName().'")+'.'?use-theme=yes\';'
                . '}';
            $themeCheckBox->setAttribute('onclick', $onclick);
        }
        
        Page::insert($form);
    }
    private function _createCustomParamsContainer(){
        $h2 = new HTMLNode('h2');
        $h2->addTextNode('Custom Execution Parameters');
        Page::insert($h2);
        $p = new PNode();
        $p->addText('Here you can add extra parameters which will be sent with '
                . 'th force execute command.');
        Page::insert($p);
        $table = new HTMLNode('table');
        $table->setID('custom-params-table');
        $table->setAttribute('border', 1);
        $table->setStyle(array(
            'border-collapse'=>'collapse',
            'margin-top'=>'30px'
        ));
        $headerRow = new TableRow();
        $headerRow->setStyle([
            'border-bottom'=>'double',
            'background-color'=>'rgba(66,234,88,0.3)',
            'font-weight'=>'bold'
        ]);
        $headerRow->setClassName('tasks-table-header-row');
        $nameCell = new TableCell();
        $nameCell->setClassName('tasks-table-header-cell');
        $nameCell->setStyle([
            'padding'=>'10px'
        ]);
        $nameCell->addTextNode('Attribute Name');
        $headerRow->addChild($nameCell);
        
        $valueCell = new TableCell();
        $valueCell->setClassName('tasks-table-header-cell');
        $valueCell->setStyle([
            'padding'=>'10px'
        ]);
        $valueCell->addTextNode('Attribute Value');
        $headerRow->addChild($valueCell);
        
        $removeCell = new TableCell();
        $removeCell->setClassName('tasks-table-header-cell');
        $removeCell->setStyle([
            'padding'=>'10px'
        ]);
        $removeCell->addTextNode('Remove');
        $headerRow->addChild($removeCell);
        
        $table->addChild($headerRow);
        $noDataRow = new TableRow();
        $noDataRow->setID('no-attributes-row');
        $noDataCell = new TableCell();
        $noDataCell->setStyle(array(
            'background-color'=>'lightgray',
            'text-align'=>'center',
            'font-weight'=>'bold'
        ));
        $noDataCell->setColSpan(3);
        $noDataCell->addTextNode('No Attributes');
        $noDataRow->addChild($noDataCell);
        $table->addChild($noDataRow);
        $addRow = new TableRow();
        $addRow->setID('add-attr-button-row');
        $addButtonCell = new TableCell();
        $addButtonCell->setColSpan(3);
        $addButtonCell->setID('add-attr-button-cell');
        $addButtonCell->addTextNode('<button style="width:100%" onclick="addAttribute()">Add Attribute</button>', false);
        $addRow->addChild($addButtonCell);
        $table->addChild($addRow);
        Page::insert($table);
    }

    /**
     * 
     * @param CronJob $job
     */
    private function _createInfoTable($job){
        $taskTable = new HTMLNode('table');
        $taskTable->setID('tasks-table');
        Page::insert($taskTable);
        $taskTable->setAttribute('border', 1);
        $taskTable->setStyle(array(
            'border-collapse'=>'collapse',
            'margin-top'=>'30px'
        ));
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
    /**
     * Constructs and returns the URL of the page.
     * This method will include the password that is used to protect 
     * Cron jobs if it was set.
     * @return string
     */
    private function _getPageURL() {
        if($this->pageUrl !== null){
            return $this->pageUrl;
        }
        $url = WebFiori::getSiteConfig()->getBaseURL().'cron-jobs/view-job/';
        $pass = Cron::password();
        if($pass != 'NO_PASSWORD'){
            $url .= '/'.$pass.'';
        }
        $this->pageUrl = $url;
        return $url;
    }
}