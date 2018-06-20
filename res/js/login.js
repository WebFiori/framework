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
/* global APIS */

/**
 * A generic function to send login request to the server.
 * @param {Object} loginParams An object that contains login 
 * parameters. The general structure of the object is as follows:
 * <pre>
 * {<br/>
 * &nbsp;&nbsp;username:''<br/>
 * &nbsp;&nbsp;password:''<br/>
 * &nbsp;&nbsp;session-duration:0<br/>
 * &nbsp;&nbsp;'refresh-timeout':false<br/>
 * &nbsp;&nbsp;callbacks:{<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onsuccess:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onclienterr:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onservererr:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;ondisconnected:[]<br/>
 * &nbsp;&nbsp;}<br/>
 * }
 * </pre>
 * The parameters description:
 * <ul>
 * <li><b>username</b>: Username or the email address of the user.</li>
 * <li><b>password</b>: Login password.</li>
 * <li><b>session-duration</b>: The duration of the session in minutes. 
 * It must be a value grater than 0. If invalid value is given, 10 is used.</li>
 * <li><b>refresh-timeout</b>: A boolean value. If set to true, 
 * the session will never timeout as long as the user is active. If 
 * set to false, the user will be limited by the time that was set by 
 * the variable <b>'session-duration'</b> even if he is active.</li>
 * </ul>
 * In addition to the given parameters, the object can have an optional 
 * attributes (an object) for calling back functions for specific events. the attribute 
 * name is <b>callbacks</b>. The object will have the following arrays of callbacks: 
 * <ul>
 * <li><b>onsucess</b>: An array that contains functions to call in case 
 * of successful login.</li>
 * <li><b>onclienterr</b>: An array that contains functions to call in case 
 * of 4xx error.</li>
 * <li><b>onservererr</b>: An array that contains functions to call in case 
 * of 5xx error.</li>
 * <li><b>ondisconnected</b>: An array that contains functions to call in case 
 * of no internet access is available.</li>
 * </ul>
 * @see AJAX.js
 * @returns {undefined}
 */
function login(loginParams={
    username:'',
    password:'',
    'session-duration':10,
    'refresh-timeout':false,
    callbacks:{
        onsucess:[],
        onclienterr:[],
        onservererr:[],
        ondisconnected:[]
    }
}){
    if(loginParams !== undefined){
        if(loginParams.username !== undefined && loginParams.username !== null && loginParams.username.length !== 0){
            if(loginParams.password !== undefined && loginParams.password !== null && loginParams.password.length !== 0){
                if(typeof loginParams['session-duration'] === 'number'){
                    if(loginParams['session-duration'] > 0){
                        var duration = loginParams['session-duration'];
                    }
                    else{
                        var duration = 10;
                    }
                }
                else{
                    var duration = 10;
                }
                if(typeof loginParams['refresh-timeout'] === 'boolean'){
                    var refresh = loginParams['refresh-timeout'] === true ? 'y' : 'n';
                }
                else{
                    var refresh = 'n';
                }
                var params = 'action=login&username='+encodeURIComponent(loginParams['username'])+
                        '&password='+encodeURIComponent(loginParams['password'])+
                        '&session-duration='+duration+'&refresh-timeout='+refresh;
                var ajax = new AJAX({
                    url:APIS.AuthAPI.link,
                    method:'post'
                });
                ajax.setParams(params);
                if(typeof loginParams['callbacks'] === 'object'){
                    var calls = loginParams['callbacks'];
                    if(Array.isArray(calls['onsucess'])){
                        for(var x = 0 ; x < calls['onsucess'].length ; x++){
                            var call = calls['onsucess'][x];
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
                console.error('The parameter \'loginParams.password\' is undefined, null or empty string.');
            }
        }
        else{
            console.error('The parameter \'loginParams.username\' is undefined, null or empty string.');
        }
    }
    else{
        console.error('The parameter \'loginParams\' is undefined or null.');
    }
}
/**
 * Sends AJAX request to the server for creating a password reset request.
 * @param {type} params
 * <pre>
 * {<br/>
 * &nbsp;&nbsp;email:''<br/>
 * &nbsp;&nbsp;callbacks:{<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onsuccess:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onclienterr:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onservererr:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;ondisconnected:[]<br/>
 * &nbsp;&nbsp;}<br/>
 * }
 * </pre>
 * The parameters description:
 * <ul>
 * <li><b>email</b>: The email address of the user.</li>
 * </ul>
 * In addition to the given parameters, the object can have an optional 
 * attributes (an object) for calling back functions for specific events. the attribute 
 * name is <b>callbacks</b>. The object will have the following arrays of callbacks: 
 * <ul>
 * <li><b>onsucess</b>: An array that contains functions to call in case 
 * of successful login.</li>
 * <li><b>onclienterr</b>: An array that contains functions to call in case 
 * of 4xx error.</li>
 * <li><b>onservererr</b>: An array that contains functions to call in case 
 * of 5xx error.</li>
 * <li><b>ondisconnected</b>: An array that contains functions to call in case 
 * of no internet access is available.</li>
 * </ul>
 * @see AJAX.js
 * @returns {undefined}
 */
function forgotPassword(params={
    email:'',
    callbacks:{
        onsucess:[],
        onclienterr:[],
        onservererr:[],
        ondisconnected:[]
    }
}){
    if(params.email !== undefined && params.email !== null && params.email.length !== 0){
        var reqParams = 'action=forgot-password&email='+params.email;
        var ajax = new AJAX({
            url:APIS.PasswordAPIs.link,
            method:'post'
        });
        ajax.setParams(reqParams);
        if(typeof params['callbacks'] === 'object'){
            var calls = params['callbacks'];
            if(Array.isArray(calls['onsucess'])){
                for(var x = 0 ; x < calls['onsucess'].length ; x++){
                    var call = calls['onsucess'][x];
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
}