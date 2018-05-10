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


//sets the translation
Page::get()->loadTranslation(TRUE);

//load theme
Page::get()->loadTheme();

$lang = LANGUAGE['pages']['register'];

Page::get()->setTitle($lang['title']);

Page::get()->setDescription($lang['description']);
//end of page setup.

// check if user is logged in
//if not, go to login page
if(WebsiteFunctions::get()->getMainSession()->validateToken() != TRUE){
    header('location: login');
}
?>
<!DOCTYPE html>
<html lang="<?php echo Page::get()->getLang()?>">
    <head>
        <?php echo getHeadNode('pages/register', Page::get()->getLang())?>
        <script type="text/javascript" src="res/js/js-ajax-helper-1.0.0/AJAX.js"></script>
        <script type="text/javascript" src="res/js/APIs.js"></script>
        <script type="text/javascript">
            function register(){
                var userName = document.getElementById('username-input').value;
                if(userName !== ''){
                    var pass = document.getElementById('pass-input').value;
                    if(pass !== ''){
                        var confPass = document.getElementById('conf-pass-input').value;
                        if(pass === confPass){
                            var email = document.getElementById('email-input').value;
                            if(email !== ''){
                                var acclvl = document.getElementById('acc-lvl-select').value;
                                if(acclvl !== ''){
                                    var params = 'action=add-user&username='+encodeURIComponent(userName)+
                                            '&password='+encodeURIComponent(pass)+
                                            '&email='+encodeURIComponent(email)+
                                            '&access-level='+acclvl;
                                    var ajax = new AJAX();
                                    ajax.setURL(APIS.UserAPIs.link);
                                    ajax.setReqMethod('post');
                                    ajax.setParams(params);
                                    ajax.setOnSuccess(function(){
                                        console.log(this.response);
                                        window.location.href = 'pages/profile?user-id='+this.jsonResponse['user']['user-id'];
                                    });
                                    ajax.setOnClientError(function(){
                                        console.log(this.response);
                                        document.getElementById('status-label').innerHTML = this.jsonResponse['message'];
                                    });
                                    ajax.send();
                                }
                                else{
                                    document.getElementById('status-label').innerHTML = '<?php echo $lang['errors']['missing-acc-lvl']?>';
                                }
                            }
                            else{
                                document.getElementById('status-label').innerHTML = '<?php echo $lang['errors']['missing-email']?>';
                            }
                        }
                        else{
                            document.getElementById('status-label').innerHTML = '<?php echo $lang['errors']['pass-missmatch']?>';
                        }
                    }
                    else{
                        document.getElementById('status-label').innerHTML = '<?php echo $lang['errors']['missing-pass']?>';
                    }
                }
                else{
                    document.getElementById('status-label').innerHTML = '<?php echo $lang['errors']['missing-username']?>';
                }
            }
        </script>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <?php echo getAsideNavNode(Page::get()->getWritingDir(),4);?>
                    <div id="pa-main-content" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage" dir="<?php echo Page::get()->getWritingDir()?>" class="<?php echo 'pa-'.Page::get()->getWritingDir().'-col-ten'?> show-border">
                        <header id="header" itemscope itemtype="http://schema.org/WPHeader" class="pa-row">
                            <h1 name="page-title" itemprop="name" id="page-title"><?php echo $lang['title']?></h1>
                        </header>
                        <div class="pa-row">
                            <?php
                                if(WebsiteFunctions::get()->getAccessLevel() == 0){
                                    echo '<div style="overflow-x:auto;" class="pa-'.Page::get()->getWritingDir().'-col-ten">';
                                    echo '<table>';
                                    echo '<tr>';
                                    echo '<td><label>'.$lang['labels']['username'].'</label></td>';
                                    echo '<td><input id="username-input" type="text"></td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo '<td><label>'.$lang['labels']['password'].'</label></td>';
                                    echo '<td><input id="pass-input" type="password"></td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo '<td><label>'.$lang['labels']['conf-pass'].'</label></td>';
                                    echo '<td><input id="conf-pass-input" type="password"></td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo '<td><label>'.$lang['labels']['email'].'</label></td>';
                                    echo '<td><input id="email-input" type="text"></td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo '<td><label>'.$lang['labels']['acc-lvl'].'</label></td>';
                                    echo '<td><select id="acc-lvl-select">';
                                    echo '<option value="0">Admin</option>';
                                    echo '<option value="1">Other</option>';
                                    echo '</select></td>';
                                    echo '</tr>';
                                    echo '</table>';
                                    echo '<label id="status-label"></label>';
                                    echo '<button class="pa-row" data-action="ok" onclick="register()">'.$lang['labels']['reg'].'</button>';
                                    echo '</div>';
                                }
                                else{
                                    echo '<b style="color:red">NOT AUTHORIZED</b>';
                                }
                            ?>
                        </div>
                        <?php echo getFooterNode()?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


