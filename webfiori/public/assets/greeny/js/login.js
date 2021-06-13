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

function sendLoginRequest(){
    document.getElementById('message').innerHTML = '<b style="color:black">Logging in...</b>';
    var username = document.getElementById('username-input').value;
    var password = document.getElementById('password-input').value;
    if(username === '' || password === ''){
        document.getElementById('message').innerHTML = '<b style="color:#ff8080">Missing username or password!</b>';
        return false;
    }
    var keepLogged = document.getElementById('keep-me-logged').checked;
    var sessionDuration = keepLogged === true ? 10080 : 30;
    var refresh = keepLogged === true ? false : true;
    var loginParams = {
        username:username,
        password:password,
        'session-duration':sessionDuration,
        'refresh-timeout':refresh,
        callbacks:{
            onsuccess:[
                function(){
                    console.log(this.response);
                    document.getElementById('message').innerHTML = '<b style="color:lightgreen">'+this.jsonResponse['message']+'</b>';
                    var user = this.jsonResponse['session']['user'];
                    console.log(user);
                    if(user['status-code'] === 'N'){
                        if(window.tok){
                            window.location.href = 'activate-account?activation-token='+window.tok;
                        }
                        else{
                            window.location.href = 'activate-account';
                        }
                    }
                    else{
                        window.location.href = 'home';
                    }
                }
            ],
            onclienterr:[
                function(){
                    console.log(this.response);
                    if(this.jsonResponse !== null){
                        document.getElementById('message').innerHTML = '<b style="color: #ff8080;">'+this.jsonResponse['message']+'</b>';
                    }
                    else{
                        document.getElementById('message').innerHTML = '<b style="color:#ff8080">Something went wrong!</b>';
                    }
                }
            ],
            onservererr:[],
            ondisconnected:[]
        }
    };
    login(loginParams);
    return false;
}
