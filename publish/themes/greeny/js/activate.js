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
function activateAccount(){
    var token = document.getElementById('token-input').value;
    var ajax = new AJAX({
        method:'post',
        url:'apis/UserAPIs'
    });
    ajax.setParams('action=activate-account&activation-token='+encodeURIComponent(token));
    var messageDisplay = document.getElementById('message');
    ajax.setOnSuccess(function(){
        messageDisplay.innerHTML = '<b style="color:green">'+window.messages['activated']+'</b>';
        window.location.href = 'pages/home';
    });
    ajax.setOnClientError(function(){
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['inv-tok']+'</b>';
    });
    ajax.setOnServerError(function(){
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['server-err']+'</b>';
    });
    ajax.setOnDisconnected(function(){
        messageDisplay.innerHTML = '<b style="color:red">'+window.messages['disconnected']+'</b>';
    });
    ajax.send();
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

