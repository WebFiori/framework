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
use webfiori\WebFiori;
use webfiori\entity\Page;
use webfiori\entity\cron\CronJob;
use phpStructs\html\JsCode;
use phpStructs\html\TableRow;
use phpStructs\html\TableCell;
use phpStructs\html\HTMLNode;
use phpStructs\html\PNode;
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
     * A top container that contains all task related controls.
     * @var HTMLNode 
     */
    private $controlsContainer;
    /**
     * @since 1.0
     */
    public function __construct() {
        if(WebFiori::getWebsiteController()->getSessionVar('cron-login-status') !== true){
            header('location: '.WebFiori::getSiteConfig()->getBaseURL().'cron/login');
        }
        $jobName = $_GET['job-name'];
        $password = Cron::password();
        $job = Cron::getJob($jobName);
        if($job instanceof CronJob){
            $this->controlsContainer = new HTMLNode();
            $this->controlsContainer->setWritingDir('ltr');
            $this->controlsContainer->setStyle([
                'direction'=>'ltr'
            ]);
            $this->job = $job;
            
            Page::title('View Task');
            Page::description('Display task info.');
            
            $defaltSiteLang = WebFiori::getSiteConfig()->getPrimaryLanguage();
            $siteNames = WebFiori::getSiteConfig()->getWebsiteNames();
            $siteName = isset($siteNames[$defaltSiteLang]) ? $siteNames[$defaltSiteLang] : null;
            if($siteName !== null){
                Page::siteName($siteName);
            }
            
            $h1 = new HTMLNode('h1');
            $h1->addTextNode('View Task Details');
            $h1->setStyle([
                'direction'=>'ltr'
            ]);
            $this->controlsContainer->addChild($h1);
            
            $hr = new HTMLNode('hr',false);
            
            $this->controlsContainer->addChild($hr);
            if(Cron::password() != 'NO_PASSWORD'){
                $this->controlsContainer->addTextNode('<button name="input-element" onclick="logout()"><b>Logout</b></button><br/>', false);
            }
            $backButton = new HTMLNode('button');
            $backButton->addTextNode('Back to Jobs List');
            $backButton->setAttribute('onclick',"window.location.href = 'cron/jobs';");
            $backButton->setStyle([
                'float'=>'left'
            ]);
            $backButton->setName('input-element');
            $this->controlsContainer->addChild($backButton);
            $this->controlsContainer->addTextNode('<br/>', false);
            $this->_createInfoTable($job);
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
            $isRefresh = 'false';
            if(isset($_GET['refresh'])){
                $isRefresh = 'true';
            }
            $jsCode = new JsCode();
            $jsCode->addCode(''
                    . 'window.onload = function(){'."\n"
                    . '     window.isRefresh = '.$isRefresh.';'
                    . '     window.customAttrsArr = '.$custAtrrsAsJsonArr.';'
                    . '     window.customAttrs = [];'
                    . '     for(var x = 0 ; x < window.customAttrsArr.length ; x++){'
                    . '         addAttribute(window.customAttrsArr[x]);'
                    . '     }'."\n"
                    . "     "
                    . '     window.intervalId = window.setInterval(function(){'."\n"
                    . '         if(window.isRefresh){'."\n"
                    . '             disableOrEnableInputs();'."\n"
                    . '             document.getElementById(\'refresh-label\').innerHTML = \'<b>Refreshing...</b>\';'."\n"
                    . '             window.location.href = \'cron/jobs?refresh=yes\';'."\n"
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
                    . '        var attrNameInput = "<input style=\'border:0px\' name=\'input-element\' type=\'text\' value=\'"+name+"\' oninput=\'attributeNameChanged(this,"+inputsIndex+")\'>";'."\n"
                    . '    }'
                    . '    else{'."\n"
                    . '        var attrNameInput = "<input style=\'border:0px\' name=\'input-element\' type=\'text\'  oninput=\'attributeNameChanged(this,"+inputsIndex+")\'>";'."\n"
                    . '    }'."\n"
                    . '    var attrValInput = "<input style=\'border:0px\' type=\'text\' name=\'input-element\' oninput=\'attributeValueChanged(this,"+inputsIndex+")\'>";'."\n"
                    . '    var newRow = document.createElement("tr");'."\n"
                    . '    newRow.id = "attribute-"+window.customAttrs.length+"-row";'."\n"
                    . '    newRow.className = "attribute-row";'."\n"
                    . '    newRow.innerHTML = "<td>"+attrNameInput+"</td><td>"+attrValInput+"</td><td style=\'padding:0px\'>"'."\n"
                    . '    +"<button name=\'input-element\' class=\'remove-attribute-button cancel\' onclick=\'removeAttr("+window.customAttrs.length+")\'>X</button></td>";'."\n"
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
                    . '        if(attr.value.length !== 0){'."\n"
                    . '            if(attr.use === true){'."\n"
                    . '                if(retVal.length === 0){'."\n"
                    . '                    retVal += attr.name+"="+encodeURIComponent(attr.value.trim());'."\n"
                    . '                }'."\n"
                    . '                else{'."\n"
                    . '                    retVal += "&"+attr.name+"="+encodeURIComponent(attr.value.trim());'."\n"
                    . '                }'."\n"
                    . '            }'."\n"
                    . '        }'."\n"
                    . '    }'."\n"
                    . '    if(retVal.length === 0){'."\n"
                    . '        return "";'."\n"
                    . '    }'."\n"
                    . '    return "&"+retVal;'."\n"
                    . '};'."\n"
                    . 'function execJob(source,jobName){'."\n"
                    . '     var refresh = window.isRefresh;'."\n"
                    . '     window.isRefresh = false;'."\n"
                    . '     disableOrEnableInputs();'
                    . '     source.innerHTML = \'Executing Job...\';'."\n"
                    . '     var outputWindow = document.getElementById(\'output-area\');'."\n"
                    . '     outputWindow.innerHTML = \'<b>Forcing job "\'+jobName+\'" to execute...</b>\n\';'."\n"
                    . '     var xhr = new XMLHttpRequest();'."\n"
                    . '     xhr.open(\'post\',\'cron/apis/force-execution\');'."\n"
                    . '     xhr.onreadystatechange = function(){'."\n"
                    . '         outputWindow.innerHTML += \'Ready State: \'+this.readyState+\'\n\';'."\n"
                    . '         if(this.readyState === 4 && this.status === 200){'."\n"
                    . '             try{'."\n"
                    . '                 var asJson = JSON.parse(this.responseText);'."\n"
                    . '                 if(asJson[\'more-info\'][\'failed\'].length != 0){'."\n"
                    . '                     outputWindow.innerHTML += \'<b style=\"color:red;font-weight:bold\">Job executed but did not finish successfully.</b> \n\';'."\n"
                    . '                     source.innerHTML = \'<b>The job was executed but did not finish successfully.</b>\';'."\n"
                    . '                 }'."\n"
                    . '                 else{'."\n"
                    . '                     outputWindow.innerHTML += \'<b style=\"color:green;font-weight:bold\">Job executed and finished successfully.</b> \n\';'."\n"
                    . '                     source.innerHTML = \'<b>Job executed and finished successfully</b>\';'."\n"
                    . '                 }'."\n"
                    . '             }'."\n"
                    . '             catch(e){'."\n"
                    . '                 outputWindow.innerHTML += \'<b style=\"color:red;font-weight:bold\">Job did not execute successfully due to server error.</b> \n\';'."\n"
                    . '                 source.innerHTML = \'Something Went Wrong While Executing the Job. Try Again\';'."\n"
                    . '             }'."\n"
                    . '             outputWindow.innerHTML += \'Raw server response:\n\'+this.responseText;'."\n"
                    . '             disableOrEnableInputs(false);'."\n"
                    . '             window.isRefresh = refresh;'."\n"
                    . '         }'."\n"
                    . '         else if(this.readyState === 4 && this.status === 0){'."\n"
                    . '             outputWindow.innerHTML += \'<b style=\"color:red;font-weight:bold\">Connection to the server was lost!</b> \n\';'."\n"
                    . '             source.innerHTML = \'<b>Connection Lost. Try Again.</b>\';'."\n"
                    . '             disableOrEnableInputs(false);'."\n"
                    . '             window.isRefresh = refresh;'."\n"
                    . '         }'."\n"
                    . '         else if(this.readyState === 4){'."\n"
                    . '             outputWindow.innerHTML += \'<b style=\"color:red;font-weight:bold\">Unknown error prevented job execution!</b> \n\';'."\n"
                    . '             outputWindow.innerHTML += \'Raw server response:\n\'+this.responseText;'."\n"
                    . '             source.innerHTML = \'Something Went Wrong While Executing the Job. Try Again\';'."\n"
                    . '             disableOrEnableInputs(false);'."\n"
                    . '             window.isRefresh = refresh;'."\n"
                    . '         }'."\n"
                    . '     }'."\n"
                    . "     xhr.setRequestHeader('content-type','application/x-www-form-urlencoded');"
                    . "     xhr.send('job-name='+encodeURIComponent(jobName)+extractCustomParams());"."\n"
                    . '};'."\n"
                    . 'function logout(){'."\n"
                    . '     var xhr = new XMLHttpRequest();'."\n"
                    . '     xhr.open(\'post\',\'cron/apis/logout\');'."\n"
                    . '     xhr.onreadystatechange = function(){'."\n"
                    . '         if(this.readyState === 4){'."\n"
                    . '             window.location.href = \'cron\';'
                    . '         }'."\n"
                    . '     }'."\n"
                    . "     xhr.setRequestHeader('content-type','application/x-www-form-urlencoded');"
                    . "     xhr.send();"."\n"
                    . '};'."\n"
                    . '');
            Page::document()->getHeadNode()->addChild($jsCode);
            $inlineStyle = new HTMLNode('style');
            $inlineStyle->addTextNode(''
                    . 'button{'
                    . 'cursor: pointer'
                    . '}'
                    . 'button.ok{'
                    . 'width: 100%;border: 0px;'
                    . 'background-color: #229000;'
                    . 'margin: 0;color: white;'
                    . 'font-weight: bold;'
                    . 'height: 25px;'
                    . '}'
                    . 'button.ok:hover{'
                    . 'background-color: #22B000;'
                    . '}'
                    . 'button.cancel:hover{'
                    . 'background-color: rgba(200,0,0,0.5);'
                    . '}'
                    . 'button[disabled]{'
                    . 'cursor:not-allowed'
                    . '}'
                    . 'button.cancel{'
                    . 'width: 100px;'
                    . 'margin: 0;'
                    . 'background-color: rgba(200,0,0,0.7);'
                    . 'color: white;'
                    . 'font-weight: bold;'
                    . 'border: 0px;'
                    . 'height:25px;'
                    . '}'
                    . '');
            Page::document()->getHeadNode()->addChild($inlineStyle);
            $forceNode = new HTMLNode();
            $forceNode->addTextNode('<button style="margin-top:30px" name="input-element" onclick="execJob(this,\''.$job->getJobName().'\')" class="force-execution-button">Force Execution</button>', false);
            $this->controlsContainer->addChild($forceNode);
            Page::insert($this->controlsContainer);
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
    
    private function _createCustomParamsContainer(){
        $h2 = new HTMLNode('h2');
        $h2->addTextNode('Custom Execution Parameters');
        $this->controlsContainer->addChild($h2);
        $p = new PNode();
        $p->addText('Here you can add extra parameters which will be sent with '
                . 'force execute command.');
        $this->controlsContainer->addChild($p);
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
        $addButtonCell->addTextNode('<button name="input-element" class="ok" style="width:100%" onclick="addAttribute()">Add Attribute</button>', false);
        $addRow->addChild($addButtonCell);
        $table->addChild($addRow);
        $this->controlsContainer->addChild($table);
    }

    /**
     * 
     * @param CronJob $job
     */
    private function _createInfoTable($job){
        $taskTable = new HTMLNode('table');
        $taskTable->setID('tasks-table');
        $this->controlsContainer->addChild($taskTable);
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
}