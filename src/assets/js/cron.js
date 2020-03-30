/**
 * This function is used to enable or disable input controls in the page.
 * @param {Boolean} disable Pass true to disable input elements. False to 
 * enable them.
 * @returns {undefined}
 */
function disableOrEnableInputs(disable=true){
    var inputEls = document.getElementsByName('input-element');
    if(disable){
        for(var x = 0 ; x < inputEls.length ; x++){
            inputEls[x].setAttribute('disabled','');
        }
    }
    else{
        for(var x = 0 ; x < inputEls.length ; x++){
            inputEls[x].removeAttribute('disabled');
        }
    }
}
/**
 * Force a job to execute.
 * @param {type} source The button which is used to send the force action.
 * @param {type} jobName The name of the job that will be forced to execute.
 * @returns {undefined}
 */
function execJob(source,jobName){
     var refresh = window.isRefresh;
     window.isRefresh = false;
     disableOrEnableInputs();     
     source.innerHTML = 'Executing Job...';
     var xhr = new XMLHttpRequest();
     xhr.open('post','cron/apis/force-execution');
     var outputWindow = document.getElementById('output-area');
     outputWindow.innerHTML = '<b>Forcing job "'+jobName+'" to execute...</b>\n';
     xhr.onreadystatechange = function(){
         outputWindow.innerHTML += 'Ready State: '+this.readyState+'\n';
         if(this.readyState === 4 && this.status === 200){
             try{
                 var asJson = JSON.parse(this.responseText);
                 if(asJson['more-info']['failed'].length !== 0){
                     outputWindow.innerHTML += '<b style=\"color:red;font-weight:bold\">Job executed but did not finish successfully.</b> \n';
                     source.innerHTML = '<b>The job was executed but did not finish successfully.</b>';
                 }
                 else{
                     outputWindow.innerHTML += '<b style=\"color:green;font-weight:bold\">Job executed and finished successfully.</b> \n';
                     source.innerHTML = '<b>Job executed and finished successfully</b>';
                 }
             }
             catch(e){
                 outputWindow.innerHTML += '<b style=\"color:red;font-weight:bold\">Job did not execute successfully due to server error.</b> \n';
                 source.innerHTML = 'Something Went Wrong While Executing the Job. Try Again';
             }
             outputWindow.innerHTML += 'Raw server response:\n'+this.responseText;
             disableOrEnableInputs(false);
             window.isRefresh = refresh;
         }
         else if(this.readyState === 4 && this.status === 0){
             outputWindow.innerHTML += '<b style=\"color:red;font-weight:bold\">Connection to the server was lost!</b> \n';
             source.innerHTML = '<b>Connection Lost. Try Again.</b>';
             disableOrEnableInputs(false);
             window.isRefresh = refresh;
         }
         else if(this.readyState === 4){
             outputWindow.innerHTML += '<b style=\"color:red;font-weight:bold\">Unknown error prevented job execution!</b> \n';
             outputWindow.innerHTML += 'Raw server response:\n'+this.responseText;
             source.innerHTML = 'Something Went Wrong While Executing the Job. Try Again';
             disableOrEnableInputs(false);
             window.isRefresh = refresh;
         }
     };
     xhr.setRequestHeader('content-type','application/x-www-form-urlencoded');     xhr.send('job-name='+encodeURIComponent(jobName));
};
/**
 * Logout of CRON control panel.
 * @returns {undefined}
 */
function logout(){
     var xhr = new XMLHttpRequest();
     xhr.open('post','cron/apis/logout');
     xhr.onreadystatechange = function(){
         if(this.readyState === 4){
             window.location.href = 'cron';         
         }
     };
     xhr.setRequestHeader('content-type','application/x-www-form-urlencoded');     xhr.send();
};
/**
 * This function is called when adding new execution parameter to the job.
 * @param {type} name The name of the new parameter.
 * @returns {undefined}
 */
function addAttribute(name=undefined){
    var attrsCount = 0;
    for(var x = 0 ; x < window.customAttrs.length ; x++){
        if(window.customAttrs[x].use === true){
            attrsCount++;
        }
    }
    var attrsTable = document.getElementById('custom-params-table').children[0];
    var addBtnRow = attrsTable.children[attrsTable.children.length - 1];
    attrsTable.removeChild(addBtnRow);
    if(attrsCount === 0){
        window.noAttrsNode = attrsTable.children[attrsTable.children.length - 1];
        attrsTable.removeChild(window.noAttrsNode);
    }
    var inputsIndex = window.customAttrs.length;
    if(name !== undefined){
        var attrNameInput = "<input style='border:0px' name='input-element' type='text' value='"+name+"' oninput='attributeNameChanged(this,"+inputsIndex+")'>";
    }    else{
        var attrNameInput = "<input style='border:0px' name='input-element' type='text'  oninput='attributeNameChanged(this,"+inputsIndex+")'>";
    }
    var attrValInput = "<input style='border:0px' type='text' name='input-element' oninput='attributeValueChanged(this,"+inputsIndex+")'>";
    var newRow = document.createElement("tr");
    newRow.id = "attribute-"+window.customAttrs.length+"-row";
    newRow.className = "attribute-row";
    newRow.innerHTML = "<td>"+attrNameInput+"</td><td>"+attrValInput+"</td><td style='padding:0px'>"
    +"<button name='input-element' class='remove-attribute-button cancel' onclick='removeAttr("+window.customAttrs.length+")'>X</button></td>";
    attrsTable.appendChild(newRow);
    attrsTable.appendChild(addBtnRow);
    window.customAttrs.push({name:name,value:"",use:true});
    
    
}
/**
 * A function which is called when the delete attribute button is clicked.
 * @param {type} num The number or the index of the parameter that will be 
 * removed.
 * @returns {undefined}
 */
function removeAttr(num){
    window.customAttrs[num]["use"] = false;
    var row = document.getElementById("attribute-"+num+"-row");
    var table = document.getElementById('custom-params-table').children[0];
    table.removeChild(row);
    var attrsCount = 0;
    for(var x = 0 ; x < window.customAttrs.length ; x++){
        if(window.customAttrs[x].use === true){
            attrsCount++;
        }
    }
    if(attrsCount === 0){
        var addBtnRow = table.children[table.children.length - 1];
        table.removeChild(addBtnRow);
        table.appendChild(window.noAttrsNode);
        table.appendChild(addBtnRow);
    }
}
function attributeValueChanged(source,num){
    window.customAttrs[num].value = source.value;
};
function attributeNameChanged(source,num){
    window.customAttrs[num].name = source.value;
};
function extractCustomParams(){
    var retVal = "";
    for(var x = 0 ; x < window.customAttrs.length ; x++){
        var attr = window.customAttrs[x];
        if(attr.value.length !== 0){
            if(attr.use === true){
                if(retVal.length === 0){
                    retVal += attr.name+"="+encodeURIComponent(attr.value.trim());
                }
                else{
                    retVal += "&"+attr.name+"="+encodeURIComponent(attr.value.trim());
                }
            }
        }
    }
    if(retVal.length === 0){
        return "";
    }
    return "&"+retVal;
};
