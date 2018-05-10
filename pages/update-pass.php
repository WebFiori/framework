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

//sets the translation
Page::get()->loadTranslation(TRUE);

//load theme
Page::get()->loadTheme();

$lang = LANGUAGE['pages']['update-pass'];

Page::get()->setTitle($lang['title']);

Page::get()->setDescription($lang['description']);
//end of page setup.

// check if user is logged in
//if not, go to login page
if(WebsiteFunctions::get()->getMainSession()->validateToken() != TRUE){
    header('location: login');
}

//check user type
$userId = filter_input(INPUT_GET, 'user-id');

?>
<!DOCTYPE html>
<html lang="<?php echo Page::get()->getLang()?>">
    <head>
        <?php echo getHeadNode('pages/profile', Page::get()->getLang())?>
        <script type="text/javascript" src="res/js/js-ajax-helper-0.0.5/AJAX.js"></script>
        <script type="text/javascript" src="res/js/APIs.js"></script>
        <script type="text/javascript">
            function update(){
                console.log('Update');
                var oldPass = document.getElementById('old-pass-input').value;
                if(oldPass !== ''){
                    var newPass = document.getElementById('new-pass-input').value;
                    if(newPass !== ''){
                        var confPass = document.getElementById('conf-pass-input').value;
                        if(confPass === newPass){
                            var params = 'action=update-password&old-pass='+encodeURIComponent(oldPass)+
                                    '&new-pass='+encodeURIComponent(newPass)+'&user-id=<?php echo $userId?>';
                            var ajax = new AJAX();
                            ajax.setURL(APIS.UserAPIs.link);
                            ajax.setReqMethod('post');
                            ajax.setParams(params);
                            ajax.setOnSuccess(function(){
                                console.log(this.response);
                                document.getElementById('status-label').innerHTML = '<?php echo $lang['labels']['updated']?>';
                                window.location.href = 'pages/profile?user-id=<?php echo $userId?>';
                            });
                            ajax.setOnClientError(function(){
                                console.log(this.response);
                                document.getElementById('status-label').innerHTML = '<?php echo $lang['labels']['incorrect-old-pass']?>';
                            });
                            ajax.send();
                        }
                        else{
                            document.getElementById('status-label').innerHTML = '<?php echo $lang['labels']['pass-missmatch']?>';
                        }
                    }
                    else{
                        document.getElementById('status-label').innerHTML = '<?php echo $lang['labels']['empty-new-password']?>';
                    }
                }
                else{
                    document.getElementById('status-label').innerHTML = '<?php echo $lang['labels']['empty-old-pass']?>';
                }
            }
        </script>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <?php echo getAsideNavNode(Page::get()->getWritingDir(),2);?>
                    <div id="pa-main-content" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage" dir="<?php echo Page::get()->getWritingDir()?>" class="<?php echo 'pa-'.Page::get()->getWritingDir().'-col-ten'?> show-border">
                        <header id="header" itemscope itemtype="http://schema.org/WPHeader" class="pa-row">
                            <h1 name="page-title" itemprop="name" id="page-title"><?php echo $lang['title']?></h1>
                        </header>
                        <div class="pa-row">
                            <?php
                            if($userId != NULL && $userId != FALSE){
                                if($userId != WebsiteFunctions::get()->getUserID()){
                                    if(WebsiteFunctions::get()->getAccessLevel() != 0){
                                        echo '<div class="pa-row">';
                                        echo '<table>';
                                        echo '<tr>';
                                        echo '<td><lable>'.$lang['labels']['old-pass'].'</label></td>';
                                        echo '<td><input id="old-pass-input" type="password"></td>';
                                        echo '</tr>';
                                        echo '<tr>';
                                        echo '<td><lable>'.$lang['labels']['new-pass'].'</label></td>';
                                        echo '<td><input id="new-pass-input" type="password"></td>';
                                        echo '</tr>';
                                        echo '<tr>';
                                        echo '<td><lable>'.$lang['labels']['conf-pass'].'</label></td>';
                                        echo '<td><input id="conf-pass-input" type="password"></td>';
                                        echo '</tr>';
                                        echo '</table>';
                                        echo '<div class = "pa-'.Page::get()->getWritingDir().'-col-ten">';
                                        echo '<label id="status-label"></label>';
                                        echo '</div>';
                                        echo '<button onclick="update()" class="pa-row" data-action="ok">'.$lang['labels']['update'].'</button>';
                                        echo '</div>';
                                    }
                                    else{
                                        echo '<b style="color:red">NOT AUTHORIZED</b>';
                                    }
                                }
                                else{
                                    echo '<div class="pa-row">';
                                    echo '<table>';
                                    echo '<tr>';
                                    echo '<td><lable>'.$lang['labels']['old-pass'].'</label></td>';
                                    echo '<td><input id="old-pass-input" type="password"></td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo '<td><lable>'.$lang['labels']['new-pass'].'</label></td>';
                                    echo '<td><input id="new-pass-input" type="password"></td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo '<td><lable>'.$lang['labels']['conf-pass'].'</label></td>';
                                    echo '<td><input id="conf-pass-input" type="password"></td>';
                                    echo '</tr>';
                                    echo '</table>';
                                    echo '<div class = "pa-'.Page::get()->getWritingDir().'-col-ten">';
                                    echo '<label id="status-label"></label>';
                                    echo '</div>';
                                    echo '<button onclick="update()" class="pa-row" data-action="ok">'.$lang['labels']['update'].'</button>';
                                    echo '</div>';
                                }
                            }
                            else{
                                header('location: home');
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


