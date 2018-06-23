/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
function sendActivateReq(){
    var token = document.getElementById('token-input').value;
    var messageDisplay = document.getElementById('message');
    var params = {
        'activation-token':token,
        callbacks:{
            onsuccess:[
                function(){
                    messageDisplay.innerHTML = '<b style="color:green">'+window.messages['activated']+'</b>';
                    window.location.href = 'pages/home';
                }
            ],
            onclienterr:[
                function(){
                    messageDisplay.innerHTML = '<b style="color:red">'+window.messages['inv-tok']+'</b>';
                }
            ],
            onservererr:[
                function(){
                    messageDisplay.innerHTML = '<b style="color:red">'+window.messages['server-err']+'</b>';
                }
            ],
            ondisconnected:[
                function(){
                    messageDisplay.innerHTML = '<b style="color:red">'+window.messages['disconnected']+'</b>';
                }
            ]
        }
    };
    activateAccount(params);
    return false;
}
function tokInputChange(){
    if(this.value.length === 64){
        document.getElementById('activate-button').removeAttribute('disabled');
    }
    else{
        document.getElementById('activate-button').setAttribute('disabled','');
    }
}

function activateAccount(params={
    'activation-token':'',
    callbacks:{
        onsuccess:[],
        onclienterr:[],
        onservererr:[],
        ondisconnected:[]
    }
}){
    if(typeof params === 'object'){
        if(params['activation-token'] !== undefined && params['activation-token'] !== null && params['activation-token'].length !== 0){
            var ajax = new AJAX({
                method:'post',
                url:APIS.UserAPIs.link
            });
            var reqParams = 'action=activate-account&activation-token='+encodeURIComponent(params['activation-token']);
            ajax.setParams(reqParams);
            if(typeof params['callbacks'] === 'object'){
                var calls = params['callbacks'];
                if(Array.isArray(calls['onsuccess'])){
                    for(var x = 0 ; x < calls['onsuccess'].length ; x++){
                        var call = calls['onsuccess'][x];
                        if(typeof call === 'function'){
                            ajax.setOnSuccess(call);
                        }
                    }
                }

                if(Array.isArray(calls['onclienterr'])){
                    for(var x = 0 ; x < calls['onclienterr'].length ; x++){
                        var call = calls['onclienterr'][x];
                        if(typeof call === 'function'){
                            ajax.setOnClientError(call);
                        }
                    }
                }

                if(Array.isArray(calls['onservererr'])){
                    for(var x = 0 ; x < calls['onservererr'].length ; x++){
                        var call = calls['onservererr'][x];
                        if(typeof call === 'function'){
                            ajax.setOnServerError(call);
                        }
                    }
                }

                if(Array.isArray(calls['ondisconnected'])){
                    for(var x = 0 ; x < calls['ondisconnected'].length ; x++){
                        var call = calls['ondisconnected'][x];
                        if(typeof call === 'function'){
                            ajax.setOnDisconnected(call);
                        }
                    }
                }
            }
            ajax.send();
        }
        else{
            console.error('The parameter \'params[\'activation-token\']\' is undefined, null or empty string.');
        }
    }
    else{
        console.error('The given parameter is not an object.');
    }
}

