<?php 
require_once '../root.php';
Util::displayErrors();
PageAttributes::get()->loadTranslation(TRUE);
PageAttributes::get()->loadTheme();
require_once 'ERR_'.PageAttributes::get()->getLang().'.php';
$error;
$errInfo;
$error = filter_input(INPUT_GET, 'err');
if($error != FALSE && $error != NULL){
    switch ($error){
        case 403:{$errInfo = ERR_403;break;}
        case 404:{$errInfo = ERR_404;break;}
        case 408:{$errInfo = ERR_408;break;}
        case 415:{$errInfo = ERR_415;break;}
        case 500:{$errInfo = ERR_500;break;}
        case 501:{$errInfo = ERR_501;break;}
        case 505:{$errInfo = ERR_505;break;}
    }
}
PageAttributes::get()->setTitle(ERR_PAGE_LANG['error'].' '.$error);
PageAttributes::get()->setDescription(ERR_PAGE_LANG['error'].' '.$error.'. '.$errInfo['message']);
?>
<!DOCTYPE html>
<html <?php echo 'lang="'.PageAttributes::get()->getLang().'"'?> >
    <head>
        <?php echo staticHeadTag('errors/error?err='.$error, PageAttributes::get()->getLang())?>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <div id="pa-main-content" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage" dir="<?php echo PageAttributes::get()->getWritingDir()?>" class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?> show-border">
                        <header id="header" itemscope itemtype="http://schema.org/WPHeader" class="pa-row">
                            <h1 name="page-title" itemprop="name" dir="<?php echo PageAttributes::get()->getWritingDir()?>" id="page-title"><?php echo ERR_PAGE_LANG['error'].' '.$error?></h1>
                        </header>
                        <div class="pa-row">
                            <div class="pa-row">
                                <h2 id="post-title"><?php echo $errInfo['type']?></h2>
                                <div class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?>">
                                    <p><?php echo $errInfo['message']?></p>
                                    <p><?php echo ERR_PAGE_LANG['req-url'].' '.Util::getRequestedURL()?></p>
                                    <p><a href="<?php echo SiteConfig::get()->getHomePage()?>"><?php echo ERR_PAGE_LANG['go-home']?></a></p>
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
