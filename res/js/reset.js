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

function resetPassInputChanged(){
    var messageDisplay = document.getElementById('message');
    var email = document.getElementById('email-input').value;
    if(email !== ''){
        var pass = document.getElementById('password-input').value;
        if(pass !== ''){
            var confPass = document.getElementById('conf-pass-input').value;
            if(pass === confPass){
                messageDisplay.innerHTML = '';
                document.getElementById('submit-button').removeAttribute('disabled');
                if(window['request-parameters'] !== undefined){
                    window['request-parameters'] = {
                        email:email,
                        'new-password':pass,
                        'conf-pass':confPass
                    };
                }
                else{
                    window['request-parameters'] = {
                        email:email,
                        'new-password':pass,
                        'conf-pass':confPass,
                        token:window.token,
                        callbacks:{
                            onsuccess:[function(){
                                document.getElementById('submit-button').removeAttribute('disabled');
                                messageDisplay.innerHTML = '<b style="color:green">'+window.messages['resetted']+'</b>';
                            }],
                            onclienterr:[function(){
                                if(this.jsonResponse['message'] === 'The following parameter(s) has invalid values: \'email\'.'){
                                    messageDisplay.innerHTML = window.messages['inv-email'];
                                }
                                else{
                                    messageDisplay.innerHTML = this.jsonResponse['message'];
                                }
                                document.getElementById('submit-button').removeAttribute('disabled');
                            }],
                            onservererr:[function(){
                                messageDisplay.innerHTML = window.messages['server-err'];
                                document.getElementById('submit-button').removeAttribute('disabled');
                            }],
                            ondisconnected:[function(){
                                messageDisplay.innerHTML = window.messages['disconnected'];
                                document.getElementById('submit-button').removeAttribute('disabled');
                            }]
                        }
                    };
                    return;
                }
            }
            else{
                messageDisplay.innerHTML = window.messages['password-missmatch'];
            }
        }
    }
    document.getElementById('submit-button').setAttribute('disabled','');
}
function resetPass(){
    var messageDisplay = document.getElementById('message');
    messageDisplay.innerHTML = window.messages['resetting'];
    document.getElementById('submit-button').setAttribute('disabled','');
    resetPassword(window['request-parameters']);
    return false;
}