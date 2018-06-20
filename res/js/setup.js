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
function checkMailParams(){
    var ajax = new AJAX({
        method:'post',
        url:'apis/SysAPIs'
    });
    var form = new FormData();
    form.append('action','update-send-email-account');
    form.append('server-address',window.mailAttrs['server-address']);
    form.append('server-port',window.mailAttrs['port']);
    form.append('email-address',window.mailAttrs['address']);
    form.append('username',window.mailAttrs['username']);
    form.append('password',window.mailAttrs['password']);
    form.append('name',window.mailAttrs['name']);
    var messageDisplay = document.getElementById('message-display');
    var submitButton = document.getElementById('check-input');
    submitButton.setAttribute('disabled','');
    messageDisplay.innerHTML = '<b>'+window.messages['checking-connection']+'</b>';
    ajax.setParams(form);
    ajax.setOnSuccess(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:green">'+window.messages['success']+'</b>';
        document.getElementById('next-button').removeAttribute('disabled');
    });
    ajax.setOnClientError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages[this.jsonResponse['message']]+'</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.setOnServerError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">Server Error (500)</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.setOnDisconnected(function(){
        console.log('Disconnected!');
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['disconnected']+'</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.send();
    return false;
}

function runSetup(){
    var ajax = new AJAX({
        method:'post',
        url:'apis/SysAPIs'
    });
    var form = new FormData();
    form.append('action','create-first-account');
    form.append('username',window.account['username']);
    form.append('password',window.account['password']);
    form.append('email',window.account['email']);
    var messageDisplay = document.getElementById('message-display');
    var submitButton = document.getElementById('check-input');
    submitButton.setAttribute('disabled','');
    var prevButton = document.getElementById('prev-button');
    prevButton.setAttribute('disabled','');
    messageDisplay.innerHTML = '<b>'+window.messages['creating-account']+'</b>';
    ajax.setParams(form);
    ajax.setOnSuccess(function(){
        document.getElementById('next-button').removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:green">'+window.messages['account-created']+'</b>';
    });
    ajax.setOnClientError(function(){
        console.log(this.response);
        var msg = this.jsonResponse['message'];
        if(msg === 'The following parameter(s) has invalid values: \'email\'.'){
            messageDisplay.innerHTML = '<b style="color:red">'+window.messages['inv-email']+'</b>';
        }
        else if(msg === 'query_error'){
            messageDisplay.innerHTML = '<b style="color:red">query_error: '+this.jsonResponse['details']['error-message']+'</b>';
        }
        else{
            messageDisplay.innerHTML = '<b style="color:red">Something Went Wrong!</b>';
        }
        submitButton.removeAttribute('disabled');
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.setOnServerError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">Server Error (500)</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.setOnDisconnected(function(){
        console.log('Disconnected!');
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['disconnected']+'</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.send();
    return false;
}

function checkConectionParams(){
    var ajax = new AJAX({
        method:'post',
        url:'apis/SysAPIs'
    });
    var form = new FormData();
    form.append('action','update-database-attributes');
    form.append('host',window.dbAttrs['host']);
    form.append('database-username',window.dbAttrs['username']);
    form.append('database-password',window.dbAttrs['password']);
    form.append('database-name',window.dbAttrs['dbName']);
    var messageDisplay = document.getElementById('message-display');
    var submitButton = document.getElementById('check-input');
    submitButton.setAttribute('disabled','');
    messageDisplay.innerHTML = '<b>'+window.messages['checking-connection']+'</b>';
    ajax.setParams(form);
    ajax.setOnSuccess(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:green">'+window.messages['success']+'</b>';
        document.getElementById('next-button').removeAttribute('disabled');
    });
    ajax.setOnClientError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages[this.jsonResponse['details']['error-code']]+'</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.setOnServerError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">Server Error (500)</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.setOnDisconnected(function(){
        console.log('Disconnected!');
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['disconnected']+'</b>';
        document.getElementById('next-button').setAttribute('disabled','');
    });
    ajax.send();
    return false;
}

