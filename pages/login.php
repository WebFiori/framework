<?php

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

//first, load the root file
require_once '../root.php';

//use this to show runtime errors
Util::displayErrors();

//sets the translation
PageAttributes::get()->loadTranslation(TRUE);
$pageLbls = LANGUAGE['pages']['login'];
//load themem
PageAttributes::get()->loadTheme();

//end of page setup.
?>
<!DOCTYPE html>
<html lang = "<?php echo PageAttributes::get()->getLang()?>">
    <head>
        <?php echo staticHeadTag('login', PageAttributes::get()->getLang())?>
        <script type="text/javascript" src="res/js/js-ajax-helper-0.0.5/AJAX.js"></script>
        <script type="text/javascript" src="res/js/APIs.js"></script>
        <script type="text/javascript" src="res/js/langs/lANG_EN.js"></script>
        <link rel="stylesheet" href="res/css/login.css">
        <script type="text/javascript">
            function login(){
                document.getElementById('message').innerHTML = <?php echo '\''.LANGUAGE['general']['wait'].'\''?>;
                var username = document.getElementById('username-input').value;
                var password = document.getElementById('password-input').value;
                var params = 'action=login&username='+encodeURIComponent(username)+'&password='+password;
                var ajax = new AJAX();
                ajax.setURL(APIS.AuthAPI.link);
                ajax.setReqMethod('post');
                ajax.setParams(params);
                ajax.setOnSuccess(function(){
                    console.log(this.response);
                    document.getElementById('message').innerHTML = <?php echo '\''.$pageLbls['success'].'\''?>;
                    var tok = this.jsonResponse['user']['token'];
                    //exp after 30 min
                    var date = new Date();
                    date.setTime(currentTime + 1000*60*30);
                    var currentTime = date.getTime();
                    document.cookie = 'token='+tok+';expires ='+date.toUTCString();
                    window.location.href = 'pages/home';
                });
                ajax.setOnClientError(function(){
                    console.log(this.response);
                    document.getElementById('message').innerHTML = <?php echo '\''.$pageLbls['errors']['incorrect-login-params'].'\''?>;
                });
                ajax.send();
            }
        </script>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <form dir="<?php echo PageAttributes::get()->getWritingDir()?>" onsubmit="login()" dir="rtl"  method="POST" id="login_form" class="pa-row">
                        <div style="margin-bottom: 18%;text-align: center;" class="pa-row">
                            <!--<img id="login_logo" src="res/images/favicon.png" alt="Website Logo">-->
                            <label style="font-weight: bold; display: block; margin:auto; text-align: center; width: 100%;"><?php echo $pageLbls['labels']['main']?></label>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <label for="username"><?php echo $pageLbls['labels']['username']?></label><br/>
                            <input id="username-input" spellcheck="off" form="login_form" required placeholder="<?php echo $pageLbls['placeholders']['username']?>" type="text" name="username"/>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <label for="password"><?php echo $pageLbls['labels']['password']?></label><br/>
                            <input autocomplete="off" id="password-input" spellcheck="off" form="login_form" required placeholder="<?php echo $pageLbls['placeholders']['password']?>" type="password" name="password"/>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <label id="message"></label>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <input id="login_button" value="<?php echo $pageLbls['actions']['login']?>" type="button" onclick="login()"/>
                            <!--<button id="cancel_button"><?php //echo DISP_LANG_LOGIN['cancel-label']?></button>-->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>



