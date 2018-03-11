<?php

function dynamicFooter(){
    if(PageAttributes::get()->getLang() != NULL){
        if(PageAttributes::get()->getWritingDir()){
            return '<?php echo staticFooter()?>';
        }
        else{
            throw new Exception('Writing direction of the page is not set.');
        }
    }
    else{
        throw new Exception('Language of the page is not set.');
    }
}

function staticFooter(){
    if(PageAttributes::get()->getWritingDir() != null && PageAttributes::get()->getLang() != NULL){
        $tag = new HTMLTag(5);
        $tag->openTag('<div class="pa-row">');
        $tag->openTag('<footer id="footer" dir="'.PageAttributes::get()->getWritingDir().'" class="pa-'.PageAttributes::get()->getWritingDir().'-col-twelve show-border" name="footer" itemtype="http://schema.org/WPFooter">');
        $tag->openTag('<nav itemscop itemtype="http://schema.org/SiteNavigationElement">');
        $tag->content('<meta itemprop="name" content="Site Links">');
        $tag->openTag('<ul>');
        if(PageAttributes::get()->getLang() == "EN"){
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="en/about">About</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="en/about/contact-us">Contact Us</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="en/about/privacy">Privacy</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="https://ibrahim-2017.blogspot.com">Blog</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="admin/login">admin</a>');
//            $tag->closeTag('</li>');
        }
        else if(PageAttributes::get()->getLang() == 'AR'){
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="ar/about">حول الموقع</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="ar/about/contact-us">الإتصال بنا</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="ar/about/privacy">الخصوصية</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="https://ibrahim-2017.blogspot.com">المدونة</a>');
//            $tag->closeTag('</li>');
//            $tag->openTag('<li>');
//            $tag->content('<a itemprop="url" href="admin/login">إدارة</a>');
//            $tag->closeTag('</li>');
        }
        $tag->closeTag('</ul>');
        $tag->closeTag('</nav>');
        if(PageAttributes::get()->getLang() == 'EN'){
            $tag->content('<div class="pa-ltr-col-twelve">Programming Academia, All Rights Reserved © 2018</div>');
        }
        else{
            $tag->content('<div class="pa-rtl-col-twelve">أكاديميا البرمجة, جميع الحقوق محفوظة © 2018 </div>');
        }
        $tag->closeTag('</footer>');
        $tag->closeTag('</div>');
        return $tag.'';
    }
}