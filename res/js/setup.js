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
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages[this.jsonResponse['details']['error-code']]+'</b>';
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