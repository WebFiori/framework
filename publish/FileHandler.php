<?php
if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * This class is used to write HTML or PHP files.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class FileHandler{
    
    private $file;
    private $tapSpace;
    private $tabCount = 0;

    public function __construct($fName, $mode='w+') {
        $this->tapSpace = '    ';
        $ffName = str_replace('\\', '/', $fName);
        $this->file = fopen($ffName, $mode);
        if($this->file === FALSE){
            throw new Exception('Unable to open the file \''.$fName.'\' using the mode \''.$mode.'\'.');
        }
    }
    /**
     * Close the file and save changes.
     */
    public function close(){
        fclose($this->file);
    }
    /**
     * Write new content to the file
     * @param type $content the content that will be written.
     * @param type $incTab if true, a tab of size 4 will be added before the content.
     * @param type $incNewLine if true, the cursor will move to the next line.
     */
    public function write($content, $incTab = FALSE, $incNewLine = FALSE){
        if($incTab == TRUE && $incNewLine == TRUE){
            fwrite($this->file, $this->getTab().$content);
            $this->newLine();
        }
        else if($incTab == FALSE && $incNewLine == TRUE){
            fwrite($this->file, $content);
            $this->newLine();
        }
        else if($incTab == TRUE && $incNewLine == FALSE){
            fwrite($this->file, $this->getTab().$content);
        }
        else{
            fwrite($this->file, $content);
        }
    }
    /**
     * Open new html tag.
     * After the tag is written to the file, the tab size will be 
     * increased by 4 and the cursor will move to the next line.
     * @param type $tagName the name of the tag with any additional parameters ( 
     * e.g. &lt;div class="a-class" style=""&gt;).
     */
    public function openTag($tagName = '<div>'){
        $this->write($tagName,TRUE,TRUE);
        $this->addTab();
    }
    
    /**
     * Close an html tag
     * After the tag is written to the file, the tab size will be 
     * reduced by 4 and the cursor will move to the next line.
     * @param type $tagName the name of the tag with any additional parameters ( 
     * e.g. &lt;/div gt;).
     */
    public function closeTag($tagName = '</div>'){
        $this->reduceTab();
        $this->write($tagName, TRUE, TRUE);
    }

    public function getTabCount(){
        return $this->tabCount;
    }
    
    /**
     * Increase tab size by 4.
     */
    public function addTab(){
        $this->tabCount += 1;
    }
    /**
     * Reduce tab size by 4.
     * If the tab size is 0, it will not reduce it more.
     */
    public function reduceTab(){
        if($this->tabCount > 0){
            $this->tabCount -= 1;
        }
    }
    /**
     * Writes '\n' to the file.
     */
    public function newLine(){
        fwrite($this->file, "\n");
    }
    
    private function getTab(){
        if($this->tabCount == 0){
            return '';
        }
        else{
            $tab = '';
            for($i = 0 ; $i < $this->tabCount ; $i++){
                $tab .= $this->tapSpace;
            }
            return $tab;
        }
    }
}