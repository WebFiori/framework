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
PageAttributes::get()->loadTranslation(TRUE);

//load theme
PageAttributes::get()->loadTheme();

$lang = LANGUAGE['pages']['profile'];

PageAttributes::get()->setTitle($lang['title']);

PageAttributes::get()->setDescription($lang['description']);
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
<html lang="<?php echo PageAttributes::get()->getLang()?>">
    <head>
        <?php echo staticHeadTag('pages/profile', PageAttributes::get()->getLang())?>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <?php echo staticAsideNav(PageAttributes::get()->getWritingDir(),2);?>
                    <div id="pa-main-content" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage" dir="<?php echo PageAttributes::get()->getWritingDir()?>" class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-ten'?> show-border">
                        <header id="header" itemscope itemtype="http://schema.org/WPHeader" class="pa-row">
                            <h1 name="page-title" itemprop="name" id="page-title"><?php echo $lang['title']?></h1>
                        </header>
                        <div class="pa-row">
                            <?php
                            if($userId != NULL && $userId != FALSE){
                                if($userId != WebsiteFunctions::get()->getUserID()){
                                    if(WebsiteFunctions::get()->getAccessLevel() != 0){
                                        $profile = new User();
                                        $profile->setAccessLevel('<b style="color:red">NOT AUTHORIZED</b>');
                                        $profile->setDisplayName('<b style="color:red">NOT AUTHORIZED</b>');
                                        $profile->setEmail('<b style="color:red">NOT AUTHORIZED</b>');
                                        $profile->setStatus('<b style="color:red">NOT AUTHORIZED</b>');
                                        $profile->setActivationTok('<b style="color:red">NOT AUTHORIZED</b>');
                                        $profile->setUserName('<b style="color:red">NOT AUTHORIZED</b>');
                                    }
                                    else{
                                        $profile = UserFunctions::get()->getUserByID($userId);
                                        if($profile == UserFunctions::NO_SUCH_USER){
                                            $profile = new User();
                                            $profile->setAccessLevel(UserFunctions::NO_SUCH_USER);
                                            $profile->setDisplayName(UserFunctions::NO_SUCH_USER);
                                            $profile->setEmail(UserFunctions::NO_SUCH_USER);
                                            $profile->setStatus(UserFunctions::NO_SUCH_USER);
                                            $profile->setActivationTok(UserFunctions::NO_SUCH_USER);
                                            $profile->setUserName(UserFunctions::NO_SUCH_USER);
                                        }
                                    }
                                }
                                else{
                                    $profile = UserFunctions::get()->getUserByID(WebsiteFunctions::get()->getUserID());
                                }
                            }
                            else{
                                header('location: home');
                            }
                            ?>
                            <div style="border: 1px solid" class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                <p><b><?php echo $lang['labels']['actions']?></b></p>
                                <ul>
                                    <li><a href="pages/update-pass?user-id=<?php echo $userId?>"><?php echo $lang['labels']['update-password']?></a></li>
                                    <li><a href="pages/update-email?user-id=<?php echo $userId?>"><?php echo $lang['labels']['update-email']?></a></li>
                                    <li><a href="pages/update-disp-name?user-id=<?php echo $userId?>"><?php echo $lang['labels']['update-disp-name']?></a></li>
                                </ul>
                            </div>
                            <div class="pa-row">
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['username']?></b><?php echo $profile->getUserName()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['display-name']?></b><?php echo $profile->getDisplayName()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['email']?></b><?php echo $profile->getEmail()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['status']?></b><?php echo $profile->getStatus()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['reg-date']?></b><?php echo $profile->getRegDate()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['last-login']?></b><?php echo $profile->getLastLogin()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['access-level']?></b><?php echo $profile->getAccessLevel()?></p>
                                </div>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><b><?php echo $lang['labels']['activation-token']?></b><a href="pages/login?ac-tok=<?php echo $profile->getActivationTok()?>"><?php echo $profile->getActivationTok()?></a></p>
                                </div>
                            </div>
                        </div>
                        <?php echo staticFooter()?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


