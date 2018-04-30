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
require_once 'root.php';

//sets the translation
PageAttributes::get()->loadTranslation(TRUE);

//load themem
PageAttributes::get()->loadTheme();

//end of page setup.
?>
<!DOCTYPE html>
<html>
    <head>
        <?php echo staticHeadTag('example-page', PageAttributes::get()->getLang())?>
    </head>
    <body itemscope itemtype="http://schema.org/WebPage">
        <div class="pa-container">
            <div class="pa-row">
                <div class="pa-row">
                    <div id="pa-main-content" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage" dir="<?php echo PageAttributes::get()->getWritingDir()?>" class="<?php echo 'pa-'.PageAttributes::get()->getWritingDir().'-col-twelve'?> show-border">
                        <header id="header" itemscope itemtype="http://schema.org/WPHeader" class="pa-row">
                            <h1 name="page-title" itemprop="name" id="page-title">This is a Test Page</h1>
                        </header>
                        <div class="pa-row">
                            This is only to check if UI Components load fine.
                        </div>
                        <?php echo staticFooter()?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


