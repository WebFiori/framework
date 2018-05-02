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
        $tag->openTag('<footer id="footer" dir="'.PageAttributes::get()->getWritingDir().'" name="footer" itemtype="http://schema.org/WPFooter">');

        $tag->closeTag('</footer>');
        return $tag.'';
    }
}