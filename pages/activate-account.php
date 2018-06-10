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
$activationTok = filter_input(INPUT_GET, 'activation-token');
$mainSession = WebsiteFunctions::get()->getMainSession();

if($mainSession->validateToken() === TRUE){
    if($mainSession->getUser()->getStatus() == 'A'){
        header('location: home');
    }
}
else{
    header('location: login');
}
Page::get()->usingLanguage(TRUE);
$pageLbls = LANGUAGE['pages']['activate-account'];
Page::get()->setTitle($pageLbls['title']);
Page::get()->setDescription($pageLbls['description']);
//load theme
Page::get()->loadTheme();

//end of page setup.
?>
<!DOCTYPE html>
<html lang = "<?php echo Page::get()->getLang()?>">
    <head>
        <?php echo getHeadNode('login', Page::get()->getLang())?>
        <link rel="stylesheet" href="res/css/login.css">
        <script type="text/javascript" src="res/js/APIs.js"></script>
        <script type="text/javascript" src="res/js/js-ajax-helper-1.0.0/AJAX.js"></script>
        <script type="text/javascript">
            <?php
                if($activationTok !== NULL && $activationTok !== FALSE){
                    ?>
            window.onload = function(){
                activateAccount();
            }
                        <?php
                }
            ?>
            function activateAccount(){
                var tok = document.getElementById('message').innerHTML = <?php echo '\''.LANGUAGE['general']['wait'].'\''?>;
                if(tok === ''){
                    return false;
                }
                var tok = document.getElementById('activation-token-input').value;
                var params = 'action=activate-account&activation-token='+encodeURIComponent(tok);
                var ajax = new AJAX(
                        {
                                <?php
                        if(defined('DEBUG')){
                            echo '"enable-log":true,';
                        }
                        ?>
                        url:APIS.UserAPIs.link,
                        method:'post'
                        }
                );
                ajax.setParams(params);
                ajax.setOnSuccess(function(){
                    console.log(this.response);
                    document.getElementById('message').innerHTML = <?php echo '\''.$pageLbls['success'].'\''?>;
                    //window.location.href = 'pages/home';
                });
                ajax.setOnClientError(function(){
                    console.log(this.response);
                    document.getElementById('message').innerHTML = this.jsonResponse['message'];
                });
                ajax.send();
                return false;
            }
        </script>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <form dir="<?php echo Page::get()->getWritingDir()?>" dir="rtl"  method="POST" id="login_form" class="pa-row">
                        <div style="margin-bottom: 18%;text-align: center;" class="pa-row">
                            <!--<img id="login_logo" src="res/images/favicon.png" alt="Website Logo">-->
                            <label style="font-weight: bold; display: block; margin:auto; text-align: center; width: 100%;"><?php echo $pageLbls['labels']['main']?></label>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <label for="activation-token-input"><?php echo $pageLbls['labels']['activation-token']?></label><br/>
                            <input style="width: 100%;height: 40px" value="<?php echo $activationTok?>" id="activation-token-input" spellcheck="off" form="login_form" required placeholder="<?php echo $pageLbls['placeholders']['activation-token']?>" type="text" name="username"/>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <label id="message"></label>
                        </div>
                        <div class="pa-row" style="background-color: #2d8659">
                            <input id="submit-button" value="<?php echo $pageLbls['actions']['activate']?>" type="submit" onclick="return activateAccount()"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>