function adminAccInputsChanged(){
    var username = document.getElementById('username-input').value;
    if(username !== ''){
        var password = document.getElementById('password-input').value;
        if(password !== ''){
            var messageDisplay = document.getElementById('message-display');
            var confPass = document.getElementById('conf-pass-input').value;
            if(confPass === password){
                messageDisplay.innerHTML = '';
                var email = document.getElementById('address-input').value;
                if(email !== ''){
                    window.account = {
                        username:username,
                        password:password,
                        email:email
                    };
                    document.getElementById('check-input').removeAttribute('disabled');
                    return;
                }
            }
            else{
                
                messageDisplay.innerHTML = '<b style="color:red">'+window.messages['password-missmatch']+'</b>';
            }
        }
    }
    document.getElementById('check-input').setAttribute('disabled','');
}
function dbInputChanged(){
    var host = document.getElementById('database-host-input').value;
    if(host !== ''){
        var username = document.getElementById('username-input').value;
        if(username !== ''){
            var password = document.getElementById('password-input').value;
            if(password !== ''){
                var dbName = document.getElementById('db-name-input').value;
                if(dbName !== ''){
                    window.dbAttrs = {
                        host:host,
                        username:username,
                        password:password,
                        dbName:dbName
                    };
                    document.getElementById('check-input').removeAttribute('disabled');
                    return;
                }
            }
        }
    }
    document.getElementById('check-input').setAttribute('disabled','');
}

function emailInputChanged(){
    var host = document.getElementById('server-address-input').value;
    if(host !== ''){
        var port = document.getElementById('port-input').value;
        if(port !== ''){
            var address = document.getElementById('address-input').value;
            if(address !== ''){
                var username = document.getElementById('username-input').value;
                if(username !== ''){
                    var password = document.getElementById('password-input').value;
                    if(password !== ''){
                        var name = document.getElementById('account-name-input').value;
                        if(name !== ''){
                            document.getElementById('check-input').removeAttribute('disabled');
                            window.mailAttrs = {
                                'server-address':host,
                                port:port,
                                address:address,
                                username:username,
                                password:password,
                                name:name
                            };
                            return;
                        }
                    }
                }
            }
        }
    }
    document.getElementById('check-input').setAttribute('disabled','');
}
function siteInfoInputsChanged(){
    var saveButton = document.getElementById('save-input');
    var siteName = document.getElementById('site-name-input').value;
    if(siteName !== ''){
        var siteDesc = document.getElementById('site-description-input').value;
        if(siteDesc !== ''){
            saveButton.removeAttribute('disabled');
            window.siteInfo = {
                name:siteName,
                desc:siteDesc
            };
            return;
        }
    }
    saveButton.setAttribute('disabled','');
}
function updateSiteInfo(){
    var ajax = new AJAX({
        method:'post',
        url:'apis/WebsiteAPIs'
    });
    var form = new FormData();
    form.append('action','update-site-info');
    form.append('site-name',window.siteInfo['name']);
    form.append('site-description',window.siteInfo['desc']);
    var messageDisplay = document.getElementById('message-display');
    var submitButton = document.getElementById('save-input');
    submitButton.setAttribute('disabled','');
    messageDisplay.innerHTML = '<b>'+window.messages['saving']+'</b>';
    ajax.setParams(form);
    ajax.setOnSuccess(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:green">'+window.messages['saved']+'</b>';
        document.getElementById('finish-button').removeAttribute('disabled');
    });
    ajax.setOnClientError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+this.jsonResponse['message']+'</b>';
        document.getElementById('finish-button').setAttribute('disabled','');
    });
    ajax.setOnServerError(function(){
        console.log(this.jsonResponse);
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">Server Error (500)</b>';
        document.getElementById('finish-button').setAttribute('disabled','');
    });
    ajax.setOnDisconnected(function(){
        console.log('Disconnected!');
        submitButton.removeAttribute('disabled');
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['disconnected']+'</b>';
        document.getElementById('finish-button').setAttribute('disabled','');
    });
    ajax.send();
    return false;
}