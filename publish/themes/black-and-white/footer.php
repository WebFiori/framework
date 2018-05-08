<?php

function dynamicFooter(){
    if(Page::get()->getLang() != NULL){
        if(Page::get()->getWritingDir()){
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
    if(Page::get()->getWritingDir() != null && Page::get()->getLang() != NULL){
        $tag = new HTMLTag(5);
        $tag->openTag('<footer id="footer" dir="'.Page::get()->getWritingDir().'" name="footer" itemtype="http://schema.org/WPFooter">');

        $tag->closeTag('</footer>');
        return $tag.'';
    }
}