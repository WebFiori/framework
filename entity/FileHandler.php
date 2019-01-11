<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
use Exception;
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
     * @param type $incTab if true, a tab will be added before the content.
     * @param type $incNewLine if true, the cursor will move to the next line.
     */
    public function write($content, $incTab = FALSE, $incNewLine = FALSE){
        if($incTab == TRUE && $incNewLine == TRUE){
            fwrite($this->file, $this->_getTab().$content);
            $this->newLine();
        }
        else if($incTab == FALSE && $incNewLine == TRUE){
            fwrite($this->file, $content);
            $this->newLine();
        }
        else if($incTab == TRUE && $incNewLine == FALSE){
            fwrite($this->file, $this->_getTab().$content);
        }
        else{
            fwrite($this->file, $content);
        }
    }
    /**
     * Open new html tag.
     * After the tag is written to the file, the tab size will be 
     * increased by 1 and the cursor will move to the next line.
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
     * reduced by 1 and the cursor will move to the next line.
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
    
    private function _getTab(){
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